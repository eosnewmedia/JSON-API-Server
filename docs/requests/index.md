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
| originalHttpRequest()                                  | RequestInterface      | Returns the instance (PSR-7) of the original HTTP request.                                                                                                                           |
| isMainRequest()                                        | bool                  | Indicates if the current request is the main or a sub request.                                                                                                                       |
| relationship()                                         | string                | Returns the name of the requested relationship if the main request is a relationship request, otherwise an empty string.                                                             |
| requestedResourceBody()                                | bool                  | Indicates if the response for this request should contain attributes and relationships                                                                                               |
| requestedField(string $type, string $name)             | bool                  | Indicates if a field (attribute) should be contained in the resource response.                                                                                                       |
| requestedRelationships()                               | bool                  | Indicates if resources fetched by this request should provide their relationships even if their attributes are not requested (for example with sub request for "include" parameter). |
| requestedInclude(string $relationship)                 | bool                  | Indicates if the resources of a relationship should be included in the response document.                                                                                            |
| subRequest(string $relationship, $keepFilters = false) | FetchRequestInterface | Creates a new fetch resource request for the given relationship. A sub request does not contain pagination and sorting.                                                              |


Example of usage ("fetchResources" in a request handler, which implements `JsonApiAwareInterface`):

```php
public function fetchResources(FetchRequestInterface $request): DocumentInterface
{
    $entities = $this->getExampleRepository()->findBy($fetch->filters()->all());
    
    $resources = [];
    foreach($entities as $entity){
        // first create the resource for an entity...
        $resource = $this->jsonApi()->resource('examples', $entity->getId());
        
        // add attributes if requested
        if($request->requestedResourceBody()){
            if($request->requestedField('examples', 'name')){
                $resource->attributes()->set('name', $entity->getName());
            }
            if($request->requestedField('examples', 'description')){
                $resource->attributes()->set('description', $entity->getDescription());
            }
        }
        
        // add relationships if requested
        if($request->requestedRelationships()){
            $test = $entity->getTest(); // get related entity...
            
            $relatedResource = $this->jsonApi()->resource('tests', $test->getId()); // ...and build a resource for it
            
            if($request->requestedInclude('test')){ // same as $request->subRequest('test')->requestedResourceBody()
                if($request->requestedField('tests', 'title')){
                    $relatedResource->attributes()->set('title', $test->getTitle());    
                }
            }
            
            // add related resource as relationship
            $resource->relationships()->set(
                $this->jsonApi()->toOneRelationship('test', $relatedResource)
            )
        }    
    }
    
    return $this->jsonApi()->multiResourceDocument($resources);
}
```

## Save

A save request is represented by an instance of `Enm\JsonApi\Server\Model\Request\SaveRequestInterface` which extends
`Enm\JsonApi\Model\Request\SaveRequestInterface` and `Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface`.

These methods are provided for optimizing response creation:

| Method                | Return Type           | Description                                                |
|-----------------------|-----------------------|------------------------------------------------------------|
| originalHttpRequest() | RequestInterface      | Returns the instance (PSR-7) of the original HTTP request. |
| fetch()               | FetchRequestInterface | Create a new fetch request from current request            |

Example of usage ("saveResource" in a request handler, which implements `JsonApiAwareInterface`):

```php
public function saveResource(SaveRequestInterface $request): DocumentInterface
{
    $resource = $request->document()->data()->first();
    
    if($request->containsId()){
        $entity = $this->getExampleRepository()->findOneById($request->id());
    } else {
        $id = $resource->id(); // use the client generated id..
        if($id === ''){
            $id = $this->jsonApi()->generateUuid(); // ...or create a new uuid if there is no id generated by client
        }
        
        $entity = new Entity($id);
    }
    
    if($resource->attributes()->has('name')){
        $entity->setName($resource->attributes()->getRequired('name'));
    }
    if($resource->attributes()->has('description')){
        $entity->setDescription($resource->attributes()->getRequired('description'));
    }
    
    // set relationship
    if($resource->relationships()->has('test')){
        $test = $this->getTestRepository()->findOneById(
            $resource->relationships()->get('test')->realted()->first()->id()
        );
    
        $entity->setTest($test);
    }
    
    $this->getExampleRepository()->saveEntity($entity);
    
    return $this->fetchResource($request->fetch($entity->getId()));
}
```

## Delete

A delete request is represented by an instance of `Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface` which extends
`Enm\JsonApi\Model\Request\JsonApiRequestInterface`.

These methods are provided for optimizing response creation:

| Method                | Return Type      | Description                                                |
|-----------------------|------------------|------------------------------------------------------------|
| originalHttpRequest() | RequestInterface | Returns the instance (PSR-7) of the original HTTP request. |

Example of usage ("deleteResource" in a request handler, which implements `JsonApiAwareInterface`):

```php
public function deleteResource(AdvancedJsonApiRequestInterface $request): DocumentInterface
{
    $this->getExampleRepository()->deleteOneById($request->id());
    
    // return a document without content
    return $this->jsonApi()->singleResourceDocument()->withHttpStatus(204);
}
```

*****

[prev: Request Handler](../request-handler/index.md) | [back to README](../../README.md) | [next: Exception Handling](../exception-handling/index.md)
