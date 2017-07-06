<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\JsonApiAwareInterface;
use Enm\JsonApi\Server\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\Request\FetchMainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\MainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\SaveMainRequestProviderInterface;

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
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResource(FetchMainRequestProviderInterface $request): DocumentInterface
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
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResources(FetchMainRequestProviderInterface $request): DocumentInterface
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
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchRelationship(FetchMainRequestProviderInterface $request): DocumentInterface
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
     * @param SaveMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function saveResource(SaveMainRequestProviderInterface $request): DocumentInterface
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
     * @param MainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function deleteResource(MainRequestProviderInterface $request): DocumentInterface
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
