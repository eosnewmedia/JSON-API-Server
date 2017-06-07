[back to README](../README.md)
# Advanced Usage
This section will show you which parts of this library are highly customizable and which methods are provided for a good developer experience and to optimize the logic of your api implementation.

1. [Request Options](#request-options)
    1. [Fetch](#fetch)
        1. [Fetch Example](#fetch-example)
    1. [Create and Patch](#create-and-patch)
        1. [Create Example](#create-example)
        1. [Patch Example](#patch-example)
1. [Events](#events)
1. [Document Serializer](#document-serializer)

*****
*****

## Request Options
The handling of json api requests is based on the symfony http foundation request class.
Each request class (and their interfaces) are giving you access to the symfony request to customize request handling.

*****

### Fetch
The interface for fetch requests (`Enm\JsonApi\Server\Model\Request\FetchInterface` ) is used for these cases:

* findResource
* findResources
* findRelationship
* find Related

 and abstracts all json api relevant options.
 
 The default implementation which should be used in most cases is `Enm\JsonApi\Server\Model\Request\FetchRequest`.
 While parsing the request through the default implementation also the content type is validated to be `application/vnd.api+json`.
 
 | Method                                        | Return Type                              | Description
 |-----------------------------------------------|------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
 | shouldReturnFullResource()                    | bool                                     | Indicates if a resource should contain attributes. By default the resources fetched by the main request should be full resources while related resources depends on "include" parameter or the requested relationship ("findRelated"). See http://jsonapi.org/format/#fetching-includes and http://jsonapi.org/format/#fetching-relationships |
 | shouldContainRelationships()                  | bool                                     | Indicates if relationships should be provided for a resource. By default the resources fetched by the main request should contain relationships. If a related resource should contain relationship depends on the "include" parameter. See http://jsonapi.org/format/#fetching-includes.                                                      |
 | shouldContainAttribute(type, name)            | bool                                     | Indicates if a resource (by type) should contain a special attribute (by name). See http://jsonapi.org/format/#fetching-sparse-fieldsets.                                                                                                                                                                                                     |
 | filters()                                     | KeyValueCollectionInterface              | The filter collection contains requested filters. See http://jsonapi.org/format/#fetching-filtering                                                                                                                                                                                                                                           |
 | pagination()                                  | KeyValueCollectionInterface              | The pagination collections contains requested pagination parameters. See http://jsonapi.org/format/#fetching-pagination                                                                                                                                                                                                                       |
 | sorting()                                     | SortInstruction[]                        | The sorting method returns an array of sort instructions in the requested order.                                                                                                                                                                                                                                                              |
 | subRequest(relationship, keepFilters = false) | FetchInterface                           | The subRequest method creates a sub request for a named relationship which contains all necessary parameters to include resources of a relationship if requested.                                                                                                                                                                             |
 | addInclude(include)                           | $this                                    | Manipulates the request to include a relation even if not requested via client request.                                                                                                                                                                                                                                                       |
 | getHttpRequest()                              | Symfony\Component\HttpFoundation\Request | Provides the original request to allow access for example to http headers.                                                                                                                                                                                                                                                                    |

You are not required to check is attributes should be contained because json api will remove all unrequested attributes on response. 
The "shouldContain"-Methods allows you to exclude partials of your logic from execution if not needed and, in case of "shouldContainRelationships", prevent your application from building endless loops.

#### Fetch Example

    // in your resource provider
    public function findResources(string $type, FetchInterface $fetch): array {
        $entities = $this->getYourRepository()->getEntities($type, $fetch->filters()->all());
        
        $resources = [];
        foreach($entities as $entity){
            $resource = new JsonResource($entity->getType(), $entity->getId());
            
            if($fetch->shouldContainFullResource()){
                if($fetch->shouldContainAttribute($entity->getType(), 'name')){
                    $resource->attributes()->set('name', $entity->getName());
                }
            }
            
            if($fetch->shouldContainRelationships()){
                $related = new JsonResource($entity->getSubEntity()->getType(), $entity->getSubEntity()->getId());
                
                $subRequest = $fetch->subRequest('subResource');
                
                if($subRequest->shouldContainFullResource()){
                    if($subRequest->shouldContainAttribute($entity->getSubEntity()->getType(), 'name')){
                        $related->attributes()->set('name', $entity->getSubEntity()->getName());
                    }
                }
                
                if($subRequest->shouldContainRelationships()){
                    // possible sub resources of the current sub resource...
                }
                
                $resource->relationships()->setToOne('subResource', $related);
            }
            
            $resources[] = $resource;
        }
        
        return $resource;
    }

 *****
 
### Create and Patch
The Save-Request () offers access the POST- or PATCH-Request and creates a resource object which have to be handled by your application.
On post requests a new resource id (uuid) is generated by default but you are not required to use it in your application logic. (Simply call $saveRequest->resource()->duplicate('yourId') to create a new resource with your custom id)
The patch request requires the client to send an id for the resource.
 
 | Method                                            | Return Type                              | Description                                                                                                         |
 |---------------------------------------------------|------------------------------------------|---------------------------------------------------------------------------------------------------------------------|
 | containsId()                                      | bool                                     | Indicates if the client sends an id or the resource id was generated.                                               |
 | resource()                                        | ResourceInterface                        | The resource which should be created or updated.                                                                    |
 | createFetch(bool shouldReturnFullResource = true) | FetchInterface                           | Creates a fetch request from http request. Can be used to create a resource response like while fetching resources. |
 | getHttpRequest()                                  | Symfony\Component\HttpFoundation\Request | The original http request                                                                                           |

The default Implementations of `Enm\JsonApi\Server\Model\Request\SaveResourceInterface` are `Enm\JsonApi\Server\Model\Request\CreateRequest`
and `Enm\JsonApi\Server\Model\Request\PatchRequest` and should always be used.

#### Create Example

    // in your resource provider
    public function createResource(SaveResourceInterface $request): ResourceInterface {
        $resource = $request->resource();
        
        $entity = $this->getYourEntityFactory()->create();
        $entity->setName($resource->attributes()->getRequired('name');
        
        $subEntity = $this->getYourRepository()->findOne(
            $resource->relationships()->get('subResource')->realted()->first()->getType(),
            $resource->relationships()->get('subResource')->realted()->first()->getId()
        );
        
        $entity->setSubEntity($subEntity);
        
        $this->getYourRepository()->save($entity);
        
        return $resource->duplicate((string)$entity->getId); // return resource with custom generated (e.g. db auto increment) id
    }

#### Patch Example

    // in your resource provider
    public function patchResource(SaveResourceInterface $request): ResourceInterface {
        $resource = $request->resource();
        
        $entity =$this->getYourRepository()->findOne($resource->getType(), $resource->getId());
        $entity->setName($resource->attributes()->getOptional('name', $entity->getName()); // overwrite the name only if it's sended by the client
        
        // only patch relationship if client send relationship reference
        if($resource->relationships()->has('subResource')){
            $subEntity = $this->getYourRepository()->findOne(
                $resource->relationships()->get('subResource')->realted()->first()->getType(),
                $resource->relationships()->get('subResource')->realted()->first()->getId()
            );
        
            $entity->setSubEntity($subEntity);
        }
        
        $this->getYourRepository()->save($entity);
        
        return $this->findResource($resource->getType(), $resource->getId(), $request->createFetch()); // return a full resource response after patching some values
    }

*****
*****

## Events
To use the event system the library requires you to install the symfony event dispatcher component.

    composer require symfony/event-dispatcher

After installing it via composer, you have configure your json api instance:

    $eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher()
    $jsonApi->setEventDispatcher($eventDispatcher);


| Event                                  | Constant                           | Object Type                                    | Description                                                                   |
|----------------------------------------|------------------------------------|------------------------------------------------|-------------------------------------------------------------------------------|
| enm.json_api.on_fetch                  | JsonApi::ON_FETCH                  | Enm\JsonApi\Server\Event\FetchEvent            | Allows you to modify a fetch request before it will be executed               |
| enm.json_api.before_normalize_resource | JsonApi::BEFORE_NORMALIZE_RESOURCE | Enm\JsonApi\Server\Event\ResourceEvent         | Allows you to modify a resource before it will be normalized for the response |
| enm.json_api.on_include_resource       | JsonApi::ON_INCLUDE_RESOURCE       | Enm\JsonApi\Server\Event\ResourceEvent         | Allows you to modify a resource before it will be included as sub resource    |
| enm.json_api.before_serialize_document | JsonApi::BEFORE_SERIALIZE_DOCUMENT | Enm\JsonApi\Server\Event\DocumentEvent         | Allows you to modify a document before it is serialized to json api format    |
| enm.json_api.before_document_response  | JsonApi::BEFORE_DOCUMENT_RESPONSE  | Enm\JsonApi\Server\Event\DocumentResponseEvent | Allows you to modify the created http response before json api will return it |

*****
*****

[prev: Basic Usage](../docs/01-basics.md) | [back to README](../README.md)
