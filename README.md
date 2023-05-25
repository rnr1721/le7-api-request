# le7-api-request
API client for le7 PHP MVC framework or any PSR project
This is an simple PSR API client implementation

# API Request Utility

This project provides a simple utility for simple making HTTP requests to an API.
It includes a factory class for creating instances of the API request utility.

It not have own PSR ClientInterface implementation, and you can use any own.
For example: https://github.com/rnr1721/le7-http-client

## What it can?

- Create requests using any API, creating GET, POST, PUT, DELETE requests
- Allow to get ResponseInterface, array or object from response
- Built-in converters of JSON, XML and CSV responses to object or array
- Allow to create own convertors from ResponseInterface to your data
- Allow pre-define settings for run using DI containers
- Using different httpClients (ClientInterface), swithcing between them
- Get last request data
- Using events, for example for logging requests
- Set global headers and headers per-request
- Full PSR compatible

## Requirements

- PHP 8.0 or higher
- Composer (for installing dependencies)

## Installation

1. Install via composer:

```shell
composer require rnr1721/le7-api-request
```

## Testing

```shell
composer test
```

## Usage

In this example, I use Nyholm PSR library, but you can use any, Guzzle for
example. Also, you will need the ClientInterface implementation.
I use this implementation: https://github.com/rnr1721/le7-http-client

```php
use Core\Factories\HttpClientFactory;
use Nyholm\Psr7\Factory\Psr17Factory;

// Create PSR factories. Nyholm realisation is a single factory to all
$psr17Factory = new Psr17Factory();

// Create httpClient (PSR ClientInterface implementation)
$httpClient = new HttpClientFactory($psr17Factory);

$factory = new HttpClientFactory(
    $psr17Factory, // UriFactoryInterface
    $psr17Factory, // RequestFactoryInterface
    $psr17Factory, // ResponseFactoryInterface
    $psr17Factory, // StreamFactoryInterface
    $httpClient // ClientInterface implementation
);

$apiRequest = $factory->getApiRequest()

$data = [
    // Request data here
];

$headers = [
    'content-language' => 'ru'
];

// Get ResponseInterface for POST request
$apiRequest->post('https://example.com/api', $data, $headers)->getResponse();

// Get array for GET request
$apiRequest->get('https://example.com/api')->toArray();

// Get object for PUT request
$apiRequest->put('https://example.com/api')->toObject();

// Get ResponseInterface for PUT request
// You can use request() method for any request
$apiRequest->request('PUT','https://example.com/api')->getResponse();

// Get array from response
$apiRequest->request('POST','https://example.com', $data, $headers)->toArray();

// Get object from response
$apiRequest->request('POST', 'https://example.com', $data, $headers)->toObject();

// This will return ResponseInterface of request
$apiRequest->getResponse('POST', 'https://example.com', $data, $headers);

// You can set Uri separately if you need it
$apiRequest->setUri('https://example.com');
$apiRequest->getResponse('POST', null, $data, $headers);
$apiRequest->get();

// Make something

// Get last created request
$apiRequest->getLast()->toArray();

```

## Headers

You can use global headers, and headers for each request. Headers for each
request you can inject in request methods when you call it or by setHeader() &
setHeaders() methods.
Now, you will see how setup global headers, that will be added for all requests:

```php
$headers = [
    'My-Great-Header' => 'header_value'
    // Array with headers
]

// Set many headers for all requests in future
$apiRequest->setGlobalHeaders($headers);

// Set one global permanent header
$apiRequest->setGlobalHeader('Content-Language', 'en');
```

Also for one-time request headers:

```php
$headers = [
    'My-Great-Header' => 'header_value'
    // Array with headers
]

// Set many headers for next request only
$apiRequest->setHeaders($headers);

// Set one header for next request only
$apiRequest->setHeader('Content-Language', 'en');

// Also you can set one-time header in method:
$apiRequest->get('https://example.com', null, $headers);
```

## Sending files with multipart/form-data

```php
$data = [
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'file' => new SplFileInfo('/path/to/file.jpg')
];

$apiRequest->setContentType('multipart/form-data');

$response = $apiRequest->post('/upload', $data);
```

## Client setup

If you are using my implementation of ClientInterface,
https://github.com/rnr1721/le7-http-client you can setup some options
of client:

```php
$apiRequest->setTimeout(5); //default 10
$apiRequest->setMaxRedirects(5); // Default is 3
$apiRequest->setFollowLocation(false); // Default is true 
```

## JSON and form-data

You can create these content-types of requests:

- ***application/json***
- ***application/x-www-form-urlencoded***
- ***multipart/form-data***

By default is json, but you can switch to form-data:

```php
// This is content type for requests,
// Default is application/json
$apiRequest->setContentType('multipart/form-data')
```
Another way is set header. It turn needle mode automatically

## Convertors

By default, you can convert recieved ResponseInterface to array or object
like this:

```php
// Get array from response
$apiRequest->request('POST', 'https://example.com', $data, $headers)->toArray();
```

```php
// Get array from response
$apiRequest->request('POST', 'https://example.com', $data, $headers)->toObject();
```

Also, you can write own convertor. It must implement ResponseConvertorInterface:

```php
<?php

namespace Core\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface ResponseConvertorInterface
{

    public function get(?ResponseInterface $response = null): mixed;
}
```

As examples you can see ResponseArrayConverter and ResponseObjectConverter

Also, you can inject convertor when create ApiRequest instance:

```php
$factory = new HttpClientFactory(
    $psr17Factory, // UriFactoryInterface
    $psr17Factory, // RequestFactoryInterface
    $psr17Factory, // ResponseFactoryInterface
    $psr17Factory, // StreamFactoryInterface
    $httpClient, // ClientInterface implementation
);

// $convertor is ResponseConvertor instance
$apiRequest = $factory->getApiRequest(null, $convertor);

```

## Multiple HTTP clients

You can use different ClientInterface in different situations.
Any client have own key. When you create instance of ApiRequest,
ClientInterface that you inject have key 'default'. For some reasons
you may need add ClientInterface instance.
So, you can make this:

```php

// We have created instance
$newHttpClient; // ClientInterface

// Add new ClientInterface and make it active
$apiRequest->addHttpClient('new key', $newHttpClient, true);

// Make our requests

// Switch to default ClientInterface
$apiRequest->setActiveHttpClient('default');

```

## Uri prefixes

You can set the Uri prefix to add before all url in two way

### When creating ApiRequest instance:

```php
$factory = new HttpClientFactory(
    $psr17Factory, // UriFactoryInterface
    $psr17Factory, // RequestFactoryInterface
    $psr17Factory, // ResponseFactoryInterface
    $psr17Factory, // StreamFactoryInterface
    $httpClient // ClientInterface implementation
);

// $convertor is ResponseConvertor instance
$apiRequest = $factory->getApiRequest('https://example.com');

// And now you can use it -it will be https://example.com/contacts/get
$result = $apiRequest->setUri('/contacts/get')->get();
```

### When you have already created instance

```php
$apiRequest->setUriPrefix('https://example.com');
```

## Container configuration

In this example I using these components, but you can use any others:

- Tobias Nyholm PSR-message realisation https://github.com/Nyholm/psr7
- PHP-Di dependency injection container https://php-di.org/
- My realisation of ClientInterface https://github.com/rnr1721/le7-http-client
- Optional: some PSR Event Dispatcher if need logging or something else

```php
<?php

use Core\Interfaces\ApiRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Core\Factories\HttpClientFactory;
use Core\Interfaces\HttpClientFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Container\ContainerInterface;
use function DI\factory;

return [
    ApiRequestInterface::class => factory(function (ContainerInterface $c) {
        /** @var HttpClientFactoryInterface $factory */
        $factory = $c->get(HttpClientFactoryInterface::class);
        return $factory->getApiRequest('https://example.com/api');
    }),
    HttpClientFactoryInterface::class => factory(function (ContainerInterface $c) {
        /** @var Psr17Factory $psr17factory */
        $psr17factory = $c->get(Psr17Factory::class);
        return new HttpClientFactory(
        $psr17factory, // UriFactoryInterface
        $psr17factory, // RequestFactoryInterface
        $psr17factory, // ResponseFactoryInterface
        $psr17factory  // StreamFactoryInterface
        $c->get(ClientInterface::class) // ClientInterface
        );
    }),
    ClientInterface::class => factory(function (ContainerInterface $c) {
        /** @var Psr17Factory $psr17factory */
        $psr17factory = $c->get(Psr17Factory::class);
        return new \Core\HttpClient\HttpClientCurl($psr17factory);
    })
];
```

## PSR Events

You can log requests or somehow else use event AfterApiRequestEvent:

```php
use Core\Factories\HttpClientFactory;
use Core\Events\AfterApiRequestEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

// Create PSR factories. Nyholm realisation is a single factory to all
$psr17Factory = new Psr17Factory();

$factory = new HttpClientFactory(
    $psr17Factory, // UriFactoryInterface
    $psr17Factory, // RequestFactoryInterface
    $psr17Factory, // StreamFactoryInterface
    $httpClient, // ClientInterface implementation if not default
);

$apiRequest = $factory->getApiRequest();

// Register the AfterApiRequestEvent
$eventDispatcher->addListener(AfterApiRequestEvent::class, function (AfterApiRequestEvent $event) {
    // Processing event, for example logging
    $response = $event->getResponse();
    $method = $event->getMethod();
    $uri = $event->getUri();
    $data = $event->getData();
    $headers = $event->getHeaders();

    // log response
    // ...
});

// Send the request and get response
$response = $apiRequest->request('GET', 'https://example.com');
```
