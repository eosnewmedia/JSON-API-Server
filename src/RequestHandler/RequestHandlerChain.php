<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\JsonApiAwareInterface;
use Enm\JsonApi\Server\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\HttpRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RequestHandlerChain implements RequestHandlerInterface, JsonApiAwareInterface
{
    use JsonApiAwareTrait;

    /**
     * @var RequestHandlerInterface[]
     */
    private $requestHandlers = [];

    /**
     * @param RequestHandlerInterface $requestHandler
     *
     * @return void
     */
    public function addRequestHandler(RequestHandlerInterface $requestHandler)
    {
        $this->requestHandlers[] = $requestHandler;
    }
    
    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResource(FetchRequestInterface $request): DocumentInterface
    {
        foreach ($this->requestHandlers as $requestHandler) {
            try {
                $this->configure($requestHandler);

                return $requestHandler->fetchResource($request);
            } catch (UnsupportedTypeException $e) {

            }
        }

        throw new UnsupportedTypeException($request->type());
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResources(FetchRequestInterface $request): DocumentInterface
    {
        foreach ($this->requestHandlers as $requestHandler) {
            try {
                $this->configure($requestHandler);

                return $requestHandler->fetchResources($request);
            } catch (UnsupportedTypeException $e) {

            }
        }

        throw new UnsupportedTypeException($request->type());
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchRelationship(FetchRequestInterface $request): DocumentInterface
    {
        foreach ($this->requestHandlers as $requestHandler) {
            try {
                $this->configure($requestHandler);

                return $requestHandler->fetchRelationship($request);
            } catch (UnsupportedTypeException $e) {

            }
        }

        throw new UnsupportedTypeException($request->type());
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function saveResource(SaveRequestInterface $request): DocumentInterface
    {
        foreach ($this->requestHandlers as $requestHandler) {
            try {
                $this->configure($requestHandler);

                return $requestHandler->saveResource($request);
            } catch (UnsupportedTypeException $e) {

            }
        }

        throw new UnsupportedTypeException($request->type());
    }

    /**
     * @param HttpRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function deleteResource(HttpRequestInterface $request): DocumentInterface
    {
        foreach ($this->requestHandlers as $requestHandler) {
            try {
                $this->configure($requestHandler);

                return $requestHandler->deleteResource($request);
            } catch (UnsupportedTypeException $e) {

            }
        }

        throw new UnsupportedTypeException($request->type());
    }

    /**
     * @param RequestHandlerInterface $requestHandler
     *
     * @return void
     * @throws \RuntimeException
     */
    protected function configure(RequestHandlerInterface $requestHandler)
    {
        if ($requestHandler instanceof JsonApiAwareInterface) {
            $requestHandler->setJsonApi($this->jsonApi());
        }
    }
}
