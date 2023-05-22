<?php

use PHPUnit\Framework\TestCase;
use Core\Factories\HttpClientFactory;
use Core\Utils\ApiRequest;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Client\ClientInterface;

class ClientFactoryTest extends TestCase
{
    public function testGetApiRequest()
    {
        // Создаем заглушки для зависимостей
        $uriFactory = $this->createMock(UriFactoryInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $httpClient = $this->createMock(ClientInterface::class);

        // Создаем экземпляр фабрики
        $factory = new HttpClientFactory($uriFactory, $requestFactory, $streamFactory, $httpClient);

        // Проверяем, что возвращается экземпляр ApiRequest
        $this->assertInstanceOf(ApiRequest::class, $factory->getApiRequest());
    }

    public function testGetHttpClient()
    {
        // Создаем заглушку для HttpClient
        $httpClient = $this->createMock(ClientInterface::class);

        // Создаем экземпляр фабрики
        $factory = new HttpClientFactory(
            $this->createMock(UriFactoryInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            $httpClient
        );

        // Проверяем, что возвращается экземпляр HttpClient
        $this->assertSame($httpClient, $factory->getHttpClient());
    }
}
