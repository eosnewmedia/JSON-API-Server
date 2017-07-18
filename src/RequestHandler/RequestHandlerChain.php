<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Request\JsonApiRequestInterface;
use Enm\JsonApi\JsonApiAwareInterface;
use Enm\JsonApi\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
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
        return $this->execute('fetchResource', $request);
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchResources(FetchRequestInterface $request): DocumentInterface
    {
        return $this->execute('fetchResources', $request);
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function fetchRelationship(FetchRequestInterface $request): DocumentInterface
    {
        return $this->execute('fetchRelationship', $request);
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function saveResource(SaveRequestInterface $request): DocumentInterface
    {
        return $this->execute('saveResource', $request);
    }

    /**
     * @param AdvancedJsonApiRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function deleteResource(AdvancedJsonApiRequestInterface $request): DocumentInterface
    {
        return $this->execute('deleteResource', $request);
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    public function modifyRelationship(SaveRequestInterface $request): DocumentInterface
    {
        return $this->execute('modifyRelationship', $request);
    }

    /**
     * @param string $method
     * @param JsonApiRequestInterface $request
     * @return DocumentInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    private function execute(string $method, JsonApiRequestInterface $request): DocumentInterface
    {
        foreach ($this->requestHandlers as $requestHandler) {
            try {
                if ($requestHandler instanceof JsonApiAwareInterface) {
                    $requestHandler->setJsonApi($this->jsonApi());
                }

                return $requestHandler->$method($request);
            } catch (UnsupportedTypeException $e) {

            }
        }

        throw new UnsupportedTypeException($request->type());
    }
}
