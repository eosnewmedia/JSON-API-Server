## Change Log

### 3.0.0
* removed pagination and moved it to json api common
* removed special requests and moved it to json api common
* removed special requests and moved it to json api common
* removed (mostly) unused concept of request providers
* removed request handler chain
* removed "seperated save traits" and moved "separted save methods" to request handler interface
* simplified json api server class
* removed guzzle prs dependency
* removed psr 7 request/response dependency for json api server request and response (of course still possible)
* removed request and (http)response creation from json api server for more flexibility
* require request handlers to create full responses (more flexibility)

### 2.3.0
* added `Enm\JsonApi\Server\RequestHandler\FetchRelationshipTrait`

### 2.2.0
* added `Enm\JsonApi\Server\Pagination\PaginationLinkGeneratorInterface`
* added `Enm\JsonApi\Server\Pagination\OffsetPaginationLinkGenerator`
* added `Enm\JsonApi\Server\Pagination\PaginationTrait`

### 2.1.0
* changed method signature of `Enm\JsonApi\Server\JsonApiServer::handleException` to public

### 2.0.0
* renamed namespace `Enm\JsonApi\Server\Provider` to `Enm\JsonApi\Server\ResourceProvider`
* removed method `findRelationship` from `Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface`
* changed signature of method `findResource` from `Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface`
* changed signature of  method `findResources` from `Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface`
* changed signature of  method `createResource` from `Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface`
* changed signature of  method `patchResource` from `Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface`
* changed signature of  method `deleteResource` from `Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface`
* removed class `Enm\JsonApi\Server\Provider\AbstractImmutableResourceProvider`
* removed class `Enm\JsonApi\Server\Provider\AbstractResourceProvider`
* removed class `Enm\JsonApi\Server\Provider\ResourceProviderCollection`
* removed interface `Enm\JsonApi\Server\Provider\ResourceProviderRegistryInterface`
* removed class `Enm\JsonApi\Server\Provider\ResourceProviderRegistry`
* removed interface `Enm\JsonApi\Server\Provider\ResourceProviderRegistryAwareInterface`
* removed class `Enm\JsonApi\Server\Provider\ResourceProviderRegistryAwareTrait`
* added trait `Enm\JsonApi\Server\ResourceProvider\FetchOnlyTrait`
* added interface `Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface`
* added class `Enm\JsonApi\Server\RequestHandler\RequestHandlerRegistry`
* added class `Enm\JsonApi\Server\RequestHandler\RequestHandlerChain`
* added class `Enm\JsonApi\Server\RequestHandler\ResourceProviderRequestHandler`
* added trait `Enm\JsonApi\Server\RequestHandler\FetchOnlyTrait`
* added trait `Enm\JsonApi\Server\RequestHandler\NoRelationshipsTrait`
* added interface `Enm\JsonApi\Server\JsonApiAwareInterface`
* added trait `Enm\JsonApi\Server\JsonApiAwareTrait`
* removed class  `Enm\JsonApi\Server\JsonApi`
* removed class  `Enm\JsonApi\Server\Event\DocumentEvent`
* removed class  `Enm\JsonApi\Server\Event\DocumentResponseEvent`
* removed class  `Enm\JsonApi\Server\Event\FetchEvent`
* removed class  `Enm\JsonApi\Server\Event\ResourceEvent`
* removed interface  `Enm\JsonApi\Server\Model\Request\FetchInterface`
* removed interface  `Enm\JsonApi\Server\Model\Request\HttpRequestInterface`
* removed interface  `Enm\JsonApi\Server\Model\Request\SaveResourceInterface`
* removed class  `Enm\JsonApi\Server\Model\Request\AbstractHttpRequest`
* removed class  `Enm\JsonApi\Server\Model\Request\SaveResoureRequest`
* removed class  `Enm\JsonApi\Server\Model\Request\FetchRequest`
* removed class  `Enm\JsonApi\Server\Model\Request\SortInstruction`
* added interface  `Enm\JsonApi\Server\Model\FetchRequestInterface`
* added class  `Enm\JsonApi\Server\Model\Request\FetchRequest`
* added interface  `Enm\JsonApi\Server\Model\Request\SaveRequestInterface`
* added class  `Enm\JsonApi\Server\Model\Request\SaveSingleResourceRequest`
* added interface  `Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface`
* added trait  `Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestTrait`
* added class  `Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequest`
* removed dependency  `symfony/event-dispatcher`
* removed dependency  `symfony/http-foundation`
* added dependency  `psr/http-message`
* added dependency  `guzzlehttp/psr7`
* added dependency  `psr/log`
* changed dependency  `enm/json-api-common` to version ^2.0
* added class  `Enm\JsonApi\Server\JsonApiServer`
* added trait  `Enm\JsonApi\Server\Model\ExceptionTrait`
* added class  `Enm\JsonApi\Server\JsonApiServer`
