<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\ResourceProvider;

use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRelationshipRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface ResourceProviderInterface
{
    /**
     * Finds a single resource by type and id
     *
     * @param FetchRequestInterface $request
     *
     * @return ResourceInterface
     */
    public function findResource(FetchRequestInterface $request): ResourceInterface;

    /**
     * Finds all resources of the given type
     *
     * @param FetchRequestInterface $request
     *
     * @return ResourceInterface[]
     */
    public function findResources(FetchRequestInterface $request): array;

    /**
     * Creates a single resource
     *
     * @param SaveRequestInterface $request
     * @return ResourceInterface
     */
    public function createResource(SaveRequestInterface $request): ResourceInterface;

    /**
     * Patches a single resource
     *
     * @param SaveRequestInterface $request
     * @return ResourceInterface
     */
    public function patchResource(SaveRequestInterface $request): ResourceInterface;

    /**
     * Deletes a resource by type and id
     *
     * @param AdvancedJsonApiRequestInterface $request
     *
     * @return void
     */
    public function deleteResource(AdvancedJsonApiRequestInterface $request);

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return RelationshipInterface
     */
    public function modifyRelationship(SaveRelationshipRequestInterface $request): RelationshipInterface;
}
