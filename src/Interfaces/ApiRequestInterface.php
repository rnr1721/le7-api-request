<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\ResponseConvertorDataInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientInterface;

/**
 * Interface ApiRequestInterface
 *
 * Represents an API request.
 */
interface ApiRequestInterface
{

    /**
     * Perform a GET request.
     *
     * @param string|null $uri Uri or uri suffox to request
     * @param array|null $data The request data.
     * @param array $headers The request headers.
     * @return ResponseConvertorDataInterface The API response.
     */
    public function get(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface;

    /**
     * Perform a POST request.
     *
     * @param string|null $uri Uri or uri suffox to request
     * @param array|null $data The request data.
     * @param array $headers The request headers.
     * @return ResponseConvertorDataInterface The API response.
     */
    public function post(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface;

    /**
     * Perform a PUT request.
     *
     * @param string|null $uri Uri or uri suffox to request
     * @param array|null $data The request data.
     * @param array $headers The request headers.
     * @return ResponseConvertorDataInterface The API response.
     */
    public function put(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface;

    /**
     * Perform a PATCH request.
     *
     * @param string|null $uri Uri or uri suffox to request
     * @param array|null $data The request data.
     * @param array $headers The request headers.
     * @return ResponseConvertorDataInterface The API response.
     */
    public function patch(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface;

    /**
     * Perform a DELETE request.
     *
     * @param string|null $uri Uri or uri suffox to request
     * @param array|null $data The request data.
     * @param array $headers The request headers.
     * @return ResponseConvertorDataInterface The API response.
     */
    public function delete(
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseConvertorDataInterface;

    /**
     * Get convertor factory to easy convert to some format
     * and make request
     * 
     * @param string $method
     * @param string|null $uri Uri or uri suffox to request
     * @param array|null $data
     * @param array $headers
     * @param ResponseConvertorInterface $convertor
     * @return ResponseConvertorDataInterface
     */
    public function request(
            string $method,
            ?string $uri = null,
            ?array $data = null,
            array $headers = [],
            ResponseConvertorInterface $convertor = null
    ): ResponseConvertorDataInterface;

    /**
     * Perform a custom request and get response.
     *
     * @param string $method The request method.
     * @param string|null $uri Uri or uri suffox to request
     * @param array|null $data The request data.
     * @param array $headers The request headers.
     * @return ResponseInterface The API response.
     */
    public function getResponse(
            string $method,
            ?string $uri = null,
            ?array $data = null,
            array $headers = []
    ): ResponseInterface;

    /**
     * Set the URI for the request.
     *
     * @param string|null $url The URI.
     * @return self
     */
    public function setUri(?string $url = null): self;

    /**
     * Set URI prefix for all URI
     * 
     * @param string $uriPrefix Example: https://example.com
     * @return self
     */
    public function setUriPrefix(string $uriPrefix): self;

    /**
     * Set the timeout for the request.
     *
     * @param int $timeout The timeout value in seconds.
     * @return self
     */
    public function setTimeout(int $timeout): self;

    /**
     * The max number of redirects to follow.
     * 
     * @param int $maxRedirects Default is 3
     * @return self
     */
    public function setMaxRedirects(int $maxRedirects): self;

    /**
     * Follow Location header redirects.
     * 
     * @param bool $followLocation Default is true
     * @return self
     */
    public function setFollowLocation(bool $followLocation): self;

    /**
     * Set predefined headers for each request
     * So, each request will be send with these headers
     * 
     * @param array $headers Key=>Value array
     * @return self
     */
    public function setHeaders(array $headers): self;

    /**
     * Set predefined header. Data will be overwriten if exists
     * This will be global header. Each request will be with this header
     * 
     * @param string $header Header name
     * @param string $value Header value
     * @return self
     */
    public function setHeader(string $header, string $value): self;

    /**
     * Set data format
     * 
     * Allowed:
     * application/json
     * multipart/form-data
     * application/x-www-form-urlencoded
     * 
     * @param string $contentType
     * @return self
     */
    public function setContentType(string $contentType): self;

    /**
     * Set the default HTTP client for the request.
     * This will overwrite current client
     *
     * @param ClientInterface $httpClient The HTTP client.
     * @return self
     */
    public function setHttpClient(ClientInterface $httpClient): self;

    /**
     * Get current active HTTP client
     * 
     * @return ClientInterface
     */
    public function getCurrentHttpClient(): ClientInterface;

    /**
     * Set current active HTTP client
     * 
     * @param string $key
     * @return self
     */
    public function setActiveHttpClient(string $key): self;

    /**
     * Add another HTTP client and possible make it current active
     * 
     * @param string $key Name of HTTP client
     * @param ClientInterface $httpClient HTTP client
     * @param bool $makeActive This $httpClient will be set as current
     * @return self
     */
    public function addHttpClient(
            string $key,
            ClientInterface $httpClient,
            bool $makeActive = false
    ): self;

    /**
     * Get list of available HTTP client keys
     * 
     * @return array
     */
    public function getHttpClientList(): array;

    /**
     * Set default response convertor
     * 
     * @param ResponseConvertorInterface $convertor
     * @return self
     */
    public function setDefaultConvertor(
            ResponseConvertorInterface $convertor
    ): self;

    /**
     * Set a fake response for testing purposes.
     *
     * @param ResponseInterface $response The fake response.
     * @return self
     */
    public function setFakeResponse(ResponseInterface $response): self;

    /**
     * Get last request
     * 
     * @param ResponseConvertorInterface|null $convertor
     * @return ResponseConvertorDataInterface|null
     */
    public function getLast(
            ?ResponseConvertorInterface $convertor = null
    ): ResponseConvertorDataInterface|null;
}
