<?php

declare(strict_types=1);

namespace Core\Factories;

use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\HttpClientFactoryInterface;
use Core\Interfaces\ApiRequestInterface;
use Core\Utils\ApiRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Factory for creating instances of ApiRequestInterface.
 */
class HttpClientFactory implements HttpClientFactoryInterface
{

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
     * PSR StreamFactoryInterface implementation
     * 
     * @var StreamFactoryInterface
     */
    protected StreamFactoryInterface $streamFactory;

    /**
     * PSR ClientInterface implementation
     * 
     * @var ClientInterface
     */
    protected ClientInterface $httpClient;

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
     * @param StreamFactoryInterface $streamFactory The StreamFactoryInterface implementation.
     * @param ClientInterface $httpClient PSR ClientInterface implementation
     * @param EventDispatcherInterface|null $eventDispatcher Optional PSR EventDispatcherInterface implementation
     */
    public function __construct(
            UriFactoryInterface $uriFactory,
            RequestFactoryInterface $requestFactory,
            StreamFactoryInterface $streamFactory,
            ClientInterface $httpClient,
            ?EventDispatcherInterface $eventDispatcher = null
    )
    {
        $this->uriFactory = $uriFactory;
        $this->requestFactory = $requestFactory;
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
                $this->httpClient,
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
        return $this->httpClient;
    }

}
