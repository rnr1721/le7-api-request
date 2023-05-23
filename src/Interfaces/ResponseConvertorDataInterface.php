<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface for get data from response
 */
interface ResponseConvertorDataInterface
{

    /**
     * Get ResponseInterface body converted to array
     * 
     * @return array
     */
    public function toArray(): array;

    /**
     * Get ResponseInterface body converted to object
     * 
     * @return object
     */
    public function toObject(): object;

    /**
     * Get ResponseInterface body converted to own format
     * from own implementation of ResponseConverterInterface
     * 
     * @return mixed
     */
    public function ownFormat(): mixed;

    /**
     * Get ResponseInterface object
     * 
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;
}
