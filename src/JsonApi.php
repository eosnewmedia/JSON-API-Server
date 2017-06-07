<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server;

use Enm\JsonApi\Server\Event\DocumentEvent;
use Enm\JsonApi\Server\Event\DocumentResponseEvent;
use Enm\JsonApi\Server\Event\FetchEvent;
use Enm\JsonApi\Server\Event\ResourceEvent;
use Enm\JsonApi\Exception\InvalidRequestException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Document\ErrorDocument;
use Enm\JsonApi\Model\Document\RelationshipCollectionDocument;
use Enm\JsonApi\Model\Document\RelationshipDocument;
use Enm\JsonApi\Model\Document\ResourceCollectionDocument;
use Enm\JsonApi\Model\Document\ResourceDocument;
use Enm\JsonApi\Model\Error\ErrorInterface;
use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Provider\ResourceProviderInterface;
use Enm\JsonApi\Serializer\DocumentSerializerInterface;
use Enm\JsonApi\Serializer\Serializer;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonApi
{
    const ON_FETCH = 'enm.json_api.on_fetch';

    const BEFORE_NORMALIZE_RESOURCE = 'enm.json_api.before_normalize_resource';

    const ON_INCLUDE_RESOURCE = 'enm.json_api.on_include_resource';

    const BEFORE_SERIALIZE_DOCUMENT = 'enm.json_api.before_serialize_document';

    const BEFORE_DOCUMENT_RESPONSE = 'enm.json_api.before_document_response';

    const CONTENT_TYPE = 'application/vnd.api+json';

    /**
     * @var ResourceProviderInterface
     */
    private $resourceProvider;

    /**
     * @var DocumentSerializerInterface
     */
    private $serializer;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param ResourceProviderInterface $resourceProvider
     */
    public function __construct(ResourceProviderInterface $resourceProvider)
    {
        $this->resourceProvider = $resourceProvider;
    }

    /**
     * @param DocumentSerializerInterface $serializer
     *
     * @return $this
     */
    public function setSerializer(DocumentSerializerInterface $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     *
     * @return $this
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     *
     * @return Response
     * @throws \Exception
     */
    public function fetchResource(string $type, string $id, FetchInterface $request): Response
    {
        $this->dispatch(
            self::ON_FETCH,
            new FetchEvent($request, $type, $id)
        );

        $resource = $this->getResourceProvider()
            ->findResource($type, $id, $request);

        $this->dispatch(
            self::BEFORE_NORMALIZE_RESOURCE,
            new ResourceEvent($resource, $request)
        );

        $this->normalizeRequestedResource($resource, $request);

        $document = new ResourceDocument($resource);
        $this->includeRelations($document, $request);

        return $this->buildResponse($document, $request->getHttpRequest());
    }

    /**
     * @param string $type
     * @param FetchInterface $request
     *
     * @return Response
     * @throws \Exception
     */
    public function fetchResources(string $type, FetchInterface $request): Response
    {
        $this->dispatch(
            self::ON_FETCH,
            new FetchEvent($request, $type)
        );

        $resources = $this->getResourceProvider()
            ->findResources($type, $request);

        $this->normalizeRequestedResources($request, $resources);

        $document = new ResourceCollectionDocument($resources);
        $this->includeRelations($document, $request);

        return $this->buildResponse($document, $request->getHttpRequest());
    }

    /**
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     * @param string $relationship
     *
     * @return Response
     * @throws \Exception
     */
    public function fetchRelationship(string $type, string $id, FetchInterface $request, string $relationship): Response
    {
        $relation = $this->findRelationship(
            $type,
            $id,
            $request,
            $relationship
        );

        $relatedResources = $relation->related()->all();
        switch ($relation->getType()) {
            case RelationshipInterface::TYPE_MANY:
                $document = new RelationshipCollectionDocument($relatedResources);
                break;
            case RelationshipInterface::TYPE_ONE:
                $document = new RelationshipDocument(
                    $relation->related()->isEmpty() ?
                        null : array_shift($relatedResources)
                );
                break;
            default:
                throw new InvalidRequestException('Invalid relationship');
        }

        $this->includeRelations($document, $request);

        return $this->buildResponse($document, $request->getHttpRequest());
    }

    /**
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     * @param string $relationship
     *
     * @return Response
     * @throws \Exception
     */
    public function fetchRelated(string $type, string $id, FetchInterface $request, string $relationship): Response
    {
        $request->addInclude($relationship);

        $relation = $this->findRelationship(
            $type,
            $id,
            $request,
            $relationship
        );

        $resources = $relation->related()->all();
        $this->normalizeRequestedResources($request, $resources);

        switch ($relation->getType()) {
            case RelationshipInterface::TYPE_MANY:
                $document = new ResourceCollectionDocument($resources);
                break;
            case RelationshipInterface::TYPE_ONE:
                $document = new ResourceDocument(
                    $relation->related()->isEmpty() ?
                        null : array_shift($resources)
                );
                break;
            default:
                throw new InvalidRequestException('Invalid relationship');
        }

        $this->includeRelations($document, $request);

        return $this->buildResponse($document, $request->getHttpRequest());
    }

    /**
     * @param string $type
     * @param SaveResourceInterface $request
     *
     * @return Response
     * @throws \Exception
     */
    public function createResource(string $type, SaveResourceInterface $request): Response
    {
        if ($request->resource()->getType() !== $type) {
            throw new InvalidRequestException('Invalid resource type');
        }

        $resource = $this->getResourceProvider()->createResource($request);
        $document = new ResourceDocument($resource);

        return $this->buildResponse($document, $request->getHttpRequest());
    }

    /**
     * @param string $type
     * @param string $id
     * @param SaveResourceInterface $request
     *
     * @return Response
     * @throws \Exception
     */
    public function patchResource(string $type, string $id, SaveResourceInterface $request): Response
    {
        if ($request->resource()->getType() !== $type) {
            throw new InvalidRequestException('Invalid resource type');
        }

        if (!$request->containsId() || $request->resource()->getId() !== $id) {
            throw new InvalidRequestException('Invalid resource id');
        }

        $resource = $this->getResourceProvider()->patchResource($request);
        $document = new ResourceDocument($resource);

        return $this->buildResponse($document, $request->getHttpRequest());
    }

    /**
     * @param string $type
     * @param string $id
     * @param Request|null $request
     *
     * @return Response
     * @throws \Exception
     */
    public function deleteResource(string $type, string $id, Request $request = null): Response
    {
        if (!$request instanceof Request) {
            $request = Request::createFromGlobals();
        }

        return $this->buildResponse(
            new ResourceDocument(),
            $request,
            $this->getResourceProvider()->deleteResource($type, $id)
        );
    }

    /**
     * @param ErrorInterface $error
     * @param Request|null $request
     *
     * @return Response
     * @throws \Exception
     */
    public function handleError(ErrorInterface $error, Request $request = null): Response
    {
        if (!$request instanceof Request) {
            $request = Request::createFromGlobals();
        }

        $document = new ErrorDocument([$error]);

        return $this->buildResponse($document, $request, $error->getStatus());
    }

    /**
     * @return ResourceProviderInterface
     */
    protected function getResourceProvider(): ResourceProviderInterface
    {
        return $this->resourceProvider;
    }

    /**
     * @return DocumentSerializerInterface
     */
    protected function getSerializer(): DocumentSerializerInterface
    {
        if (!$this->serializer instanceof DocumentSerializerInterface) {
            $this->serializer = new Serializer();
        }

        return $this->serializer;
    }

    /**
     * @param string $eventName
     * @param Event $eventObject
     *
     * @return JsonApi
     */
    protected function dispatch(string $eventName, Event $eventObject): JsonApi
    {
        if ($this->dispatcher !== null) {
            $this->dispatcher->dispatch($eventName, $eventObject);
        }

        return $this;
    }

    /**
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     * @param string $relationship
     *
     * @return RelationshipInterface
     */
    private function findRelationship(
        string $type,
        string $id,
        FetchInterface $request,
        string $relationship
    ): RelationshipInterface {
        return $this->getResourceProvider()->findRelationship(
            $type,
            $id,
            $request,
            $relationship
        );
    }

    /**
     * @param DocumentInterface $document
     * @param FetchInterface $request
     *
     * @return JsonApi
     */
    private function includeRelations(DocumentInterface $document, FetchInterface $request): JsonApi
    {
        foreach ($document->data()->all() as $resource) {
            $this->includeResourceRelations($document, $resource, $request);
        }

        return $this;
    }

    /**
     * @param DocumentInterface $document
     * @param ResourceInterface $resource
     * @param FetchInterface $request
     *
     * @return JsonApi
     */
    private function includeResourceRelations(
        DocumentInterface $document,
        ResourceInterface $resource,
        FetchInterface $request
    ): JsonApi {
        foreach ($resource->relationships()->all() as $relationship) {
            $subRequest = $request->subRequest($relationship->getName());
            $includeRelated = $request->shouldIncludeRelationship(
                $relationship->getName()
            );

            foreach ($relationship->related()->all() as $related) {
                if ($includeRelated) {
                    $this->dispatch(
                        self::ON_INCLUDE_RESOURCE,
                        new ResourceEvent($related, $request)
                    );

                    $this->normalizeRequestedResource($related, $request);

                    $document->included()->set($related);
                }

                $this->includeResourceRelations(
                    $document,
                    $related,
                    $subRequest
                );
            }
        }

        return $this;
    }

    /**
     * @param FetchInterface $request
     * @param ResourceInterface[] $resources
     *
     * @return JsonApi
     */
    private function normalizeRequestedResources(FetchInterface $request, array $resources = []): JsonApi
    {
        foreach ($resources as $resource) {
            $this->normalizeRequestedResource($resource, $request);
        }

        return $this;
    }

    /**
     * @param ResourceInterface $resource
     * @param FetchInterface $request
     *
     * @return JsonApi
     */
    private function normalizeRequestedResource(ResourceInterface $resource, FetchInterface $request): JsonApi
    {
        $this->dispatch(
            self::BEFORE_NORMALIZE_RESOURCE,
            new ResourceEvent($resource, $request)
        );

        foreach ($resource->attributes()->all() as $name => $attribute) {
            $shouldContainAttributes = $request->shouldContainAttribute(
                $resource->getType(),
                $name
            );
            if (!$shouldContainAttributes) {
                $resource->attributes()->remove($name);
            }
        }

        return $this;
    }

    /**
     * @param DocumentInterface $document
     * @param Request $request
     * @param int $statusCode
     *
     * @return Response
     * @throws \Exception
     */
    private function buildResponse(DocumentInterface $document, Request $request, int $statusCode = 0): Response
    {
        $this->dispatch(
            self::BEFORE_SERIALIZE_DOCUMENT,
            new DocumentEvent($document, $request)
        );


        $response = new Response();
        try {
            $response->setStatusCode($statusCode);
        } catch (\InvalidArgumentException $e) {
            $response->setStatusCode(Response::HTTP_OK);
        }
        if ($response->getStatusCode() !== Response::HTTP_NO_CONTENT) {
            $serialized = $this->getSerializer()->serializeDocument($document);
            $response->setContent(json_encode($serialized));
        }
        $response->headers->set('Content-Type', self::CONTENT_TYPE);

        $this->dispatch(
            self::BEFORE_DOCUMENT_RESPONSE,
            new DocumentResponseEvent($document, $request, $response)
        );

        return $response;
    }
}
