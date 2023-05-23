<?php

declare(strict_types=1);

namespace Core\Factories;

use Psr\Http\Message\ResponseInterface;
use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\ResponseConvertorDataInterface;
use Core\Utils\ResponseConvertors\ResponseArrayConvertor;
use Core\Utils\ResponseConvertors\ResponseObjectConvertor;
use \InvalidArgumentException;

/**
 * Class for get converted response data
 */
class ResponseConvertorData implements ResponseConvertorDataInterface
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
    public function toArray(): array
    {
        $convertor = new ResponseArrayConvertor($this->response);
        return $convertor->get();
    }

    /**
     * @inheritDoc
     */
    public function toObject(): object
    {
        $convertor = new ResponseObjectConvertor($this->response);
        return $convertor->get();
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function ownFormat(): mixed
    {
        if ($this->convertor === null) {
            throw new InvalidArgumentException("You must set default converter");
        }
        return $this->convertor->get($this->response);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

}
