<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Core\Utils\ResponseConvertors\ResponseArrayConvertor;
use Core\Utils\ResponseConvertors\ResponseObjectConvertor;
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
     * @return ResponseArrayConvertor
     */
    public function toArray(
            ?ResponseInterface $response = null
    ): ResponseArrayConvertor;

    /**
     * Get converter to object
     * 
     * @param ResponseInterface|null $response
     * @return ResponseObjectConvertor
     */
    public function toObject(
            ?ResponseInterface $response = null
    ): ResponseObjectConvertor;

    /**
     * Get data from default converter
     * 
     * @return mixed
     */
    public function get(): mixed;
}
