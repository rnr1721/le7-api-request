<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Core\Interfaces\ApiRequestInterface;
use Psr\Http\Client\ClientInterface;

/**
 * Main factory for creating ready-for-use
 * ClientInterface and ApiRequestInterface
 */
interface HttpClientFactoryInterface
{

    /**
     * Get an instance of the API request.
     * 
     * @param string|null $uriPrefix Prefix for all URLs
     * @param ResponseConvertorInterface|null $convertor Default convertor
     * @return ApiRequestInterface Ready to use ApiRequest
     */
    public function getApiRequest(
            ?string $uriPrefix = null,
            ?ResponseConvertorInterface $convertor = null
    ): ApiRequestInterface;

    /**
     * Get an instance of the default HTTP client.
     *
     * @return ClientInterface The default HTTP client instance.
     */
    public function getHttpClient(): ClientInterface;

    /**
     * Set default HttpClient if you not inject in constructor
     * own ClientInterface implementation
     * 
     * @param string $defaultClient curl or php
     * @return self
     */
    public function setDefaultHttpClient(string $defaultClient): self;
}
