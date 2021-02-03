Flowly Content API Client
=========================

# Installation

```
composer require simmcomm/content-api-client
```

# Usage

```php

require __DIR__ . '/vendor/autoload.php';

use Flowly\Content\ContentApiClient;

// new instance with factory method
// $access and $secret are from Flowly app API keys module
$client = ContentApiClient::create($access, $secret);

// custom initialization
// $http - instance of Symfony\Contracts\HttpClient\HttpClientInterface
// $serializer - instance of Symfony\Component\Serializer\SerializerInterface
// $validator - instance of Symfony\Component\Validator\Validator\ValidatorInterface
$client = new ContentApiClient($http, $serializer, $validator, $access, $secret);

// all request arguments and responses are mapped to their class
// mapping classes are located in namespaces Flowly\Content\Request and Flowly\Content\Response
// all methods in api client are accordingly typed (arguments and return value)

// public client api

// used on scene list view
$client->getScenes();

// single scene object 
$client->getScene();

// similar videos, based on scene id passed to this method
$client->getScenesSuggest();

// actors and categories lists
$client->getCategories();
$client->getActors();

// scene or actor can be star rated (1-5 scores)
$client->submitRating();

// used for portal landing page
$client->getScenesLanding();

```
