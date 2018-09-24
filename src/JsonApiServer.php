<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server;

use Enm\JsonApi\Exception\BadRequestException;
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
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    /**
     * @var DocumentSerializerInterface
     */
    private $serializer;

    /**
     * @param DocumentDeserializerInterface $deserializer
     * @param RequestHandlerInterface $requestHandler
     * @param DocumentSerializerInterface $serializer
     */
    public function __construct(
        DocumentDeserializerInterface $deserializer,
        RequestHandlerInterface $requestHandler,
        DocumentSerializerInterface $serializer
    ) {
        $this->deserializer = $deserializer;
        $this->requestHandler = $requestHandler;
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
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws BadRequestException
     */
    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        switch ($request->method()) {
            case 'GET':
                if ($request->id()) {
                    if ($request->relationship()) {
                        $response = $this->requestHandler->fetchRelationship($request);
                        break;
                    }
                    $response = $this->requestHandler->fetchResource($request);
                    break;
                }
                $response = $this->requestHandler->fetchResources($request);
                break;
            case 'POST':
                if ($request->relationship()) {
                    $response = $this->requestHandler->addRelatedResources($request);
                    break;
                }
                $response = $this->requestHandler->createResource($request);
                break;
            case 'PATCH':
                if ($request->relationship()) {
                    $response = $this->requestHandler->replaceRelatedResources($request);
                    break;
                }
                $response = $this->requestHandler->patchResource($request);
                break;
            case 'DELETE':
                if ($request->relationship()) {
                    $response = $this->requestHandler->removeRelatedResources($request);
                    break;
                }
                $response = $this->requestHandler->deleteResource($request);
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
