<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Exception\JsonApiException;
use Enm\JsonApi\JsonApiAwareInterface;
use Enm\JsonApi\JsonApiInterface;
use Enm\JsonApi\JsonApiTrait;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Error\Error;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Model\ExceptionTrait;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequest;
use Enm\JsonApi\Server\Model\Request\FetchRequest;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequest;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonApiServer implements JsonApiInterface, LoggerAwareInterface
{
    use JsonApiTrait;
    use LoggerAwareTrait;
    use ExceptionTrait;

    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    /**
     * @var string
     */
    private $apiPrefix;

    /**
     * @param RequestHandlerInterface $requestHandler
     * @param string $apiPrefix
     */
    public function __construct(RequestHandlerInterface $requestHandler, string $apiPrefix = '')
    {
        $this->requestHandler = $requestHandler;
        if ($this->requestHandler instanceof JsonApiAwareInterface) {
            $this->requestHandler->setJsonApi($this);
        }
        $this->apiPrefix = $apiPrefix;
    }

    /**
     * @return LoggerInterface
     */
    protected function logger(): LoggerInterface
    {
        if (!$this->logger instanceof LoggerInterface) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    /**
     * @return RequestHandlerInterface
     */
    protected function requestHandler(): RequestHandlerInterface
    {
        return $this->requestHandler;
    }

    /**
     * @param RequestInterface $request
     * @return FetchRequestInterface
     * @throws JsonApiException
     */
    protected function fetchRequestFromHttpRequest(RequestInterface $request): FetchRequestInterface
    {
        return new FetchRequest($request, true, $this->apiPrefix);
    }

    /**
     * @param RequestInterface $request
     * @return SaveRequestInterface
     * @throws JsonApiException
     */
    protected function saveRequestFromHttpRequest(RequestInterface $request): SaveRequestInterface
    {
        return new SaveRequest($request, $this->documentDeserializer(), $this->apiPrefix);
    }

    /**
     * @param RequestInterface $request
     * @return AdvancedJsonApiRequestInterface
     * @throws JsonApiException
     */
    protected function apiRequestFromHttpRequest(RequestInterface $request): AdvancedJsonApiRequestInterface
    {
        return new AdvancedJsonApiRequest($request, $this->apiPrefix);
    }

    /**
     * @param RequestInterface $request
     * @param bool $debug
     * @return ResponseInterface
     */
    public function handleHttpRequest(RequestInterface $request, bool $debug = false): ResponseInterface
    {
        $this->logger()->info($request->getMethod() . ' ' . (string)$request->getUri());
        $this->logger()->debug(
            $request->getMethod() . ' ' . (string)$request->getUri(),
            [
                'apiPrefix' => $this->apiPrefix,
                'headers' => $request->getHeaders(),
                'content' => (string)$request->getBody()
            ]
        );

        try {
            switch (strtoupper($request->getMethod())) {
                case 'GET':
                    return $this->handleFetch($request);

                case 'POST':
                case 'PATCH':
                    return $this->handleSave($request);

                case 'DELETE':
                    return $this->handleDelete($request);

                default:
                    throw new BadRequestException('Http method "' . $request->getMethod() . '" is not supported by json api!');
            }
        } catch (\Throwable $e) {
            return $this->handleException($e, $debug);
        }
    }

    /**
     * @param \Throwable $throwable
     * @param bool $debug
     * @return ResponseInterface
     */
    protected function handleException(\Throwable $throwable, bool $debug): ResponseInterface
    {
        $this->logger()->error(
            $throwable->getMessage(),
            [
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'code' => $throwable->getCode(),
                'trace' => $throwable->getTrace()
            ]
        );

        $apiError = Error::createFrom($throwable, $debug);

        $document = $this->singleResourceDocument();
        $document->errors()->add($apiError);
        $document->withHttpStatus($apiError->status());

        return $this->respondWith($document);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws JsonApiException
     */
    protected function handleFetch(RequestInterface $request): ResponseInterface
    {
        $fetchRequest = $this->fetchRequestFromHttpRequest($request);

        if ($fetchRequest->containsId()) {
            if ($fetchRequest->relationship() !== '') {

                $document = $this->requestHandler()->fetchRelationship($fetchRequest);
            } else {

                $document = $this->requestHandler()->fetchResource($fetchRequest);
            }
        } else {
            $document = $this->requestHandler()->fetchResources($fetchRequest);
        }


        foreach ($document->data()->all() as $resource) {
            $this->removeUnrequestedAttributes($resource, $fetchRequest);
            $this->includeRelated($document, $resource, $fetchRequest);
            $this->removeUnrequestedRelationships($resource, $fetchRequest);
        }

        return $this->respondWith($document);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws JsonApiException
     */
    protected function handleSave(RequestInterface $request): ResponseInterface
    {
        $saveRequest = $this->saveRequestFromHttpRequest($request);

        if ($saveRequest->containsId() && strtoupper($request->getMethod()) === 'POST') {
            $this->throwBadRequest('A patch request requires the http method "patch"!');
        }

        if (!$saveRequest->containsId() && strtoupper($request->getMethod()) === 'PATCH') {
            $this->throwBadRequest('A create request requires the http method "post"!');
        }

        $document = $this->requestHandler()->saveResource($saveRequest);

        return $this->respondWith($document);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws JsonApiException
     */
    protected function handleDelete(RequestInterface $request): ResponseInterface
    {
        $httpRequest = $this->apiRequestFromHttpRequest($request);
        if (!$httpRequest->containsId()) {
            $this->throwBadRequest('Missing the required resource id');
        }

        $document = $this->requestHandler()->deleteResource($httpRequest);

        return $this->respondWith($document);
    }

    /**
     * @param ResourceInterface $resource
     * @param FetchRequestInterface $request
     * @return void
     */
    protected function removeUnrequestedAttributes(ResourceInterface $resource, FetchRequestInterface $request)
    {
        foreach ($resource->attributes()->all() as $key => $value) {
            if (!$request->requestedResourceBody() || !$request->requestedField($resource->type(), $key)) {
                $resource->attributes()->remove($key);
            }
        }
    }

    /**
     * @param ResourceInterface $resource
     * @param FetchRequestInterface $request
     * @return void
     */
    protected function removeUnrequestedRelationships(ResourceInterface $resource, FetchRequestInterface $request)
    {
        if (!$request->requestedResourceBody()) {
            foreach ($resource->relationships()->all() as $relationship) {
                $resource->relationships()->removeElement($relationship);
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param ResourceInterface $resource
     * @param FetchRequestInterface $request
     *
     * @return void
     */
    protected function includeRelated(
        DocumentInterface $document,
        ResourceInterface $resource,
        FetchRequestInterface $request
    ) {
        foreach ($resource->relationships()->all() as $relationship) {
            $shouldIncludeRelationship = $request->requestedInclude($relationship->name());
            $subRequest = $request->subRequest($relationship->name());
            foreach ($relationship->related()->all() as $related) {
                $this->removeUnrequestedAttributes($related, $subRequest);

                if ($shouldIncludeRelationship && !$document->included()->has($related->type(), $related->id())) {
                    $document->included()->set($related);
                }

                $this->includeRelated($document, $related, $subRequest);

                $this->removeUnrequestedRelationships($related, $subRequest);
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @return ResponseInterface
     */
    protected function respondWith(DocumentInterface $document): ResponseInterface
    {
        return new Response(
            $document->httpStatus(),
            [
                'Content-Type' => self::CONTENT_TYPE
            ],
            $this->hasResponseBody($document) ? json_encode($this->serializeDocument($document)) : null
        );
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    protected function hasResponseBody(DocumentInterface $document): bool
    {
        $statusCodes = [201, 202, 204, 304];

        return !in_array($document->httpStatus(), $statusCodes, true) && (
                !$document->data()->isEmpty() ||
                !$document->metaInformation()->isEmpty() ||
                !$document->errors()->isEmpty()
            );
    }
}
