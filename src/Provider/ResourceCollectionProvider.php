<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Provider;

use Enm\JsonApi\Exception\InvalidRequestException;
use Enm\JsonApi\Exception\ResourceNotFoundException;
use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use Enm\JsonApi\Model\Resource\ResourceCollectionInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class ResourceCollectionProvider extends AbstractResourceProvider
{
    /**
     * @var ResourceCollectionInterface
     */
    private $resources;

    /**
     * @param ResourceCollectionInterface $resources
     */
    public function __construct(ResourceCollectionInterface $resources)
    {
        $this->resources = $resources;
    }

    /**
     * @return ResourceCollectionInterface
     */
    public function resources(): ResourceCollectionInterface
    {
        return $this->resources;
    }

    /**
     * Finds a single resource by type and id
     *
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     *
     * @return ResourceInterface
     * @throws ResourceNotFoundException
     */
    public function findResource(string $type, string $id, FetchInterface $request): ResourceInterface
    {
        if (!$this->resources()->has($type, $id)) {
            throw new ResourceNotFoundException($type, $id);
        }

        return $this->resources()->get($type, $id);
    }

    /**
     * Finds all resources of the given type
     *
     * @param string $type
     * @param FetchInterface $request
     *
     * @return ResourceInterface[]
     */
    public function findResources(string $type, FetchInterface $request): array
    {
        $resources = [];

        foreach ($this->resources()->all() as $resource) {
            if ($resource->getType() === $type) {
                $resources[] = $resource;
            }
        }

        return $resources;
    }

    /**
     * Creates a single resource
     *
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     * @throws InvalidRequestException
     */
    public function createResource(SaveResourceInterface $request): ResourceInterface
    {
        throw new InvalidRequestException('Creating resources of type "' . $request->resource()->getType() . '" is not allowed');
    }

    /**
     * Patches a single resource
     *
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     * @throws InvalidRequestException
     */
    public function patchResource(SaveResourceInterface $request): ResourceInterface
    {
        throw new InvalidRequestException('Patching resources of type "' . $request->resource()->getType() . '" is not allowed');
    }

    /**
     * Deletes a resource by type and id
     *
     * @param string $type
     * @param string $id
     *
     * @return int
     * @throws InvalidRequestException
     */
    public function deleteResource(string $type, string $id): int
    {
        throw new InvalidRequestException('Deleting resources of type "' . $type . '" is not allowed');
    }
}
