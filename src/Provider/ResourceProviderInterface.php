<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Provider;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface ResourceProviderInterface
{
    /**
     * Finds a single resource by type and id
     *
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     *
     * @return ResourceInterface
     */
    public function findResource(string $type, string $id, FetchInterface $request): ResourceInterface;

    /**
     * Finds all resources of the given type
     *
     * @param string $type
     * @param FetchInterface $request
     *
     * @return ResourceInterface[]
     */
    public function findResources(string $type, FetchInterface $request): array;

    /**
     * Finds the given relationship for a resource identified by type and id
     *
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     * @param string $relationship
     *
     * @return RelationshipInterface
     */
    public function findRelationship(
        string $type,
        string $id,
        FetchInterface $request,
        string $relationship
    ): RelationshipInterface;

    /**
     * Creates a single resource
     *
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     */
    public function createResource(SaveResourceInterface $request): ResourceInterface;

    /**
     * Patches a single resource
     *
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     */
    public function patchResource(SaveResourceInterface $request): ResourceInterface;

    /**
     * Deletes a resource by type and id
     *
     * @param string $type
     * @param string $id
     *
     * @return int http status code (200|202|204)
     */
    public function deleteResource(string $type, string $id): int;

    /**
     * Returns an array of types which are supported by this provider
     *
     * @return array
     */
    public function getSupportedTypes(): array;
}
