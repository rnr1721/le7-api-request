<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface ResponseConvertorInterface
{

    public function get(?ResponseInterface $response = null): mixed;
}
