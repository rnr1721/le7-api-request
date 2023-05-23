<?php

declare(strict_types=1);

namespace Core\Factories;

use Psr\Http\Message\ResponseInterface;
use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\ResponseConvertorFactoryInterface;
use Core\Utils\ResponseConvertors\ResponseArrayConvertor;
use Core\Utils\ResponseConvertors\ResponseObjectConvertor;
use \InvalidArgumentException;

/**
 * Factory for response converters
 */
class ResponseConvertorFactory implements ResponseConvertorFactoryInterface
{

    /**
     * PSR response
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $response = null;

    /**
     * Response converter if exists
     * @var ResponseConvertorInterface|null
     */
    protected ?ResponseConvertorInterface $convertor = null;

    public function __construct(
            ?ResponseInterface $response = null,
            ?ResponseConvertorInterface $convertor = null
    )
    {
        $this->response = $response;
        $this->convertor = $convertor;
    }

    /**
     * @inheritDoc
     */
    public function arrayConvertor(
            ?ResponseInterface $response = null
    ): ResponseArrayConvertor
    {
        return new ResponseArrayConvertor($this->getResponse($response));
    }

    /**
     * @inheritDoc
     */
    public function objectConvertor(
            ?ResponseInterface $response = null
    ): ResponseObjectConvertor
    {
        return new ResponseObjectConvertor($this->getResponse($response));
    }

    /**
     * @inheritDoc
     */
    public function getResponse(?ResponseInterface $response = null): ResponseInterface
    {
        $currentResponse = $response ?? $this->response;
        if ($currentResponse === null) {
            throw new InvalidArgumentException('You must set ResponseInterface');
        }
        return $currentResponse;
    }

}
