<?php

use Core\Utils\ApiRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ApiRequestTest extends TestCase
{

    public function testGetRequestWithFakeResponse()
    {

        $uriFactory = $this->createMock(UriFactoryInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $httpClient = $this->createMock(ClientInterface::class);
        $fakeResponse = $this->createMock(ResponseInterface::class);

        $apiRequest = new ApiRequest($uriFactory, $requestFactory, $streamFactory, $httpClient);

        $apiRequest->setFakeResponse($fakeResponse);

        $result = $apiRequest->setUri('http://example.com')->get();

        $this->assertSame($fakeResponse, $result);
    }

}
