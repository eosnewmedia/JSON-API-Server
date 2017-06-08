<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Provider;

use Enm\JsonApi\Exception\HttpException;
use Enm\JsonApi\Exception\InvalidRequestException;
use Enm\JsonApi\Model\Common\KeyValueCollectionInterface;
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
        throw $this->createResourceNotFoundException($type, $id);
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
        throw $this->createUnsupportedTypeException($type);
    }

    /**
     * @return ResourceProviderRegistryInterface
     */
    public function getProviderRegistry(): ResourceProviderRegistryInterface
    {
        return $this->providerRegistry();
    }

    /**
     * @return KeyValueCollectionInterface
     */
    public function executeCreateKeyValueCollection(): KeyValueCollectionInterface
    {
        return $this->createKeyValueCollection();
    }

    /**
     * @return InvalidRequestException
     */
    public function executeCreateInvalidRequestException(): InvalidRequestException
    {
        return $this->createInvalidRequestException('Test');
    }

    /**
     * @return HttpException
     */
    public function executeCreateHttpException(): HttpException
    {
        return $this->createHttpException(200, 'OK');
    }
}
