<?php

declare(strict_types=1);

namespace Core\Factories;

use Core\HttpClient\HttpClientCurl;
use Core\HttpClient\HttpClientDefault;
use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\HttpClientFactoryInterface;
use Core\Interfaces\ApiRequestInterface;
use Core\Utils\ApiRequest;
use Core\Exceptions\ApiRequestException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Factory for creating instances of ApiRequestInterface.
 */
class HttpClientFactory implements HttpClientFactoryInterface
{

    /**
     * Default HttpClient if user not set own
     * @var string
     */
    protected string $defaultHttpClient = 'curl';

    /**
     * PSR UriFactoryInterface implementation
     * 
     * @var UriFactoryInterface
     */
    protected UriFactoryInterface $uriFactory;

    /**
     * PSR RequestFactoryInterface implementation
     * 
     * @var RequestFactoryInterface
     */
    protected RequestFactoryInterface $requestFactory;

    /**
     * PSR ResponseFactory implementation
     * 
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * PSR StreamFactoryInterface implementation
     * 
     * @var StreamFactoryInterface
     */
    protected StreamFactoryInterface $streamFactory;

    /**
     * PSR ClientInterface implementation
     * 
     * @var ClientInterface|null
     */
    protected ?ClientInterface $httpClient = null;

    /**
     * PSR EventDispatcherInterface implementation for events
     * 
     * @var EventDispatcherInterface|null
     */
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * ClientFactory constructor
     * @param UriFactoryInterface $uriFactory The UriFactoryInterface implementation.
     * @param RequestFactoryInterface $requestFactory The RequestFactoryInterface implementation.
     * @param ResponseFactoryInterface $responseFactory PSR ResponseFactoryInterface implementation
     * @param StreamFactoryInterface $streamFactory The StreamFactoryInterface implementation.
     * @param ClientInterface|null $httpClient PSR ClientInterface implementation
     * @param EventDispatcherInterface|null $eventDispatcher Optional PSR EventDispatcherInterface implementation
     */
    public function __construct(
            UriFactoryInterface $uriFactory,
            RequestFactoryInterface $requestFactory,
            ResponseFactoryInterface $responseFactory,
            StreamFactoryInterface $streamFactory,
            ?ClientInterface $httpClient = null,
            ?EventDispatcherInterface $eventDispatcher = null
    )
    {
        $this->uriFactory = $uriFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->httpClient = $httpClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritDoc
     */
    public function getApiRequest(
            ?string $uriPrefix = null,
            ?ResponseConvertorInterface $convertor = null
    ): ApiRequestInterface
    {
        return new ApiRequest(
                $this->uriFactory,
                $this->requestFactory,
                $this->streamFactory,
                $this->getHttpClient(),
                $convertor,
                $uriPrefix,
                $this->eventDispatcher
        );
    }

    /**
     * @inheritDoc
     */
    public function getHttpClient(): ClientInterface
    {
        if ($this->httpClient === null) {
            $msg = "You need HttpClient (ClientInterface) implementation. Simplest way - run 'composer require rnr1721/le7-http-client'. Or, please inject in constructor own ClientInterface implementation";
            if ($this->defaultHttpClient === 'curl') {
                if (class_exists(HttpClientCurl::class)) {
                    $this->httpClient = new HttpClientCurl($this->responseFactory);
                } else {
                    throw new ApiRequestException($msg);
                }
            } elseif ($this->defaultHttpClient === 'php') {
                if (class_exists(HttpClientDefault::class)) {
                    $this->httpClient = new HttpClientDefault($this->responseFactory);
                } else {
                    throw new ApiRequestException($msg);
                }
            } else {
                throw new ApiRequestException($msg);
            }
        }
        return $this->httpClient;
    }

    /**
     * @inheritDoc
     */
    public function setDefaultHttpClient(string $defaultClient): self
    {
        $allowed = ['curl', 'php'];
        if (!in_array($defaultClient, $allowed)) {
            throw new ApiRequestException("Default HttpClient ban be" . ' ' . implode(' or ', $allowed));
        }
        $this->defaultHttpClient = $defaultClient;
        return $this;
    }

}
