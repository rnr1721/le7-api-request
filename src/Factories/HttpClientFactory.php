<?php

declare(strict_types=1);

namespace Core\Factories;

use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\HttpClientFactoryInterface;
use Core\Interfaces\ApiRequestInterface;
use Core\Utils\ApiRequest;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Factory for creating instances of ApiRequestInterface.
 */
class HttpClientFactory implements HttpClientFactoryInterface
{

    protected UriFactoryInterface $uriFactory;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;
    protected ClientInterface $httpClient;

    /**
     * ClientFactory constructor
     * @param UriFactoryInterface $uriFactory The UriFactoryInterface implementation.
     * @param RequestFactoryInterface $requestFactory The RequestFactoryInterface implementation.
     * @param StreamFactoryInterface $streamFactory The StreamFactoryInterface implementation.
     * @param ClientInterface $httpClient PSR ClientInterface implementation
     */
    public function __construct(
            UriFactoryInterface $uriFactory,
            RequestFactoryInterface $requestFactory,
            StreamFactoryInterface $streamFactory,
            ClientInterface $httpClient
    )
    {
        $this->uriFactory = $uriFactory;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->httpClient = $httpClient;
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
                $uriPrefix
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
