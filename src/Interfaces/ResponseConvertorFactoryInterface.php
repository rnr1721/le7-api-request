<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Core\Utils\ResponseConvertors\ResponseArrayConvertor;
use Core\Utils\ResponseConvertors\ResponseObjectConvertor;
use Psr\Http\Message\ResponseInterface;

/**
 * Factory for get ResponseInterface converters to different formats
 */
interface ResponseConvertorFactoryInterface
{

    /**
     * Get ResponseInterface converter to array
     * 
     * @param ResponseInterface|null $response
     * @return ResponseArrayConvertor
     */
    public function arrayConvertor(
            ?ResponseInterface $response = null
    ): ResponseArrayConvertor;

    /**
     * Get ResponseInterface converter to object
     * 
     * @param ResponseInterface|null $response
     * @return ResponseObjectConvertor
     */
    public function objectConvertor(
            ?ResponseInterface $response = null
    ): ResponseObjectConvertor;

    /**
     * Get current ResponseInterface
     * 
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function getResponse(
            ?ResponseInterface $response = null
    ): ResponseInterface;
}
