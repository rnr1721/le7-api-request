<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Core\Utils\ResponseConvertors\ResponseJsonArrayConvertor;
use Core\Utils\ResponseConvertors\ResponseJsonObjectConvertor;
use Psr\Http\Message\ResponseInterface;

/**
 * Factory for get converters
 */
interface ResponseConvertorFactoryInterface
{

    /**
     * Get converter to array
     * 
     * @param ResponseInterface|null $response
     * @return ResponseJsonArrayConvertor
     */
    public function toArray(
            ?ResponseInterface $response = null
    ): ResponseJsonArrayConvertor;

    /**
     * Get converter to object
     * 
     * @param ResponseInterface|null $response
     * @return ResponseJsonObjectConvertor
     */
    public function toObject(
            ?ResponseInterface $response = null
    ): ResponseJsonObjectConvertor;

    /**
     * Get data from default converter
     * 
     * @return mixed
     */
    public function get(): mixed;
}
