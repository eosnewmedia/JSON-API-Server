<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Exception\JsonApiException;
use Enm\JsonApi\JsonApiInterface;
use Enm\JsonApi\JsonApiTrait;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Error\Error;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Model\Request\HttpRequest;
use Enm\JsonApi\Server\Model\Request\FetchRequest;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\HttpRequestInterface;
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
    protected function fetchRequestFromRequest(RequestInterface $request): FetchRequestInterface
    {
        return new FetchRequest($request, true, $this->apiPrefix);
    }

    /**
     * @param RequestInterface $request
     * @return SaveRequestInterface
     * @throws JsonApiException
     */
    protected function saveRequestFromRequest(RequestInterface $request): SaveRequestInterface
    {
        return new SaveRequest($request, $this->documentFactory(), $this->apiPrefix);
    }

    /**
     * @param RequestInterface $request
     * @return HttpRequestInterface
     * @throws JsonApiException
     */
    protected function httpRequestFromRequest(RequestInterface $request): HttpRequestInterface
    {
        return new HttpRequest($request, $this->apiPrefix);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function handleFetch(RequestInterface $request): ResponseInterface
    {
        try {
            $fetchRequest = $this->fetchRequestFromRequest($request);

            if ($fetchRequest->containsId()) {
                if ($fetchRequest->requestedRelationship() !== '') {

                    $document = $this->requestHandler()->fetchRelationship($fetchRequest);
                } else {

                    $document = $this->requestHandler()->fetchResource($fetchRequest);
                }
            } else {
                $document = $this->requestHandler()->fetchResources($fetchRequest);
            }


            foreach ($document->data()->all() as $resource) {
                $this->normalizeResource($resource, $fetchRequest);
                $this->includeRelated($document, $resource, $fetchRequest);
            }

            return $this->respondWith($document);
        } catch (JsonApiException $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function handleSave(RequestInterface $request): ResponseInterface
    {
        try {
            $saveRequest = $this->saveRequestFromRequest($request);

            if ($saveRequest->containsId() && strtoupper($saveRequest->httpRequest()->getMethod()) === 'POST') {
                throw new BadRequestException('A post request can not have an id in the path!');
            }

            if (!$saveRequest->containsId() && strtoupper($saveRequest->httpRequest()->getMethod()) === 'PATCH') {
                throw new BadRequestException('A patch request requires an id in the path!');
            }

            $document = $this->requestHandler()->saveResource($saveRequest);

            return $this->respondWith($document);
        } catch (JsonApiException $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function handleDelete(RequestInterface $request): ResponseInterface
    {
        try {
            $httpRequest = $this->httpRequestFromRequest($request);
            if (!$httpRequest->containsId()) {
                throw new BadRequestException('Missing the required resource id');
            }

            $document = $this->requestHandler()->deleteResource($httpRequest);

            return $this->respondWith($document);
        } catch (JsonApiException $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @param \Exception $exception
     * @return ResponseInterface
     */
    public function handleException(\Exception $exception): ResponseInterface
    {
        $this->logger()->critical(
            $exception->getMessage(),
            [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTrace()
            ]
        );

        $apiError = Error::createFromException($exception);

        $document = $this->singleResourceDocument();
        $document->errors()->add($apiError);
        $document->withHttpStatus($apiError->status());

        return $this->respondWith($document);
    }

    /**
     * @param ResourceInterface $resource
     * @param FetchRequestInterface $request
     * @return void
     */
    protected function normalizeResource(ResourceInterface $resource, FetchRequestInterface $request)
    {
        foreach ($resource->attributes()->all() as $key => $value) {
            if (!$request->shouldContainAttribute($resource->type(), $key)) {
                $resource->attributes()->remove($key);
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
            $shouldIncludeRelationship = $request->shouldIncludeRelationship($relationship->name());
            foreach ($relationship->related()->all() as $related) {
                if ($shouldIncludeRelationship && $document->included()->has($related->type(), $related->id())) {
                    $this->normalizeResource($related, $request);
                    $document->included()->set($related);
                }

                $this->includeRelated($document, $related, $request->subRequest($relationship->name()));
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
            $this->hasResponseBody($document) ? $this->serializeDocument($document) : null
        );
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    protected function hasResponseBody(DocumentInterface $document): bool
    {
        $statusCodes = range(300, 400);
        $statusCodes[] = 201;
        $statusCodes[] = 202;
        $statusCodes[] = 204;

        return !in_array($document->httpStatus(), $statusCodes, true) &&
            $document->data()->isEmpty() &&
            $document->metaInformation()->isEmpty() &&
            $document->errors()->isEmpty();
    }
}
