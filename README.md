# le7-api-request
API client for le7 PHP MVC framework or any PSR project
This is an simple PSR HTTP client implementation

# API Request Utility

This project provides a simple utility for simple making HTTP requests to an API.
It includes a factory class for creating instances of the API request utility.

## What it can?

- Create requests using any API, creating GET, POST, PUT, DELETE requests

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
$apiRequest->setUri('https://example.com/api')->post($data, $headers);

// Get ResponseInterface for GET request
$apiRequest->setUri('https://example.com/api')->get();

// Get ResponseInterface for PUT request
$apiRequest->setUri('https://example.com/api')->put();

// Get ResponseInterface for PUT request
// You can use request() method for any request
$apiRequest->setUri('https://example.com/api')->request('PUT');

// Get array from response
$apiRequest->setUri('https://example.com')->convert('POST', $data, $headers)->toArray();

// Get object from response
$apiRequest->setUri('https://example.com')->convert('POST', $data, $headers)->toObject();

```

## Headers

You can use global headers and headers for each request. Headers for each
request you can inject in request methods when you call it.
Now, you see how setup global headers, that will be added for any requests:

```php
$headers = [
    // Array with headers
]

// Set many headers
$apiRequest->setHeaders($headers);

// Set one header
$apiRequest->setHeader('Content-Language', 'en');
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

You can create request with JSON data or multipart/form-data.
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
$apiRequest->setUri('https://example.com')->convert('POST', $data, $headers)->toArray();
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

Also, you can inject convertor when create ApiRequest instance:

```php
$factory = new HttpClientFactory(
    $psr17Factory, // UriFactoryInterface
    $psr17Factory, // RequestFactoryInterface
    $psr17Factory, // StreamFactoryInterface
    $httpClient, // ClientInterface implementation
);

// $convertor is ResponseConvertor instance
$apiRequest = $factory->getApiRequest(null, $convertor);

```

But you can use another way:

```php
$response = $apiRequest->setUri('https://example.com/api')->request('PUT', $data, $headers);
$convertor = new ResponseJsonArrayConvertor($response);
$result = $convertor->get($response); // We will get array if response correct
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
    $psr17Factory, // StreamFactoryInterface
    $httpClient // ClientInterface implementation
);

// $convertor is ResponseConvertor instance
$apiRequest = $factory->getApiRequest('https://example.com');

// And now you can use it
$result = $apiRequest->setUri('/contacts/get')->get();
```

### When you have already created instance

```php
$apiRequest->setUriPrefix('https://example.com');
```
