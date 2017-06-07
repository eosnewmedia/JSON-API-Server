<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Provider;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Provider\ResourceProviderInterface;
use Enm\JsonApi\Server\Provider\ResourceProviderRegistry;
use Enm\JsonApi\Server\Provider\ResourceProviderRegistryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class ResourceProviderRegistryTest extends TestCase
{
    public function testAddResourceProvider()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider());

        self::assertTrue(true);
        self::assertArraySubset(['test'], $registry->getSupportedTypes());
    }

    public function testFindResource()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider());

        $relation = $registry->findResource(
            'test',
            '1',
            $this->createMock(FetchInterface::class)
        );

        self::assertInstanceOf(ResourceInterface::class, $relation);
    }

    public function testFindRelationship()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider());

        $relation = $registry->findRelationship(
            'test',
            '1',
            $this->createMock(FetchInterface::class),
            'test'
        );

        self::assertInstanceOf(RelationshipInterface::class, $relation);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\UnsupportedTypeException
     */
    public function testUnsupportedType()
    {
        $registry = new ResourceProviderRegistry();
        $registry->findResources(
            'test',
            $this->createMock(FetchInterface::class)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotAllowedOverwriteResourceProvider()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider());
        $registry->addProvider($this->createProvider());
    }

    public function testCreateResource()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider());

        $resource = $registry->createResource(
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        ['getType' => 'test']
                    )
                ]
            )
        );

        self::assertInstanceOf(ResourceInterface::class, $resource);
    }

    public function testPathResource()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider());

        $resource = $registry->patchResource(
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        ['getType' => 'test']
                    )
                ]
            )
        );

        self::assertInstanceOf(ResourceInterface::class, $resource);
    }

    public function testDeleteResource()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider());

        self::assertEquals(0,$registry->deleteResource('test', 'test-1'));
    }

    /**
     * @return ResourceProviderInterface
     */
    protected function createProvider()
    {
        return $this->createConfiguredMock(
            ResourceProviderInterface::class,
            [
                'getSupportedTypes' => ['test'],
            ]
        );
    }

    public function testProviderRegistryAware()
    {
        $registry = new ResourceProviderRegistry();
        $provider = new TestProvider();

        $registry->addProvider($provider);
        self::assertInstanceOf(ResourceProviderRegistryInterface::class, $provider->providerRegistry());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidProviderRegistryAware()
    {
        $provider = new TestProvider();
        $provider->providerRegistry();
    }
}
