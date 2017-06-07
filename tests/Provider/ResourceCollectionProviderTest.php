<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Provider;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use Enm\JsonApi\Model\Resource\ResourceCollectionInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Provider\ResourceCollectionProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class ResourceCollectionProviderTest extends TestCase
{
    public function testFindResource()
    {
        $provider = new ResourceCollectionProvider($this->createResourceCollection());
        /** @var FetchInterface $request */
        $request = $this->createMock(FetchInterface::class);

        $resource = $provider->findResource('tests', 'test-1', $request);

        self::assertInstanceOf(ResourceInterface::class, $resource);
        self::assertEquals('tests', $resource->getType());
        self::assertEquals('test-1', $resource->getId());
    }

    public function testFindResources()
    {
        $provider = new ResourceCollectionProvider($this->createResourceCollection());
        /** @var FetchInterface $request */
        $request = $this->createMock(FetchInterface::class);

        $resources = $provider->findResources('tests', $request);

        self::assertCount(1, $resources);
    }

    public function testFindResourcesEmptyType()
    {
        $provider = new ResourceCollectionProvider($this->createResourceCollection());
        /** @var FetchInterface $request */
        $request = $this->createMock(FetchInterface::class);

        $resources = $provider->findResources('empty', $request);

        self::assertCount(0, $resources);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testExceptionOnCreateResource()
    {
        $provider = new ResourceCollectionProvider($this->createResourceCollection());
        /** @var SaveResourceInterface $request */
        $request = $this->createMock(SaveResourceInterface::class);
        $provider->createResource($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testExceptionOnPatchResource()
    {
        $provider = new ResourceCollectionProvider($this->createResourceCollection());
        /** @var SaveResourceInterface $request */
        $request = $this->createMock(SaveResourceInterface::class);
        $provider->patchResource($request);
    }


    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testExceptionOnDeleteResource()
    {
        $provider = new ResourceCollectionProvider($this->createResourceCollection());
        $provider->deleteResource('tests', 'test-1');
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\ResourceNotFoundException
     */
    public function testExceptionOnResourceNotFound()
    {
        $provider = new ResourceCollectionProvider($this->createEmptyResourceCollection());
        /** @var FetchInterface $request */
        $request = $this->createMock(FetchInterface::class);

        $provider->findResource('tests', 'test-1', $request);
    }

    public function testGetSupportedTypes()
    {
        $provider = new ResourceCollectionProvider($this->createResourceCollection());
        /** @var FetchInterface $request */

        $types = $provider->getSupportedTypes();

        self::assertContains('tests', $types);
        self::assertCount(1, $types);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResourceCollectionInterface
     */
    private function createResourceCollection()
    {
        return $this->createConfiguredMock(
            ResourceCollectionInterface::class,
            [
                'has' => true,
                'get' => $this->createConfiguredMock(
                    ResourceInterface::class,
                    [
                        'getType' => 'tests',
                        'getId' => 'test-1'
                    ]
                ),
                'all' => [
                    $this->createConfiguredMock(
                        ResourceInterface::class,
                        [
                            'getType' => 'tests',
                            'getId' => 'test-1'
                        ]
                    )
                ]
            ]
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResourceCollectionInterface
     */
    private function createEmptyResourceCollection()
    {
        return $this->createConfiguredMock(
            ResourceCollectionInterface::class,
            [
                'has' => false
            ]
        );
    }
}
