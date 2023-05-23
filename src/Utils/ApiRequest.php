<?php

declare(strict_types=1);

namespace Core\Utils;

use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\ResponseConvertorFactoryInterface;
use Core\Factories\ResponseConvertorFactory;
use Core\Interfaces\ApiRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Http\Client\Exception\RequestException;
use \InvalidArgumentException;
use function http_build_query,
             filter_var,
             method_exists,
             array_change_key_case,
             strtolower;
use const CASE_LOWER;

/**
 * Class ApiRequest
 *
 * Represents an API request utility class.
 */
class ApiRequest implements ApiRequestInterface
{

    use MultipartFormdataTrait;

    /**
     * Prefix for all URI
     * 
     * @var string
     */
    protected string $uriPrefix = '';

    /**
     * Default content type
     * @var string
     */
    protected string $contentType = 'application/json';

    /**
     * Global headers
     * @var array
     */
    protected array $headers = [];

    /**
     * List of ClientInterface objects (httpClients)
     * @var array
     */
    protected array $httpClients = [];

    /**
     * Current key of httpClient (ClientInterface object)
     * @var string
     */
    protected string $currentHttpClient = 'default';

    /**
     * Fake response for testing purposes
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $fakeResponse = null;

    /**
     * PSR URI factory
     * @var UriFactoryInterface
     */
    protected UriFactoryInterface $uriFactory;

    /**
     * PSR Request factory
     * @var RequestFactoryInterface
     */
    protected RequestFactoryInterface $requestFactory;

    /**
     * PSR Uri interface
     * @var UriInterface|null
     */
    protected ?UriInterface $uri = null;

    /**
     * PSR stream factory
     * @var StreamFactoryInterface
     */
    protected StreamFactoryInterface $streamFactory;

    /**
     * Default response convertor
     * @var ResponseConvertorInterface|null
     */
    protected ?ResponseConvertorInterface $convertor = null;

    /**
     * ApiRequest Constructor
     * 
     * @param UriFactoryInterface $uriFactory
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param ClientInterface $httpClient
     * @param ResponseConvertorInterface $convertor
     * @param string|null $uriPrefix Uri prefix
     */
    public function __construct(
            UriFactoryInterface $uriFactory,
            RequestFactoryInterface $requestFactory,
            StreamFactoryInterface $streamFactory,
            ClientInterface $httpClient,
            ResponseConvertorInterface $convertor = null,
            ?string $uriPrefix = null
    )
    {
        $this->uriFactory = $uriFactory;
        $this->requestFactory = $requestFactory;
        $this->httpClients['default'] = $httpClient;
        $this->streamFactory = $streamFactory;
        $this->convertor = $convertor;
        $this->uriPrefix = $uriPrefix ?? '';
    }

    /**
     * @inheritDoc
     */
    public function get(?array $data = null, array $headers = []): ResponseInterface
    {
        return $this->request('GET', $data, $headers);
    }

    /**
     * @inheritDoc
     */
    public function post(?array $data = null, array $headers = []): ResponseInterface
    {
        return $this->request('POST', $data, $headers);
    }

    /**
     * @inheritDoc
     */
    public function put(?array $data = null, array $headers = []): ResponseInterface
    {
        return $this->request('PUT', $data, $headers);
    }

    /**
     * @inheritDoc
     */
    public function delete(?array $data = null, array $headers = []): ResponseInterface
    {
        return $this->request('DELETE', $data, $headers);
    }

    /**
     * @inheritDoc
     */
    public function convert(
            string $method,
            ?array $data = null,
            array $headers = [],
            ?ResponseConvertorInterface $convertor = null
    ): ResponseConvertorFactoryInterface
    {
        $response = $this->request($method, $data, $headers);
        $currentConvertor = $convertor ?? $this->convertor;
        if ($currentConvertor === null) {
            throw new InvalidArgumentException('Please set default convertor');
        }
        return new ResponseConvertorFactory($response, $currentConvertor);
    }

    /**
     * @inheritDoc
     */
    public function request(
            string $method,
            ?array $data = null,
            array $headers = []
    ): ResponseInterface
    {
        if ($this->uri === null) {
            throw new InvalidArgumentException('URL is not set');
        }

        // Process URI
        $uri = $this->uri;
        $request = $this->requestFactory->createRequest($method, $uri);

        // Convert header keys to lower-case
        $lcGlobalHeaders = array_change_key_case($this->headers, CASE_LOWER);
        $lcHeaders = array_change_key_case($headers, CASE_LOWER);

        // If data exists
        if ($data !== null) {
            if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {

                // Detect content type
                if (isset($lcHeaders['content-type'])) {
                    $contentType = $lcHeaders['content-type'];
                } elseif (isset($lcGlobalHeaders['content-type'])) {
                    $contentType = $lcGlobalHeaders['content-type'];
                } else {
                    $contentType = $this->contentType;
                }

                $contentType = strtolower($contentType);
                $request = $request->withHeader('Content-Type', $contentType);

                // Process body
                if ($contentType === 'application/json') {
                    $request = $request->withBody($this->streamFactory->createStream(json_encode($data)));
                } elseif ($contentType === 'multipart/form-data') {
                    $request = $this->buildMultipartRequest($request, $data);
                } else {
                    throw new InvalidArgumentException('Invalid Content-Type');
                }
            } else {
                // If it GET or not POST, PUT, DELETE request, process it
                $uri = $uri->withQuery(http_build_query($data));
                $request = $request->withUri($uri);
            }
        }

        // Process global class headers
        foreach ($lcGlobalHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        // Process local request headers
        foreach ($lcHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // If fake response exists, we return fake response
        if ($this->fakeResponse) {
            return $this->fakeResponse;
        }

        // Get response from HttpClient
        $httpClient = $this->getCurrentHttpClient();
        $response = $httpClient->sendRequest($request);

        // Get status code
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RequestException('Request failed with status code: ' . $statusCode, $request);
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function setUri(?string $url = null): self
    {

        if ($url === null && $this->uriPrefix === '') {
            throw new InvalidArgumentException('Empty URl');
        }

        $currentUrl = $url ?? '';

        $fullUrl = $this->uriPrefix . $currentUrl;

        if (!filter_var($fullUrl, FILTER_VALIDATE_URL) || empty($fullUrl)) {
            throw new InvalidArgumentException('Invalid URL format:' . ' ' . $fullUrl);
        }

        $this->uri = $this->uriFactory->createUri($fullUrl);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUriPrefix(string $uriPrefix): self
    {
        $this->uriPrefix = $uriPrefix;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTimeout(int $timeout): self
    {
        $httpClient = $this->getCurrentHttpClient();
        if (method_exists($httpClient, 'setTimeout')) {
            $httpClient->setTimeout($timeout);
        } else {
            throw new InvalidArgumentException('The HTTP client does not support setting the timeout');
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMaxRedirects(int $maxRedirects): self
    {
        $httpClient = $this->getCurrentHttpClient();
        if (method_exists($httpClient, 'setMaxRedirects')) {
            $httpClient->setMaxRedirects($maxRedirects);
        } else {
            throw new InvalidArgumentException('The HTTP client does not support setting the max redirects');
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFollowLocation(bool $followLocation): self
    {
        $httpClient = $this->getCurrentHttpClient();
        if (method_exists($httpClient, 'setFollowLocation')) {
            $httpClient->setFollowLocation($followLocation);
        } else {
            throw new InvalidArgumentException('The HTTP client does not support setting the follow location');
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHeader(string $header, string $value): self
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function setContentType(string $contentType): self
    {
        $allowed = [
            'application/json',
            'multipart/form-data'
        ];
        if (!in_array($contentType, $allowed)) {
            throw new InvalidArgumentException("Request content type incorrect. Correct: " . implode(', ', $allowed));
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHttpClient(ClientInterface $httpClient): self
    {
        $this->httpClients['default'] = $httpClient;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addHttpClient(
            string $key,
            ClientInterface $httpClient,
            bool $makeActive = false
    ): self
    {
        if (array_key_exists($key, $this->httpClients)) {
            throw new InvalidArgumentException("HTTP client already exists:" . ' ' . $key);
        }
        $this->httpClients[$key] = $httpClient;
        if ($makeActive) {
            $this->setActiveHttpClient($key);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setActiveHttpClient(string $key): self
    {
        if (!array_key_exists($key, $this->httpClients)) {
            throw new InvalidArgumentException("HTTP client not exists:" . ' ' . $key);
        }
        $this->currentHttpClient = $key;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHttpClientList(): array
    {
        return array_keys($this->httpClients);
    }

    /**
     * @inheritDoc
     */
    public function getCurrentHttpClient(): ClientInterface
    {
        return $this->httpClients[$this->currentHttpClient];
    }

    /**
     * @inheritDoc
     */
    public function setDefaultConvertor(ResponseConvertorInterface $convertor): self
    {
        $this->convertor = $convertor;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFakeResponse(ResponseInterface $response): self
    {
        $this->fakeResponse = $response;
        return $this;
    }

}
