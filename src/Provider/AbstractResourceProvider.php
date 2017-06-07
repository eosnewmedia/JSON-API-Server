<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Provider;

use Enm\JsonApi\Exception\HttpException;
use Enm\JsonApi\Exception\InvalidRequestException;
use Enm\JsonApi\Exception\NotAllowedException;
use Enm\JsonApi\Exception\ResourceNotFoundException;
use Enm\JsonApi\Exception\UnsupportedTypeException;
use Enm\JsonApi\Model\Common\KeyValueCollection;
use Enm\JsonApi\Model\Common\KeyValueCollectionInterface;
use Enm\JsonApi\Model\Resource\JsonResource;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
abstract class AbstractResourceProvider implements ResourceProviderInterface, ResourceProviderRegistryAwareInterface
{
    use ResourceProviderRegistryAwareTrait;

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
    ): RelationshipInterface {
        return $this->findResource($type, $id, $request)->relationships()->get($relationship);
    }

    /**
     * @param string $type
     * @param string $id
     * @return ResourceInterface
     * @throws \InvalidArgumentException
     */
    protected function createResourceObject(string $type, string $id): ResourceInterface
    {
        return new JsonResource($type, $id);
    }

    /**
     * @param array $data
     * @return KeyValueCollectionInterface
     */
    protected function createKeyValueCollection(array $data = []): KeyValueCollectionInterface
    {
        return new KeyValueCollection($data);
    }

    /**
     * @param string $type
     * @param string $id
     * @return ResourceNotFoundException
     */
    protected function createResourceNotFoundException(string $type, string $id): ResourceNotFoundException
    {
        return new ResourceNotFoundException($type, $id);
    }

    /**
     * @param string $type
     * @return UnsupportedTypeException
     */
    protected function createUnsupportedTypeException(string $type): UnsupportedTypeException
    {
        return new UnsupportedTypeException($type);
    }

    /**
     * @param string $message
     * @return InvalidRequestException
     */
    protected function createInvalidRequestException(string $message): InvalidRequestException
    {
        return new InvalidRequestException($message);
    }

    /**
     * @param string $message
     * @return NotAllowedException
     */
    protected function createNotAllowedException(string $message): NotAllowedException
    {
        return new NotAllowedException($message);
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @return HttpException
     */
    protected function createHttpException(int $statusCode, string $message): HttpException
    {
        return new HttpException($statusCode, $message);
    }
}
