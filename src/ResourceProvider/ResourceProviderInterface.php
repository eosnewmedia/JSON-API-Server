<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\ResourceProvider;

use Enm\JsonApi\Server\Model\Request\FetchMainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\MainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\SaveMainRequestProviderInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface ResourceProviderInterface
{
    /**
     * Finds a single resource by type and id
     *
     * @param FetchMainRequestProviderInterface $request
     *
     * @return ResourceInterface
     */
    public function findResource(FetchMainRequestProviderInterface $request): ResourceInterface;

    /**
     * Finds all resources of the given type
     *
     * @param FetchMainRequestProviderInterface $request
     *
     * @return ResourceInterface[]
     */
    public function findResources(FetchMainRequestProviderInterface $request): array;

    /**
     * Creates a single resource
     *
     * @param SaveMainRequestProviderInterface $request
     * @return ResourceInterface
     */
    public function createResource(SaveMainRequestProviderInterface $request): ResourceInterface;

    /**
     * Patches a single resource
     *
     * @param SaveMainRequestProviderInterface $request
     * @return ResourceInterface
     */
    public function patchResource(SaveMainRequestProviderInterface $request): ResourceInterface;

    /**
     * Deletes a resource by type and id
     *
     * @param MainRequestProviderInterface $request
     *
     * @return void
     */
    public function deleteResource(MainRequestProviderInterface $request);
}
