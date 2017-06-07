<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Provider;

use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
abstract class AbstractImmutableResourceProvider extends AbstractResourceProvider
{
    /**
     * Creates a single resource
     *
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     * @throws \Exception
     */
    public function createResource(SaveResourceInterface $request): ResourceInterface
    {
        throw $this->createNotAllowedException('Creating resources of type ' . $request->resource()->getType() . ' is not allowed!');
    }

    /**
     * Patches a single resource
     *
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     * @throws \Exception
     */
    public function patchResource(SaveResourceInterface $request): ResourceInterface
    {
        throw $this->createNotAllowedException('Patching resources of type ' . $request->resource()->getType() . ' is not allowed!');
    }

    /**
     * Deletes a resource by type and id
     *
     * @param string $type
     * @param string $id
     *
     * @return int http status code (200|202|204)
     * @throws \Exception
     */
    public function deleteResource(string $type, string $id): int
    {
        throw $this->createNotAllowedException('Deleting resources of type ' . $type . ' is not allowed!');
    }
}
