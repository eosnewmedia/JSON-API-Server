<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Provider;

use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class ResourceProviderRegistry implements ResourceProviderInterface, ResourceProviderRegistryInterface
{
    /**
     * @var ResourceProviderInterface[]
     */
    private $providers = [];

    /**
     * @param ResourceProviderInterface $provider
     * @param string $type
     *
     * @return ResourceProviderRegistryInterface
     * @throws \Exception
     */
    public function addProvider(ResourceProviderInterface $provider, string $type): ResourceProviderRegistryInterface
    {
        if ($this->hasProvider($type)) {
            throw new \LogicException('Multiple resource providers for "' . $type . '"');
        }

        if ($provider instanceof ResourceProviderRegistryAwareInterface) {
            $provider->setProviderRegistry($this);
        }

        $this->providers[$type] = $provider;

        return $this;
    }

    /**
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     *
     * @return ResourceInterface
     * @throws \Exception
     */
    public function findResource(string $type, string $id, FetchInterface $request): ResourceInterface
    {
        return $this->provider($type)->findResource($type, $id, $request);
    }

    /**
     * @param string $type
     * @param FetchInterface $request
     *
     * @return ResourceInterface[]
     * @throws \Exception
     */
    public function findResources(string $type, FetchInterface $request): array
    {
        return $this->provider($type)->findResources($type, $request);
    }

    /**
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     * @throws \Exception
     */
    public function createResource(SaveResourceInterface $request): ResourceInterface
    {
        return $this->provider($request->resource()->getType())->createResource($request);
    }

    /**
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     * @throws \Exception
     */
    public function patchResource(SaveResourceInterface $request): ResourceInterface
    {
        return $this->provider($request->resource()->getType())->patchResource($request);
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return int
     * @throws \Exception
     */
    public function deleteResource(string $type, string $id): int
    {
        return $this->provider($type)->deleteResource($type, $id);
    }

    /**
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     * @param string $relationship
     *
     * @return RelationshipInterface
     * @throws \Exception
     */
    public function findRelationship(
        string $type,
        string $id,
        FetchInterface $request,
        string $relationship
    ): RelationshipInterface {
        return $this->provider($type)->findRelationship($type, $id, $request, $relationship);
    }

    /**
     * @param string $type
     *
     * @return ResourceProviderInterface
     * @throws UnsupportedTypeException
     */
    public function provider(string $type): ResourceProviderInterface
    {
        if (!$this->hasProvider($type)) {
            throw new UnsupportedTypeException($type);
        }

        return $this->providers[$type];
    }

    /**
     * @param string $type
     * @return bool
     */
    protected function hasProvider(string $type): bool
    {
        return array_key_exists($type, $this->providers);
    }
}
