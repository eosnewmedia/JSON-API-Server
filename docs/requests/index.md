# Requests

## Table Of Contents
1. [Fetch](#fetch)
1. [Save](#save)
1. [Delete](#delete)

## Fetch

A fetch request is represented by an instance of `Enm\JsonApi\Server\Model\Request\FetchRequestInterface` which extends
`Enm\JsonApi\Model\Request\FetchRequestInterface` and `Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface`.

These methods are provided for optimizing response creation:

| Method                                                 | Return Type           | Description                                                                                                                                                                          |
|--------------------------------------------------------|-----------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| originalHttpRequest()                                  | RequestInterface      | Returns the instance (PSR-7) of the original http request.                                                                                                                           |
| isMainRequest()                                        | bool                  | Indicates if the current request is the main or a sub request.                                                                                                                       |
| relationship()                                         | string                | Returns the name of the requested relationship if the main request is a relationship request, otherwise an empty string.                                                             |
| requestedResourceBody()                                | bool                  | Indicates if the response for this request should contain attributes and relationships                                                                                               |
| requestedField(string $type, string $name)             | bool                  | Indicates if a field (attribute) should be contained in the resource response.                                                                                                       |
| requestedRelationships()                               | bool                  | Indicates if resources fetched by this request should provide their relationships even if their attributes are not requested (for example with sub request for "include" parameter). |
| requestedInclude(string $relationship)                 | bool                  | Indicates if the resources of a relationship should be included in the response document.                                                                                            |
| subRequest(string $relationship, $keepFilters = false) | FetchRequestInterface | Creates a new fetch resource request for the given relationship. A sub request does not contain pagination and sorting.                                                              |

## Save

A save request is represented by an instance of `Enm\JsonApi\Server\Model\Request\SaveRequestInterface` which extends
`Enm\JsonApi\Model\Request\SaveRequestInterface` and `Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface`.

These methods are provided for optimizing response creation:

| Method                | Return Type           | Description                                                |
|-----------------------|-----------------------|------------------------------------------------------------|
| originalHttpRequest() | RequestInterface      | Returns the instance (PSR-7) of the original http request. |
| fetch()               | FetchRequestInterface | Create a new fetch request from current request            |

## Delete

A delete request is represented by an instance of `Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface` which extends
`Enm\JsonApi\Model\Request\JsonApiRequestInterface`.

These methods are provided for optimizing response creation:

| Method                | Return Type      | Description                                                |
|-----------------------|------------------|------------------------------------------------------------|
| originalHttpRequest() | RequestInterface | Returns the instance (PSR-7) of the original http request. |

*****

[prev: Request Handler](../request-handler/index.md) | [back to README](../../README.md) | [next: Exception Handling](../exception-handling/index.md)
