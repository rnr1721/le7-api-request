<?php

declare(strict_types=1);

namespace Core\Utils\ResponseConvertors;

use Core\Interfaces\ResponseConvertorInterface;
use Psr\Http\Message\ResponseInterface;
use \RuntimeException;

/**
 * ResponseInterface to array convertor
 */
class ResponseJsonArrayConvertor implements ResponseConvertorInterface
{

    protected ?ResponseInterface $response = null;

    public function __construct(?ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    /**
     * Get array from ResponseInterface
     * 
     * @param ResponseInterface|null $response
     * @return array
     * @throws RuntimeException
     */
    public function get(?ResponseInterface $response = null): array
    {
        if ($response !== null) {
            $this->response = $response;
        }

        if (empty($this->response)) {
            throw new RuntimeException('Response is empty');
        }

        $body = (string) $this->response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode JSON: ' . json_last_error_msg());
        }

        return $data;
    }

}
