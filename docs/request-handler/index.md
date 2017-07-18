# Request Handler

## Table Of Contents

1. [Concept](#concept)
1. [Interface](#interface)
1. [JSON API Aware](#json-api-aware)
1. [Usage](#usage)
1. [Handler Registry](#handler-registry)
1. [Resource Providers](#resource-providers)
    1. [Concept](resource-providers/index.md#concept)
    1. [Interface](resource-providers/index.md#interface)
    1. [JSON API Aware](resource-providers/index.md#json-api-aware)
    1. [Usage](resource-providers/index.md#usage)
1. [Handler Chain](#handler-chain)

## Concept

Request handlers are responsible for turning a JSON API request into a document, which contains the requested resources.

The document created by a request handler will be normalized and transformed into a JSON API HTTP response.

## Interface

`Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface`:

| Method                                                   | Return Type       | Description                                                                                                                                                 |
|----------------------------------------------------------|-------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|
| fetchResource(FetchRequestInterface $request)            | DocumentInterface | This method must return a single resource document containing the requested resource.                                                                       |
| fetchResources(FetchRequestInterface $request)           | DocumentInterface | This method must return a multi resource document containing the requested resources (by type, filters and pagination).                                     |
| fetchRelationship(FetchRequestInterface $request)        | DocumentInterface | This method must return a multi resource document containing the requested resources, which are the related resources for the given resource (type and id). |
| saveResource(SaveRequestInterface $request)              | DocumentInterface | This method must return a single resource document containing the resource after it was saved (created or modified).                                        |
| deleteResource(AdvancedJsonApiRequestInterface $request) | DocumentInterface | This method must return a single resource document containing no resource, because it should be deleted.                                                    |
| modifyRelationship(SaveRequestInterface $request)        | DocumentInterface | This method must return a document containing all resource identifiers of the relationship after it was modified.                                           |

## JSON API Aware

If your instance of `Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface` implements `Enm\JsonApi\JsonApiAwareInterface`
the JSON API server will be given as dependency for accessing helper methods for creating new objects.

You should use `Enm\JsonApi\JsonApiAwareTrait` in your class to implement the interface.

## Usage

If you want to handle post (create) and patch (update) requests on a different way let your request handler use
`Enm\JsonApi\Server\RequestHandler\SeparatedSaveTrait` and implement the methods `createResource` and `patchResource`. 

If your request handler does not allow write access to your resources you can use `Enm\JsonApi\Server\RequestHandler\FetchOnlyTrait`
in your handler class, which results in a 403 (not allowed) HTTP status if a post, patch or delete is requested.

If handled resources do not have relationships your request handler can use `Enm\JsonApi\Server\RequestHandler\NoRelationshipsTrait`,
which results in a 400 (bad request) status code if any relationship is requested.

If your request handler uses more than one of `Enm\JsonApi\Server\RequestHandler\FetchOnlyTrait`,
 `Enm\JsonApi\Server\RequestHandler\NoRelationshipsTrait` and `Enm\JsonApi\Server\RequestHandler\SeparatedRelationshipSaveTrait`
at the same time you have to decide which implementation of method `saveRelationship` should by used by your handler:

```php
class YourRequestHandler implements ResourceHandlerInterface, JsonApiAwareInterface
{
    use JsonApiAwareTrait, FetchOnlyTrait, NoRelationshipsTrait {
        FetchOnlyTrait::saveRelationship insteadof NoRelationshipsTrait;
    }
    
    // ... your implementation
}
```

## Handler Registry

The request handler registry (`Enm\JsonApi\Server\RequestHandler\RequestHandlerRegistry`) can be used to add multiple request
handlers to the JSON API server which normally supports only one handler.

```php
$registry = new RequestHandlerRegistry();
$registry->addRequestHandler('yourFirstType', new YourFirstRequestHandler();
$registry->addRequestHandler('yourSecondType', new YourSecondRequestHandler();

$api = new JsonApiServer($registry);
```

## Resource Providers

There is also a handler to use resource providers, which allows you to focus on resources if your project does not
require working directly with documents.

See: [Resource Providers](resource-providers/index.md) to understand how resource providers can be used.

The handler is `Enm\JsonApi\Server\RequestHandler\ResourceProviderRequestHandler` and allows you to add your providers
via method "addResourceProvider".

```php
$requestHandler = new ResourceProviderRequestHandler();
$requestHandler->addResourceProvider('yourType', new YourResourceProvider());
```

## Handler Chain

Request handlers can be chained by `Enm\JsonApi\Server\RequestHandler\RequestHandlerChain`.

This allows you to use the request handler registry with your custom handlers and the resource provider handler
with your custom providers at the same time, without the need to configure the registry with the provider handler for each
provided type.

The chain tries to execute the handlers in the provided order until the first respond with a document.
If a handler does not support a requested resource type an exception of type `Enm\JsonApi\Exception\UnsupportedTypeException` 
must be thrown to let the chain try the next handler.


```php
$registry = new RequestHandlerRegistry();
$registry->addRequestHandler('yourFirstType', new YourFirstRequestHandler();
$registry->addRequestHandler('yourSecondType', new YourSecondRequestHandler();

$providerHandler = new ResourceProviderRequestHandler();
$providerHandler->addResourceProvider('yourSecondType', new YourResourceProvider());

$requestHandler = new RequestHandlerChain();
$requestHandler->addRequestHandler($registry); // will be tried first
$requestHandler->addRequestHandler($providerHandler); // will be tried if registry can not handle the requested type

$api = new JsonApiServer($requestHandler);
```

*****

[prev: JSON API Server](../json-api-server/index.md) | [back to README](../../README.md) | [next: Requests](../requests/index.md)
