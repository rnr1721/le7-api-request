<?php

declare(strict_types=1);

namespace Core\Factories;

use Psr\Http\Message\ResponseInterface;
use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\ResponseConvertorFactoryInterface;
use Core\Utils\ResponseConvertors\ResponseJsonArrayConvertor;
use Core\Utils\ResponseConvertors\ResponseJsonObjectConvertor;
use \InvalidArgumentException;

/**
 * Factory for response converters
 */
class ResponseConvertorFactory implements ResponseConvertorFactoryInterface
{

    /**
     * PSR response
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * Response converter if exists
     * @var ResponseConvertorInterface|null
     */
    protected ?ResponseConvertorInterface $convertor = null;

    public function __construct(
            ResponseInterface $response,
            ?ResponseConvertorInterface $convertor = null
    )
    {
        $this->response = $response;
        $this->convertor = $convertor;
    }

    /**
     * @inheritDoc
     */
    public function toArray(
            ?ResponseInterface $response = null
    ): ResponseJsonArrayConvertor
    {
        return new ResponseJsonArrayConvertor($response ?? $this->response);
    }

    /**
     * @inheritDoc
     */
    public function toObject(
            ?ResponseInterface $response = null
    ): ResponseJsonObjectConvertor
    {
        return new ResponseJsonObjectConvertor($response ?? $this->response);
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function get(): mixed
    {
        if ($this->convertor === null) {
            throw new InvalidArgumentException("You must set custom converter");
        }
        return $this->convertor->get($this->response);
    }

}
