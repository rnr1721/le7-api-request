<?php

declare(strict_types=1);

namespace Core\Factories;

use Core\Interfaces\ResponseConvertorInterface;
use Core\Interfaces\ResponseConvertorDataInterface;
use Core\Utils\ResponseConvertors\ResponseArrayConvertor;
use Core\Utils\ResponseConvertors\ResponseObjectConvertor;
use Core\Exceptions\ApiRequestException;
use Psr\Http\Message\ResponseInterface;

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
     * @throws ApiRequestException
     */
    public function ownFormat(): mixed
    {
        if ($this->convertor === null) {
            throw new ApiRequestException("You must set default converter");
        }
        return $this->convertor->get($this->response);
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

}
