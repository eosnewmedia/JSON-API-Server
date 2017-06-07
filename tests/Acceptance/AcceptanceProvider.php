<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Acceptance;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use Enm\JsonApi\Model\Resource\Link\Link;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Provider\AbstractResourceProvider;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class AcceptanceProvider extends AbstractResourceProvider
{
    const TYPE = 'acceptances';
    const TO_ONE_RELATION = 'toOne';
    const TO_MANY_RELATION = 'toMany';

    /**
     * @param string $type
     * @param string $id
     * @param FetchInterface $request
     *
     * @return ResourceInterface
     */
    public function findResource(string $type, string $id, FetchInterface $request): ResourceInterface
    {
        return $this->buildResource($id, $request);
    }

    /**
     * @param string $type
     * @param FetchInterface $request
     *
     * @return array
     */
    public function findResources(string $type, FetchInterface $request): array
    {
        return [
            $this->buildResource('test-1', $request),
            $this->buildResource('test-2', $request),
        ];
    }

    /**
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     */
    public function createResource(SaveResourceInterface $request): ResourceInterface
    {
        $resource = $this->buildResource(
            md5('test'),
            $request->createFetch()
        );

        foreach ($request->resource()->attributes()->all() as $key => $value) {
            $resource->attributes()->set($key, $value);
        }

        $resource->relationships()->removeElement(
            $resource->relationships()->get(self::TO_ONE_RELATION)
        );
        $resource->relationships()->removeElement(
            $resource->relationships()->get(self::TO_MANY_RELATION)
        );

        return $resource;
    }

    /**
     * @param SaveResourceInterface $request
     * @return ResourceInterface
     */
    public function patchResource(SaveResourceInterface $request): ResourceInterface
    {
        $resource = $this->buildResource(
            $request->resource()->getId(),
            $request->createFetch()
        );

        foreach ($request->resource()->attributes()->all() as $key => $value) {
            $resource->attributes()->set($key, $value);
        }

        return $resource;
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return int
     */
    public function deleteResource(string $type, string $id): int
    {
        return 200;
    }

    /**
     * Returns an array of types which are supported by this provider
     *
     * @return array
     */
    public function getSupportedTypes(): array
    {
        return [self::TYPE];
    }

    /**
     * @param string $id
     * @param FetchInterface $fetch
     *
     * @return ResourceInterface
     */
    private function buildResource(string $id, FetchInterface $fetch): ResourceInterface
    {
        $resource = $this->createResourceObject(self::TYPE, $id);

        $resource->attributes()->set('name', 'Test');
        $resource->links()->set(
            new Link('self', 'http://example.org/' . self::TYPE . '/' . $id)
        );

        $resource->relationships()->createToOne(
            self::TO_ONE_RELATION,
            $this->createResourceObject(self::TYPE, 'abc')
        );
        $toOne = $resource->relationships()->get(self::TO_ONE_RELATION);
        if ($fetch->shouldIncludeRelationship(self::TO_ONE_RELATION)) {
            $toOne->related()
                ->get(self::TYPE, 'abc')
                ->attributes()
                ->set('name', 'To One Test');
        }

        $resource->relationships()->set($toOne);


        $resource->relationships()->createToMany(
            self::TO_MANY_RELATION,
            [$this->createResourceObject(self::TYPE, 'xyz')]
        );
        $toMany = $resource->relationships()->get(self::TO_MANY_RELATION);
        if ($fetch->shouldIncludeRelationship(self::TO_MANY_RELATION)) {
            $toMany->related()
                ->get(self::TYPE, 'xyz')
                ->attributes()
                ->set('name', 'To Many Test');
        }

        $resource->relationships()->set($toMany);

        return $resource;
    }
}
