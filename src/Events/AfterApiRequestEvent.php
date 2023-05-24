<?php

namespace Core\Events;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class AfterApiRequestEvent implements StoppableEventInterface
{

    /**
     * @var bool Indicates if event propagation is stopped.
     */
    protected bool $propagationStopped = false;

    /**
     * @var RequestInterface The current request
     */
    protected RequestInterface $request;

    /**
     * @var ResponseInterface The response received from the API request.
     */
    protected ResponseInterface $response;

    /**
     * @var string The HTTP method used for the API request.
     */
    protected string $method;

    /**
     * @var string The URI of the API request.
     */
    protected string $uri;

    /**
     * @var array|null The data sent with the API request.
     */
    protected ?array $data;

    /**
     * @var array|null The headers sent with the API request.
     */
    protected ?array $headers;

    /**
     * Create a new AfterApiRequestEvent instance.
     *
     * @param RequestInterface $request PSR RequestInterface
     * @param ResponseInterface $response The response received from the API request.
     * @param string $method The HTTP method used for the API request.
     * @param string $uri The URI of the API request.
     * @param array|null $data The data sent with the API request.
     * @param array|null $headers The headers sent with the API request.
     */
    public function __construct(
            RequestInterface $request,
            ResponseInterface $response,
            string $method,
            string $uri,
            ?array $data,
            ?array $headers
    )
    {
        $this->request = $request;
        $this->response = $response;
        $this->method = $method;
        $this->uri = $uri;
        $this->data = $data;
        $this->headers = $headers;
    }

    /**
     * Determine if event propagation has been stopped.
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stop the propagation of the event.
     *
     * @return void
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Get the API request that send.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Get the response received from the API request.
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the response received from the API request.
     *
     * @param ResponseInterface $response
     * @return self
     */
    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get the HTTP method used for the API request.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the URI of the API request.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get the data sent with the API request.
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Get the headers sent with the API request.
     *
     * @return array|null
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

}
