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
        $registry->addProvider($this->createProvider(), 'tests');

        self::assertInstanceOf(ResourceProviderInterface::class, $registry->provider('tests'));
    }

    public function testFindResource()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider(), 'tests');

        $relation = $registry->findResource(
            'tests',
            '1',
            $this->createMock(FetchInterface::class)
        );

        self::assertInstanceOf(ResourceInterface::class, $relation);
    }

    public function testFindRelationship()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider(), 'tests');

        $relation = $registry->findRelationship(
            'tests',
            '1',
            $this->createMock(FetchInterface::class),
            'tests'
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
            'tests',
            $this->createMock(FetchInterface::class)
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testNotAllowedOverwriteResourceProvider()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider(), 'tests');
        $registry->addProvider($this->createProvider(), 'tests');
    }

    public function testCreateResource()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider(), 'tests');

        $resource = $registry->createResource(
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        ['getType' => 'tests']
                    )
                ]
            )
        );

        self::assertInstanceOf(ResourceInterface::class, $resource);
    }

    public function testPathResource()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider(), 'tests');

        $resource = $registry->patchResource(
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        ['getType' => 'tests']
                    )
                ]
            )
        );

        self::assertInstanceOf(ResourceInterface::class, $resource);
    }

    public function testDeleteResource()
    {
        $registry = new ResourceProviderRegistry();
        $registry->addProvider($this->createProvider(), 'tests');

        self::assertEquals(0, $registry->deleteResource('tests', 'test-1'));
    }

    /**
     * @return ResourceProviderInterface
     */
    protected function createProvider(): ResourceProviderInterface
    {
        return $this->createMock(ResourceProviderInterface::class);
    }

    public function testProviderRegistryAware()
    {
        $registry = new ResourceProviderRegistry();
        $provider = new TestProvider();

        $registry->addProvider($provider, 'tests');
        self::assertInstanceOf(ResourceProviderRegistryInterface::class, $provider->getProviderRegistry());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidProviderRegistryAware()
    {
        $provider = new TestProvider();
        $provider->getProviderRegistry();
    }
}
