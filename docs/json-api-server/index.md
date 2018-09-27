# JSON API Server

`Enm\JsonApi\Server\JsonApiServer`:

| Method                                                      | Return Type            | Description                                                    |
|-------------------------------------------------------------|------------------------|----------------------------------------------------------------|
| addHandler(string $type, RequestHandlerInterface $handler)  | void                   | Adds a request handler                                         |
| createRequestBody(?string $requestBody)                     | DocumentInterface|null | Creates a document from the given string                       |
| handleRequest(RequestInterface $request)                    | ResponseInterface      | Handles a request to generate a response                       |
| createResponseBody(ResponseInterface $response)             | string                 | Creates the (http) response body for a given json api response |
| handleException(\Throwable $throwable, bool $debug = false) | ResponseInterface      | Creates a response for an exception                            |

## Table Of Contents

1. [Endpoints](#endpoints)
1. [Usage](#usage)

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
// create the server
$jsonApi = new JsonApiServer(new Deserializer(), new Serializer());

// add your request handlers to the registry of the json api server
$jsonApi->addHandler('customResources', new YourCustomRequestHandler());

// create the json api request
$request = new Request(
        'GET', 
         new \GuzzleHttp\Psr7\Uri('/api/customResources'),
         $jsonApi->createRequestBody(file_get_contents('php://input')),
         '/api'
);

// get a json api response json api request
try{
    $response = $jsonApi->handleHttpRequest($request);
} catch(\Exception $e){
    $response = $jsonApi->handleException($e);
}

// send the response back to requesting HTTP client...
header('HTTP/1.1 '.$response->status()); 

foreach ($response->headers()->all() as $header => $value){
    header($header.': '.$value);
}

echo $jsonApi->createResponseBody($response);

```

*****

[back to README](../../README.md) | [next: Request Handler](../request-handler/index.md)
