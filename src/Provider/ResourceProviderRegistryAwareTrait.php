<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Provider;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait ResourceProviderRegistryAwareTrait
{
    /**
     * @var ResourceProviderRegistryInterface
     */
    private $providerRegistry;

    /**
     * This method sets the resource provider registry to make all configured
     * resource providers available for each other
     *
     * @param ResourceProviderRegistryInterface $registry
     *
     * @return void
     */
    public function setProviderRegistry(ResourceProviderRegistryInterface $registry)
    {
        $this->providerRegistry = $registry;
    }

    /**
     * @return ResourceProviderRegistryInterface
     * @throws \RuntimeException
     */
    public function providerRegistry(): ResourceProviderRegistryInterface
    {
        if (!$this->providerRegistry instanceof ResourceProviderRegistryInterface) {
            throw new \RuntimeException('Missing resource provider registry!');
        }

        return $this->providerRegistry;
    }
}
