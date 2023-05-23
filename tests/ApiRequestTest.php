<?php

use Core\Interfaces\ResponseConvertorDataInterface;
use Core\Interfaces\ResponseConvertorInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Core\Utils\ApiRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ApiRequestTest extends TestCase
{

    protected function createApiRequest(
            ClientInterface $httpClient,
            RequestFactoryInterface $requestFactory,
            StreamFactoryInterface $streamFactory,
            UriFactoryInterface $uriFactory
    ): ApiRequest
    {
        return new ApiRequest(
                $uriFactory,
                $requestFactory,
                $streamFactory,
                $httpClient
        );
    }

    public function testGet()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
                ->method('getStatusCode')
                ->willReturn(200);

        $httpClientMock = $this->createMock(ClientInterface::class);
        $httpClientMock->expects($this->once())
                ->method('sendRequest')
                ->willReturn($responseMock);

        $apiRequest = $this->createApiRequest(
                $httpClientMock,
                $this->createMock(RequestFactoryInterface::class),
                $this->createMock(StreamFactoryInterface::class),
                $this->createMock(UriFactoryInterface::class)
        );

        $response = $apiRequest->setUri('https://example.com/api')->get();

        $this->assertInstanceOf(ResponseInterface::class, $response->getResponse());
        $this->assertEquals(200, $response->getResponse()->getStatusCode());
    }

    public function testPost()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
                ->method('getStatusCode')
                ->willReturn(201);

        $httpClientMock = $this->createMock(ClientInterface::class);
        $httpClientMock->expects($this->once())
                ->method('sendRequest')
                ->willReturn($responseMock);

        $apiRequest = $this->createApiRequest(
                $httpClientMock,
                $this->createMock(RequestFactoryInterface::class),
                $this->createMock(StreamFactoryInterface::class),
                $this->createMock(UriFactoryInterface::class)
        );

        $response = $apiRequest->setUri('https://example.com/api')->post();

        $this->assertInstanceOf(ResponseInterface::class, $response->getResponse());
        $this->assertEquals(201, $response->getResponse()->getStatusCode());
    }

    public function testPut()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
                ->method('getStatusCode')
                ->willReturn(204);

        $httpClientMock = $this->createMock(ClientInterface::class);
        $httpClientMock->expects($this->once())
                ->method('sendRequest')
                ->willReturn($responseMock);

        $apiRequest = $this->createApiRequest(
                $httpClientMock,
                $this->createMock(RequestFactoryInterface::class),
                $this->createMock(StreamFactoryInterface::class),
                $this->createMock(UriFactoryInterface::class)
        );

        $response = $apiRequest->setUri('https://example.com/api')->put();

        $this->assertInstanceOf(ResponseInterface::class, $response->getResponse());
        $this->assertEquals(204, $response->getResponse()->getStatusCode());
    }

    public function testDelete()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
                ->method('getStatusCode')
                ->willReturn(204);

        $httpClientMock = $this->createMock(ClientInterface::class);
        $httpClientMock->expects($this->once())
                ->method('sendRequest')
                ->willReturn($responseMock);

        $apiRequest = $this->createApiRequest(
                $httpClientMock,
                $this->createMock(RequestFactoryInterface::class),
                $this->createMock(StreamFactoryInterface::class),
                $this->createMock(UriFactoryInterface::class)
        );

        $response = $apiRequest->setUri('https://example.com/api')->delete();

        $this->assertInstanceOf(ResponseInterface::class, $response->getResponse());
        $this->assertEquals(204, $response->getResponse()->getStatusCode());
    }

    public function testRequest()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
                ->method('getStatusCode')
                ->willReturn(204);

        $httpClientMock = $this->createMock(ClientInterface::class);
        $httpClientMock->expects($this->once())
                ->method('sendRequest')
                ->willReturn($responseMock);

        $apiRequest = $this->createApiRequest(
                $httpClientMock,
                $this->createMock(RequestFactoryInterface::class),
                $this->createMock(StreamFactoryInterface::class),
                $this->createMock(UriFactoryInterface::class)
        );

        $response = $apiRequest->setUri('https://example.com/api')->request('GET');

        $this->assertInstanceOf(ResponseInterface::class, $response->getResponse());
        $this->assertEquals(204, $response->getResponse()->getStatusCode());
    }

    public function testGetResponse()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
                ->method('getStatusCode')
                ->willReturn(204);

        $httpClientMock = $this->createMock(ClientInterface::class);
        $httpClientMock->expects($this->once())
                ->method('sendRequest')
                ->willReturn($responseMock);

        $apiRequest = $this->createApiRequest(
                $httpClientMock,
                $this->createMock(RequestFactoryInterface::class),
                $this->createMock(StreamFactoryInterface::class),
                $this->createMock(UriFactoryInterface::class)
        );

        $response = $apiRequest->setUri('https://example.com/api')->getResponse('GET');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

}
