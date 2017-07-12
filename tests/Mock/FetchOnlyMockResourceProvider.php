<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Mock;

use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\ResourceProvider\FetchOnlyTrait;
use Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class FetchOnlyMockResourceProvider implements ResourceProviderInterface
{
    use FetchOnlyTrait;

    /**
     * Finds a single resource by type and id
     *
     * @param FetchRequestInterface $request
     *
     * @return ResourceInterface
     * @throws \RuntimeException
     */
    public function findResource(FetchRequestInterface $request): ResourceInterface
    {
        throw new \RuntimeException();
    }

    /**
     * Finds all resources of the given type
     *
     * @param FetchRequestInterface $request
     *
     * @return ResourceInterface[]
     * @throws \RuntimeException
     */
    public function findResources(FetchRequestInterface $request): array
    {
        throw new \RuntimeException();
    }
}
