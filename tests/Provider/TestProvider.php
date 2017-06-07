<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Provider;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Provider\AbstractImmutableResourceProvider;
use Enm\JsonApi\Server\Provider\ResourceProviderRegistryInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class TestProvider extends AbstractImmutableResourceProvider
{
    /**
     * Finds a single resource by type and id
     *
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     *
     * @return ResourceInterface
     * @throws \Exception
     */
    public function findResource(string $type, string $id, FetchInterface $request): ResourceInterface
    {
        throw new \RuntimeException();
    }

    /**
     * Finds all resources of the given type
     *
     * @param string $type
     * @param FetchInterface $request
     *
     * @return ResourceInterface[]
     * @throws \Exception
     */
    public function findResources(string $type, FetchInterface $request): array
    {
        throw new \RuntimeException();
    }

    /**
     * @return ResourceProviderRegistryInterface
     */
    public function getProviderRegistry(): ResourceProviderRegistryInterface
    {
        return $this->providerRegistry();
    }
}
