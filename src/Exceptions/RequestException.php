<?php

namespace Core\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use \Exception;
use \Throwable;

class RequestException extends Exception implements RequestExceptionInterface
{

    private RequestInterface $request;

    public function __construct(string $message, RequestInterface $request, Throwable $previous = null)
    {
        $this->request = $request;
        parent::__construct($message, 0, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

}
