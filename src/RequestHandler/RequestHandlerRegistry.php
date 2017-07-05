<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Request\JsonApiRequestInterface;
use Enm\JsonApi\Server\JsonApiAwareInterface;
use Enm\JsonApi\Server\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\HttpRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RequestHandlerRegistry implements RequestHandlerInterface, JsonApiAwareInterface
{
    use JsonApiAwareTrait;

    /**
     * @var RequestHandlerInterface[]
     */
    private $requestHandlers = [];

    /**
     * @param string $resourceType
     * @param RequestHandlerInterface $requestHandler
     *
     * @return void
     */
    public function addRequestHandler(string $resourceType, RequestHandlerInterface $requestHandler)
    {
        $this->requestHandlers[$resourceType] = $requestHandler;
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResource(FetchRequestInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->fetchResource($request);
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResources(FetchRequestInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->fetchResources($request);
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchRelationship(FetchRequestInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->fetchRelationship($request);
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function saveResource(SaveRequestInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->saveResource($request);
    }

    /**
     * @param HttpRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function deleteResource(HttpRequestInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->deleteResource($request);
    }

    /**
     * @param JsonApiRequestInterface $request
     * @return RequestHandlerInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    protected function requestHandler(JsonApiRequestInterface $request): RequestHandlerInterface
    {
        if (!array_key_exists($request->type(), $this->requestHandlers)) {
            throw new UnsupportedTypeException($request->type());
        }

        $requestHandler = $this->requestHandlers[$request->type()];
        if ($requestHandler instanceof JsonApiAwareInterface) {
            $requestHandler->setJsonApi($this->jsonApi());
        }

        return $requestHandler;
    }
}
