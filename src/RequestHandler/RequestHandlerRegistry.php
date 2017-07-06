<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Request\JsonApiRequestInterface;
use Enm\JsonApi\Server\JsonApiAwareInterface;
use Enm\JsonApi\Server\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\Request\FetchMainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\MainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\SaveMainRequestProviderInterface;

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
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResource(FetchMainRequestProviderInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->fetchResource($request);
    }

    /**
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResources(FetchMainRequestProviderInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->fetchResources($request);
    }

    /**
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchRelationship(FetchMainRequestProviderInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->fetchRelationship($request);
    }

    /**
     * @param SaveMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function saveResource(SaveMainRequestProviderInterface $request): DocumentInterface
    {
        return $this->requestHandler($request)->saveResource($request);
    }

    /**
     * @param MainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function deleteResource(MainRequestProviderInterface $request): DocumentInterface
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
