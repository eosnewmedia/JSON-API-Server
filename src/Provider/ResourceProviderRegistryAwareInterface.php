<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Provider;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface ResourceProviderRegistryAwareInterface
{
    /**
     * This method sets the resource provider registry to make all configured
     * resource providers available for each other
     *
     * @param ResourceProviderRegistryInterface $registry
     *
     * @return void
     */
    public function setProviderRegistry(ResourceProviderRegistryInterface $registry);
}
