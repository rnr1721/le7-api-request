<?php

declare(strict_types=1);

namespace Core\Utils;

use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\ResponseConvertorDataInterface;
use Core\Interfaces\ApiRequestInterface;
use Core\Events\AfterApiRequestEvent;
use Core\Factories\ResponseConvertorData;
use Psr\Http\Message\UriInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Core\Exceptions\RequestException;
use Core\Exceptions\ApiRequestException;
use function http_build_query,
             parse_url,
             method_exists,
             array_change_key_case,
             array_keys,
             array_key_exists,
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
     * Content types that allowed to send
     * @var array
     */
    protected array $allowedContentTypes = [
        'application/json',
        'multipart/form-data',
        'application/x-www-form-urlencoded'
    ];

    /**
     * Local headers. This headers will be cleared after each request
     * @var array
     */
    protected array $headers = [];

    /**
     * Global headers. This headers will be permanent for each request
     * @var array
     */
    protected array $globalHeaders = [];

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
     * Last response if exists
     * 
     * @var ResponseInterface
     */
    protected ?ResponseInterface $lastResponse = null;

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
     * Optionsl PSR event dispatcher
     * 
     * @var EventDispatcherInterface|null
     */
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * ApiRequest Constructor
     * 
     * @param UriFactoryInterface $uriFactory
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param ClientInterface $httpClient
     * @param ResponseConvertorInterface|null $convertor
     * @param string|null $uriPrefix Uri prefix
     * @param EventDispatcherInterface|null $eventDispatcher Optional PSR events
     */
    public function __construct(
            UriFactoryInterface $uriFactory,
            RequestFactoryInterface $requestFactory,
            StreamFactoryInterface $streamFactory,
            ClientInterface $httpClient,
            ?ResponseConvertorInterface $convertor = null,
            ?string $uriPrefix = null,
            ?EventDispatcherInterface $eventDispatcher = null
    )
    {
        $this->uriFactory = $uriFactory;
        $this->requestFactory = $requestFactory;
        $this->httpClients['default'] = $httpClient;
        $this->streamFactory = $streamFactory;
        $this->convertor = $convertor;
        $this->uriPrefix = $uriPrefix ?? '';
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function get(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface
    {
        return $this->request('GET', $uri, $data, $headers);
    }

    /**
     * @inheritdoc
     */
    public function post(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface
    {
        return $this->request('POST', $uri, $data, $headers);
    }

    /**
     * @inheritdoc
     */
    public function put(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface
    {
        return $this->request('PUT', $uri, $data, $headers);
    }

    /**
     * @inheritdoc
     */
    public function delete(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface
    {
        return $this->request('DELETE', $uri, $data, $headers);
    }

    /**
     * @inheritdoc
     */
    public function patch(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface
    {
        return $this->request('PATCH', $uri, $data, $headers);
    }

    /**
     * @inheritdoc
     */
    public function request(
            string $method,
            ?string $uri = null,
            ?array $data = null,
            array $headers = [],
            ?ResponseConvertorInterface $convertor = null
    ): ResponseConvertorDataInterface
    {
        $response = $this->getResponse($method, $uri, $data, $headers);
        $currentConvertor = $convertor ?? $this->convertor;
        return new ResponseConvertorData($response, $currentConvertor);
    }

    /**
     * @inheritDoc
     */
    public function getResponse(
            string $method,
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseInterface
    {

        if ($uri) {
            $this->setUri($uri);
        }

        if ($this->uri === null) {
            throw new ApiRequestException('URL is not set');
        }

        // Process URI
        $fullUri = $this->uri;
        $request = $this->requestFactory->createRequest($method, $fullUri);

        // Convert header keys to lower-case
        // Global headers, permanent for each request
        $lcGlobalHeaders = array_change_key_case($this->globalHeaders, CASE_LOWER);
        // Local headers, will be cleared after request
        $lcLocalHeaders = array_change_key_case($this->headers, CASE_LOWER);
        // Headers from method arguments
        $lcHeaders = array_change_key_case($headers, CASE_LOWER);

        if ($data !== null) {
            if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE' || $method === 'PATCH') {

                // Detect content type
                if (isset($lcHeaders['content-type'])) {
                    $contentType = $lcHeaders['content-type'];
                } elseif (isset($lcLocalHeaders['content-type'])) {
                    $contentType = $lcLocalHeaders['content-type'];
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
                } elseif ($contentType === 'application/x-www-form-urlencoded') {
                    $request = $request->withBody($this->streamFactory->createStream(http_build_query($data)));
                } else {
                    throw new ApiRequestException('Invalid Content-Type. Allowed: ' . implode(',', $this->allowedContentTypes));
                }
            } else {
                // If it GET or not POST, PUT, DELETE request, process it
                $fullUri = $fullUri->withQuery(http_build_query($data));
                $request = $request->withUri($fullUri);
            }
        }

        // Process global class headers
        foreach ($lcGlobalHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        // Process local class headers
        foreach ($lcLocalHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        // Process local request headers
        foreach ($lcHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($this->fakeResponse === null) {
            // Get response from real HttpClient
            $httpClient = $this->getCurrentHttpClient();
            $response = $httpClient->sendRequest($request);
        } else {
            // If fake response exists
            $response = $this->fakeResponse;
        }

        // Get status code
        $statusCode = $response->getStatusCode();
        if ($statusCode === 404) {
            throw new RequestException('Not found: ' . $statusCode, $request);
        } elseif ($statusCode < 200 || $statusCode >= 300) {
            throw new RequestException('Request failed with status code: ' . $statusCode, $request);
        }

        // If we have event dispatcher, we can for example log request
        if ($this->eventDispatcher !== null) {
            $afterRequestEvent = new AfterApiRequestEvent(
                    $request,
                    $response,
                    $method,
                    (string) $fullUri,
                    $data,
                    $headers
            );
            $this->eventDispatcher->dispatch($afterRequestEvent);
            $response = $afterRequestEvent->getResponse();
        }

        // Set last response
        $this->lastResponse = $response;

        $this->headers = [];
        $this->uri = null;

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function setUri(?string $url = null): self
    {

        if ($url === null && $this->uriPrefix === '') {
            throw new ApiRequestException('Empty URl');
        }

        $currentUrl = $url ?? '';

        $fullUrl = $this->uriPrefix . $currentUrl;

        $urlParts = parse_url($fullUrl);

        if ($urlParts === false || !isset($urlParts['scheme'], $urlParts['host'])) {
            throw new ApiRequestException('Invalid URL format: ' . $fullUrl);
        }

        $this->uri = $this->uriFactory->createUri($fullUrl);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setUriPrefix(string $uriPrefix): self
    {
        $this->uriPrefix = $uriPrefix;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTimeout(int $timeout): self
    {
        $httpClient = $this->getCurrentHttpClient();
        if (method_exists($httpClient, 'setTimeout')) {
            $httpClient->setTimeout($timeout);
        } else {
            throw new ApiRequestException('The HTTP client does not support setting the timeout');
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMaxRedirects(int $maxRedirects): self
    {
        $httpClient = $this->getCurrentHttpClient();
        if (method_exists($httpClient, 'setMaxRedirects')) {
            $httpClient->setMaxRedirects($maxRedirects);
        } else {
            throw new ApiRequestException('The HTTP client does not support setting the max redirects');
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFollowLocation(bool $followLocation): self
    {
        $httpClient = $this->getCurrentHttpClient();
        if (method_exists($httpClient, 'setFollowLocation')) {
            $httpClient->setFollowLocation($followLocation);
        } else {
            throw new ApiRequestException('The HTTP client does not support setting the follow location');
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHeader(string $header, string $value): self
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function setGlobalHeader(string $header, string $value): self
    {
        $this->globalHeaders[$header] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setGlobalHeaders(array $headers): self
    {
        $this->globalHeaders = $headers;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setContentType(string $contentType): self
    {

        if (!in_array($contentType, $this->allowedContentTypes)) {
            throw new ApiRequestException("Request content type incorrect. Correct: " . implode(', ', $this->allowedContentTypes));
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
            throw new ApiRequestException("HTTP client already exists:" . ' ' . $key);
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
            throw new ApiRequestException("HTTP client not exists:" . ' ' . $key);
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
    public function getLast(
            ?ResponseConvertorInterface $convertor = null
    ): ResponseConvertorDataInterface|null
    {
        if ($this->lastResponse === null) {
            return null;
        }
        $currentConvertor = $convertor ?? $this->convertor;
        return new ResponseConvertorData($this->lastResponse, $currentConvertor);
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
