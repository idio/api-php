# Idio API PHP Library

A light, object orientated wrapper for the Idio API

* Written in PHP 5
* Follows PSR-0 conventions – auto-load friendly
* Follows the PSR-2 coding standard.
* Documented and tested

## Introduction

This library is not concerned with the individual API endpoints. Instead it acts as a simple wrapper for making requests against the API, whilst handling authentication and providing some convenient ways of, for example, making concurrent requests.

## Requirements

* PHP >= 5.3.2 
 * [cURL extension](http://php.net/manual/en/book.curl.php)
 * [HTTP extension](http://www.php.net/manual/en/book.http.php) – for link manipulation through `Idio\Api\Link`
* [PHPUnit](https://github.com/sebastianbergmann/phpunit/) – to run tests. (optional, installable via composer)

## Installation

The easiest way of using the library is through [composer](http://getcomposer.org/). An example project's `composer.json` might look like:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/idio/api-php"
        }
    ],
    "require": {
        "idio/api-php" : "dev-development"
    }
}
```

## Example Usage

```php
<?php

// This file is generated by Composer
require_once 'vendor/autoload.php';

$api = new Idio\Api\Client();

$api->setUrl(
    'https://api.idio.co',
    '1.0'
);

$api->setAppCredentials(
    'my_delivery_key',
    'my_delivery_secret'
);

// Returns an Idio\Api\Response object
$response = $api->request('GET', '/content')->send();

if ($response->getStatus() == 200) {
    $content = $response->getBody();
}
```

## Advanced Features


### Concurrent (Batch) Requests
Send multiple API requests concurrently using `curl_exec_multi`.

```php
$responses = $api->batch(array(
    $api->request('GET', '/users/1'),
    $api->request('GET', '/users/2'),
    $api->request('GET', '/users/3')
))->send();
```

## Click Tracking Link Manipulation

Manipulate click tracking links to add, change or unset parameters. Useful for non-personalised content which can be cached, and then the links personalised by the client application.
```php
$link = 'http://a.idio.co/r?c=idio&u=http%3A%2F%2Fwww.idioplatform.com';
$link = $api->link($link)->setParameters(array('x'=>array('idio'=>1)))->get();
```
