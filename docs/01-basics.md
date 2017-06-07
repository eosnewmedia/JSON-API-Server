[back to README](../README.md)
# Basic Usage
This section will show you the basics you will need to use this library.

1. [Example of Usage](#example-of-usage)
1. [Endpoints](#endpoints)
1. [Providers](#providers)
    1. [Resource Provider](#resource-provider)
    1. [Multiple Resource Providers](#multiple-resource-providers)
1. [Exception and Error Handling](#exception-and-error-handling)

## Example of Usage: 

    <?php
    /**
     * GET /myResources
     * will return a list of all resources of type "myResources"
     */
    
    require_once('vendor/autoload.php');
    
    $registry = new Enm\JsonApi\Server\Provider\ResourceProviderRegistry();
    
    $registry->addProvider(
        new Enm\JsonApi\Server\Provider\ResourceCollectionProvider(
            new Enm\JsonApi\Model\Resource\JsonResourceCollection(
                [
                    new Enm\JsonApi\Model\Resource\JsonResource('myResources', '1', ['name' => 'test'])
                ]
            )
        ),
        'myResources'
    );
    
    $jsonApi = new JsonApi($registry);
 
    try {
        $response = $jsonApi->fetchResources('myResources', new FetchRequest());
    } catch(\Exception $e){
        $response = $jsonApi->handleError(Enm\JsonApi\Model\Error\Error::createFromException($e));
    }
    
    /** @var Symfony\Component\HttpFoundation $response */
    $response->send();
    
*****
*****

## Endpoints

| HTTP Request                                 | enm/json-api                                                                                                  |
|----------------------------------------------|---------------------------------------------------------------------------------------------------------------|
| GET      /{type}                             | JsonApi::fetchResource(string $type, string $id, FetchInterface $request): Response                           |
| GET      /{type}/{id}                        | JsonApi::fetchResources(string $type, FetchInterface $request): Response                                      |
| POST     /{type}                             | JsonApi::createResource(string $type, CreateResourceInterface $request): Response                             |
| PATCH    /{type}/{id}                        | JsonApi::patchResource(string $type, string $id, PatchResourceInterface $request): Response                   |
| DELETE   /{type}/{id}                        | JsonApi::deleteResource(string $type, string $id, Request $request = null): Response                          |
| GET      /{type}/{id}/children               | JsonApi::fetchRelated(string $type, string $id, FetchInterface $request, string $relationship): Response      |
| GET      /{type}/{id}/relationships/children | JsonApi::fetchRelationship(string $type, string $id, FetchInterface $request, string $relationship): Response |

*****
*****

## Providers
The implementation make use of resource providers, which are responsible to provide resource objects.

*****

### Resource Provider
A resource provider is a php class which implements `Enm\JsonApi\Server\Provider\ResourceProviderInterface`.

Every time json api is requested, internally the given resource provider will be called.

The following methods must be implemented by the resource provider:

| Method                                                                                    | Return Type           | Description                                                           |
|-------------------------------------------------------------------------------------------|-----------------------|-----------------------------------------------------------------------|
| findResource(string $type, string $id, FetchInterface $request)                           | ResourceInterface     | Find and return a resource by type and id.                            |
| findResources(string $type, FetchInterface $request)                                      | array                 | Find resources (all or filtered) by type.                             |
| findRelationship(string $type, string $id, FetchInterface $request, string $relationship) | RelationshipInterface | Find a relationship by name for a resource identified by type and id. |
| createResource(SaveResourceInterface $request)                                            | ResourceInterface     | Create a new resource.                                                |
| patchResource(SaveResourceInterface $request)                                             | ResourceInterface     | Patch a resource.                                                     |
| deleteResource(string $type, string $id)                                                  | int                   | Delete a resource identified by type and id. Return the http status.  |

*****

### Multiple Resource Providers
If you want to use multiple providers you should use the `Enm\JsonApi\Server\Provider\ResourceProviderRegistry`, which acts like a provider but forwards the request to the concrete provider.

    $registry = new Enm\JsonApi\Server\Provider\ResourceProviderRegistry();
    
    $registry->add(new CustomProviderA(), 'typeA');
    $registry->add(new CustomProviderB(), 'typeB');

*****

#### Resource Provider Registry Aware
If your resource providers are implementing `Enm\JsonApi\Server\Provider\ResourceProviderRegistryAwareInterface`, the `ResourceProviderRegistry`
will inject it self into your provider when calling `addProvider()` of the registry.

This allows you to use other resource providers for different types in your custom provider, for example to build relations.

*****
*****

## Exception and Error Handling

If you want json api to return error responses, you have to use objects of type `Enm\JsonApi\Model\Error\ErrorInterface`.

The simplest way is to use the default implementation `Enm\JsonApi\Model\Error\Error`, which offers a static method to create an 
error object from an exception.

    Enm\JsonApi\Model\Error\Error::createFromException(new \Exception());
    
Errors can contain meta informations like resources and relationships.

To create a http response from an error instance simply call:

        $response = $jsonApi->handleError($error);

*****
*****

[back to README](../README.md) | [next: Advanced Usage](../docs/02-advanced.md)
