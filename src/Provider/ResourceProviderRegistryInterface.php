<?php
declare(strict_types=1);


namespace Enm\JsonApi\Server\Provider;

use Enm\JsonApi\Exception\UnsupportedTypeException;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface ResourceProviderRegistryInterface
{
    /**
     * @param ResourceProviderInterface $provider
     *
     * @return ResourceProviderRegistryInterface
     * @throws \InvalidArgumentException
     */
    public function addProvider(ResourceProviderInterface $provider): ResourceProviderRegistryInterface;

    /**
     * @param string $type
     *
     * @return ResourceProviderInterface
     * @throws UnsupportedTypeException
     */
    public function provider(string $type): ResourceProviderInterface;
}
