<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Exception\UnsupportedMediaTypeException;
use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\JsonApiTrait;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Error\Error;
use Enm\JsonApi\Model\Request\RequestInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Model\Response\DocumentResponse;
use Enm\JsonApi\Model\Response\ResponseInterface;
use Enm\JsonApi\Serializer\DocumentDeserializerInterface;
use Enm\JsonApi\Serializer\DocumentSerializerInterface;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonApiServer
{
    use JsonApiTrait;

    /**
     * @var DocumentDeserializerInterface
     */
    private $deserializer;

    /**
     * @var DocumentSerializerInterface
     */
    private $serializer;

    /**
     * @var RequestHandlerInterface[]
     */
    private $handlers = [];

    /**
     * @param DocumentDeserializerInterface|null $deserializer
     * @param DocumentSerializerInterface|null $serializer
     */
    public function __construct(
        ?DocumentDeserializerInterface $deserializer = null,
        ?DocumentSerializerInterface $serializer = null
    ) {
        $this->deserializer = $deserializer;
        $this->serializer = $serializer;
    }

    /**
     * @param string|null $requestBody
     * @return DocumentInterface|null
     */
    public function createRequestBody(?string $requestBody): ?DocumentInterface
    {
        return (string)$requestBody !== '' ?
            $this->deserializer->deserializeDocument(json_decode($requestBody, true)) : null;
    }

    /**
     * Adds a request handler
     *
     * @param string $type
     * @param RequestHandlerInterface $handler
     */
    public function addHandler(string $type, RequestHandlerInterface $handler): void
    {
        $this->handlers[$type] = $handler;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws BadRequestException
     * @throws UnsupportedTypeException
     * @throws UnsupportedMediaTypeException
     */
    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        if ($request->headers()->getRequired('Content-Type') !== 'application/vnd.api+json') {
            throw new UnsupportedMediaTypeException($request->headers()->getRequired('Content-Type'));
        }

        switch ($request->method()) {
            case 'GET':
                if ($request->id()) {
                    if ($request->relationship()) {
                        $response = $this->getHandler($request->type())->fetchRelationship($request);
                        break;
                    }
                    $response = $this->getHandler($request->type())->fetchResource($request);
                    break;
                }
                $response = $this->getHandler($request->type())->fetchResources($request);
                break;
            case 'POST':
                if ($request->relationship()) {
                    $response = $this->getHandler($request->type())->addRelatedResources($request);
                    break;
                }
                $response = $this->getHandler($request->type())->createResource($request);
                break;
            case 'PATCH':
                if ($request->relationship()) {
                    $response = $this->getHandler($request->type())->replaceRelatedResources($request);
                    break;
                }
                $response = $this->getHandler($request->type())->patchResource($request);
                break;
            case 'DELETE':
                if ($request->relationship()) {
                    $response = $this->getHandler($request->type())->removeRelatedResources($request);
                    break;
                }
                $response = $this->getHandler($request->type())->deleteResource($request);
                break;
            default:
                throw new BadRequestException('Something was wrong...');
        }

        $document = $response->document();
        if ($document) {
            foreach ($document->data()->all() as $resource) {
                $this->includeRelated($document, $resource, $request);
                $this->cleanUpResource($resource, $request);
            }

        }

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @return string
     */
    public function createResponseBody(ResponseInterface $response): string
    {
        return $response->document() ? json_encode($this->serializer->serializeDocument($response->document())) : '';
    }

    /**
     * @param \Throwable $throwable
     * @param bool $debug
     *
     * @return ResponseInterface
     */
    public function handleException(\Throwable $throwable, bool $debug = false): ResponseInterface
    {
        $apiError = Error::createFrom($throwable, $debug);

        $document = $this->singleResourceDocument();
        $document->errors()->add($apiError);


        return new DocumentResponse($document, null, $apiError->status());
    }

    /**
     * @param string $type
     * @return RequestHandlerInterface
     * @throws UnsupportedTypeException
     */
    private function getHandler(string $type): RequestHandlerInterface
    {
        if (!array_key_exists($type, $this->handlers)) {
            throw new UnsupportedTypeException($type);
        }

        return $this->handlers[$type];
    }


    /**
     * @param DocumentInterface $document
     * @param ResourceInterface $resource
     * @param RequestInterface $request
     *
     * @return void
     */
    protected function includeRelated(
        DocumentInterface $document,
        ResourceInterface $resource,
        RequestInterface $request
    ): void {
        foreach ($resource->relationships()->all() as $relationship) {
            $shouldIncludeRelationship = $request->requestsInclude($relationship->name());
            $subRequest = $request->createSubRequest($relationship->name(), $resource);
            foreach ($relationship->related()->all() as $related) {
                if ($shouldIncludeRelationship && !$document->included()->has($related->type(), $related->id())) {
                    $document->included()->set($related);
                }
                $this->includeRelated($document, $related, $subRequest);
                $this->cleanUpResource($related, $subRequest);
            }
        }
    }

    /**
     * @param ResourceInterface $resource
     * @param RequestInterface $request
     */
    protected function cleanUpResource(ResourceInterface $resource, RequestInterface $request): void
    {
        foreach ($resource->attributes()->all() as $key => $value) {
            if (!$request->requestsAttributes() || !$request->requestsField($resource->type(), $key)) {
                $resource->attributes()->remove($key);
            }
        }

        if (!$request->requestsRelationships()) {
            foreach ($resource->relationships()->all() as $relationship) {
                $resource->relationships()->removeElement($relationship);
            }
        }
    }
}
