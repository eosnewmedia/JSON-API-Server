# Resource Providers

Resource providers are not required by JSON API server but simplify its usage in some cases.

You have to decide by yourself if resource providers meet your project requirements or if you need a full [request handler](../index.md).

## Table Of Contents
1. [Concept](#concept)
1. [Interface](d#interface)
1. [JSON API Aware](#json-api-aware)
1. [Usage](#usage)

## Concept

A resource provider is a class which only returns resource objects instead of document objects.

Their is a special request handler (`Enm\JsonApi\Server\RequestHandler\ResourceProviderRequestHandler`) which builds documents
from resources returned by a resource provider.

A resource provider for example can be used if no pagination and document meta information are needed and relationship fetching
does not require special performance improvements.

A resource provider also separates create and patch methods (instead of request handler where this is "save", because logic is mostly the same)
for simpler usage without the need of decisions based on the request.

## Interface

`Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface`:

| Method                                                   | Return Type           | Description                                        |
|----------------------------------------------------------|-----------------------|----------------------------------------------------|
| findResource(FetchRequestInterface $request)             | ResourceInterface     | Find and return the requested resource.            |
| findResources(FetchRequestInterface $request)            | ResourceInterface[]   | Find and return the requested resources.           |
| createResource(SaveRequestInterface $request)            | ResourceInterface     | Create and return the given resource.              |
| patchResource(SaveRequestInterface $request)             | ResourceInterface     | Patch and return the given resource.               |
| deleteResource(AdvancedJsonApiRequestInterface $request) | void                  | Delete the given resource.                         |
| modifyRelationship(SaveRequestInterface $request)        | RelationshipInterface | This method must return the modified relationship. |

## JSON API Aware

If your instance of `Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface` implements `Enm\JsonApi\JsonApiAwareInterface`
the JSON API server will be given as dependency for accessing helper methods for creating new objects.

You should use `Enm\JsonApi\JsonApiAwareTrait` in your class to implement the interface.

## Usage

If your resource provider does not allow write access to your resources you can use `Enm\JsonApi\Server\ResourceProvider\FetchOnlyTrait`
in your provider class, which results in a 403 (not allowed) HTTP status if a post, patch or delete is requested.

Relationship requests are handled automatically through a fetch resource request with requested include for the relationship.

If you do not want to check for the used HTTP method on relationship modification request you can use 
`Enm\JsonApi\Server\ResourceProvider\SeparatedRelationshipSaveTrait`.

*****

[back to Request Handler](../index.md)
