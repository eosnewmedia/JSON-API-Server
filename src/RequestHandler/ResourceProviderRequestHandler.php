<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\JsonApiAwareInterface;
use Enm\JsonApi\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class ResourceProviderRequestHandler implements RequestHandlerInterface, JsonApiAwareInterface
{
    use JsonApiAwareTrait;
    use SeparatedSaveTrait;
    use FetchRelationshipTrait;

    /**
     * @var ResourceProviderInterface[]
     */
    private $resourceProviders = [];

    /**
     * @param string $type
     * @param ResourceProviderInterface $resourceProvider
     *
     * @return void
     */
    public function addResourceProvider(string $type, ResourceProviderInterface $resourceProvider)
    {
        $this->resourceProviders[$type] = $resourceProvider;
    }

    /**
     * @param AdvancedJsonApiRequestInterface $request
     *
     * @return ResourceProviderInterface
     * @throws UnsupportedTypeException
     * @throws \RuntimeException
     */
    protected function resourceProvider(AdvancedJsonApiRequestInterface $request): ResourceProviderInterface
    {
        if (!array_key_exists($request->type(), $this->resourceProviders)) {
            throw new UnsupportedTypeException($request->type());
        }

        $provider = $this->resourceProviders[$request->type()];
        if ($provider instanceof JsonApiAwareInterface) {
            $provider->setJsonApi($this->jsonApi());
        }

        return $provider;
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     * @throws UnsupportedTypeException
     */
    public function fetchResource(FetchRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->singleResourceDocument($this->resourceProvider($request)->findResource($request));
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     * @throws UnsupportedTypeException
     */
    public function fetchResources(FetchRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->multiResourceDocument($this->resourceProvider($request)->findResources($request));
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     * @throws UnsupportedTypeException
     */
    protected function createResource(SaveRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->singleResourceDocument(
            $this->resourceProvider($request)->createResource($request)
        );
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     * @throws UnsupportedTypeException
     */
    protected function patchResource(SaveRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->singleResourceDocument(
            $this->resourceProvider($request)->patchResource($request)
        );
    }

    /**
     * @param AdvancedJsonApiRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     * @throws UnsupportedTypeException
     */
    public function deleteResource(AdvancedJsonApiRequestInterface $request): DocumentInterface
    {
        $this->resourceProvider($request)->deleteResource($request);

        $document = $this->jsonApi()->singleResourceDocument();
        $document->withHttpStatus(204);

        return $document;
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     * @throws UnsupportedTypeException
     */
    public function modifyRelationship(SaveRequestInterface $request): DocumentInterface
    {
        $relationship = $this->resourceProvider($request)->modifyRelationship($request);

        if ($relationship->shouldBeHandledAsCollection()) {
            return $this->jsonApi()->multiResourceDocument($relationship->related()->all());
        }

        return $this->jsonApi()->singleResourceDocument(
            $relationship->related()->isEmpty() ? null : $relationship->related()->first()
        );
    }
}
