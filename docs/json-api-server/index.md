# JSON API Server

`Enm\JsonApi\Server\JsonApiServer`:

| Method                                                            | Return Type       | Description                                    |
|-------------------------------------------------------------------|-------------------|------------------------------------------------|
| handleHttpRequest(RequestInterface $request, bool $debug = false) | ResponseInterface | Translate a HTTP request into a HTTP response  |

## Table Of Contents

1. [Concept](#concept)
1. [Endpoints](#endpoints)
1. [Usage](#usage)
1. [Advanced Configuration](#advanced-configuration)
1. [Logging](#logging)

## Concept
The JSON API server is based on [PSR-7 HTTP message interfaces](http://www.php-fig.org/psr/psr-7/).

It will detect HTTP method and api paths automatically to decide which JSON API action have to be executed.

The JSON API server requires an instance of `Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface`, which is
responsible for building a document object for the given request.

If the API path is not the URL root path the used API prefix is required to be set in the constructor.

It creates a request model for the given HTTP request, gives the model into the request handler and transform the
received document into a HTTP JSON API response.

## Endpoints

| HTTP-Method | URL-Path (without prefix)                    | Server Action                                                                                                    |
|-------------|----------------------------------------------|------------------------------------------------------------------------------------------------------------------|
| GET         | /{type}                                      | The server creates a fetch request and calls method "findResources" of the request handler.                      |
| GET         | /{type}/{id}                                 | The server creates a fetch request and calls method "findResource" of the request handler.                       |
| GET         | /{type}/{id}/relationship/{relationshipName} | The server creates a fetch request and calls method "findRelationship" of the request handler.                   |
| GET         | /{type}/{id}/{relationshipName}              | The server creates a fetch request and calls method "findRelationship" of the request handler.                   |
| POST        | /{type}                                      | The server creates a save request and calls method "saveResource" of the request handler.                        |
| PATCH       | /{type}/{id}                                 | The server creates a save request and calls method "saveResource" of the request handler.                        |
| DELETE      | /{type}/{id}                                 | The server creates a simple JSON API request and calls method "deleteResource" of the request handler.           |
| POST        | /{type}/{id}/relationship/{relationshipName} | The server creates a relationship modification request and calls method "modifyResource" of the request handler. |
| PATCH       | /{type}/{id}/relationship/{relationshipName} | The server creates a relationship modification request and calls method "modifyResource" of the request handler. |
| DELETE      | /{type}/{id}/relationship/{relationshipName} | The server creates a relationship modification request and calls method "modifyResource" of the request handler. |

## Usage

Here is an example how to use the JSON API server:

```php

// use the request handler registry to combine multiple request handlers for diffrent resource types
$requestHandler = new RequestHandlerRegistry();

// add your request handlers to the registry (or, if you have only one, add it directly to JSON API server)
$requestHandler->addRequestHandler('customResources', new YourCustomRequestHandler());

// create the server with request handler and api path prefix
$jsonApi = new JsonApiServer($requestHandler, '/api');

// create a PSR-7 request from HTTP request
$request = new Request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], getallheaders(), file_get_contents('php://input'));

// get a PSR-7 response for your PSR-7 request
$response = $jsonApi->handleHttpRequest($request);

// send the response back to requesting HTTP client...

```

## Advanced Configuration

The JSON API server implements `Enm\JsonApi\JsonApiInterface`, which offers helper methods to create resources, 
relationships and documents.

The creation of those objects is performed by factory classes.

You can overwrite the default factories by calling these methods:

* setResourceFactory(ResourceFactoryInterface $resourceFactory)
* setRelationshipFactory(RelationshipFactoryInterface $relationshipFactory)
* setDocumentFactory(DocumentFactoryInterface $documentFactory)

If factories should be overwrite their setters should always be called before object creation or document serializers
and deserializers are executed!

It's also possible to overwrite the default document serializer and the default document deserializer:

* setDocumentSerializer(DocumentSerializerInterface $documentSerializer)
* setDocumentDeserializer(DocumentDeserializerInterface $documentDeserializer)

### Logging

The JSON API server implements `Psr\Log\LoggerAwareInterface` and can log errors and debug messages (if debug is enabled)
to a provided instance of `Psr\Log\LoggerInterface`.

To inject a logger simply call "setLogger" from JSON API server.

*****

[back to README](../../README.md) | [next: Request Handler](../request-handler/index.md)
