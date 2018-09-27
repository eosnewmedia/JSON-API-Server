# Request Handler

## Table Of Contents

1. [Concept](#concept)
1. [Interface](#interface)
1. [Usage](#usage)

## Concept

Request handlers are responsible for turning a JSON API request into a JSON API response, which contains the requested resources (in a document) and all headers.

The document in the response will be normalized by the json api server and could be serialized by `JsonApiServer::createResponsebBody` to return it as http response.

## Interface

| Method                                             | Return Type       | Description                                              |
|----------------------------------------------------|-------------------|----------------------------------------------------------|
| fetchResource(RequestInterface $request)           | ResponseInterface | Fetch a single resource                                  |
| fetchResources(RequestInterface $request)          | ResponseInterface | Fetch a resource collection                              |
| fetchRelationship(RequestInterface $request)       | ResponseInterface | Fetch a relationship                                     |
| createResource(RequestInterface $request)          | ResponseInterface | Create a new resource                                    |
| patchResource(RequestInterface $request)           | ResponseInterface | Modify an existing resource                              |
| deleteResource(RequestInterface $request)          | ResponseInterface | Delete a resource                                        |
| addRelatedResources(RequestInterface $request)     | ResponseInterface | Add resources to a relationship                          |
| replaceRelatedResources(RequestInterface $request) | ResponseInterface | Replace resources of a relationship with other resources |
| removeRelatedResources(RequestInterface $request)  | ResponseInterface | Remove resources from a relationship                     |

## Usage

You can use one of the following traits if you does not need all features:

* NoResourceFetchTrait
* NoRelationshipFetchTrait
* NoResourceModificationTrait
* NoResourceDeletionTrait
* NoRelationshipModificationTrait

The traits implement the methods of `RequestHandlerInterface` with throwing a NotAllowedException for the respective action.

*****

[prev: JSON API Server](../json-api-server/index.md) | [back to README](../../README.md)
