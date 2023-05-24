<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Core\Exceptions\ResponseConvertorException;

/**
 * Convertor, that converts data from ResponseInterface to some
 * format, detecting by Content-Type header
 */
interface ResponseConvertorInterface
{

    /**
     * Get the converted response body.
     *
     * @param ResponseInterface|null $response The response object to be converted (optional).
     * @return mixed The converted response body as an array.
     * @throws ResponseConvertorException If the response is empty or if the data format is unsupported.
     */
    public function get(?ResponseInterface $response = null): mixed;
}
