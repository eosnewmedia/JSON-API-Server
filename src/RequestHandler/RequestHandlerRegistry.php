<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Model\Request\RequestInterface;
use Enm\JsonApi\Model\Response\ResponseInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RequestHandlerRegistry implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface[]
     */
    private $handlers = [];

    /**
     * @param string $type
     * @param RequestHandlerInterface $handler
     */
    public function addHandler(string $type, RequestHandlerInterface $handler): void
    {
        $this->handlers[$type] = $handler;
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
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function fetchResource(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->fetchResource($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function fetchResources(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->fetchResources($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function fetchRelationship(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->fetchRelationship($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function createResource(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->createResource($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function patchResource(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->patchResource($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function deleteResource(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->deleteResource($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function addRelatedResources(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->addRelatedResources($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function replaceRelatedResources(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->replaceRelatedResources($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws UnsupportedTypeException
     */
    public function removeRelatedResources(RequestInterface $request): ResponseInterface
    {
        return $this->getHandler($request->type())->removeRelatedResources($request);
    }
}
