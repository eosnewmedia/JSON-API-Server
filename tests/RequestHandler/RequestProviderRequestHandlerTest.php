<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\RequestHandler;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\JsonApiServer;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\RequestHandler\ResourceProviderRequestHandler;
use Enm\JsonApi\Server\Tests\Mock\MockResourceProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RequestProviderRequestHandlerTest extends TestCase
{
    public function testFetchResource()
    {
        $handler = $this->createHandler();
        $handler->addResourceProvider('tests', new MockResourceProvider());

        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'tests']);

        self::assertInstanceOf(DocumentInterface::class, $handler->fetchResource($request));
    }

    public function testFetchResources()
    {
        $handler = $this->createHandler();
        $handler->addResourceProvider('tests', new MockResourceProvider());

        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'tests']);

        self::assertInstanceOf(DocumentInterface::class, $handler->fetchResources($request));
    }

    public function testFetchToOneRelationship()
    {
        $handler = $this->createHandler();
        $handler->addResourceProvider('tests', new MockResourceProvider());

        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            ['type' => 'tests', 'relationship' => 'example']
        );

        $document = $handler->fetchRelationship($request);
        self::assertInstanceOf(DocumentInterface::class, $document);
        self::assertCount(1, $document->data()->all());
        self::assertCount(1, $document->links()->all());
    }

    public function testFetchToManyRelationship()
    {
        $handler = $this->createHandler();
        $handler->addResourceProvider('tests', new MockResourceProvider());

        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            ['type' => 'tests', 'relationship' => 'examples']
        );

        $document = $handler->fetchRelationship($request);
        self::assertInstanceOf(DocumentInterface::class, $document);
        self::assertCount(2, $document->data()->all());
    }


    public function testCreateResource()
    {
        $handler = $this->createHandler();
        $handler->addResourceProvider('tests', new MockResourceProvider());

        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            ['type' => 'tests', 'containsId' => false]
        );

        $document = $handler->saveResource($request);
        self::assertInstanceOf(DocumentInterface::class, $document);
        self::assertCount(1, $document->data()->all());
    }

    public function testPatchResource()
    {
        $handler = $this->createHandler();
        $handler->addResourceProvider('tests', new MockResourceProvider());

        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            ['type' => 'tests', 'containsId' => true]
        );

        $document = $handler->saveResource($request);
        self::assertInstanceOf(DocumentInterface::class, $document);
        self::assertCount(1, $document->data()->all());
    }

    public function testDeleteResource()
    {
        $handler = $this->createHandler();
        $handler->addResourceProvider('tests', new MockResourceProvider());

        /** @var AdvancedJsonApiRequestInterface $request */
        $request = $this->createConfiguredMock(
            AdvancedJsonApiRequestInterface::class,
            ['type' => 'tests']
        );

        $document = $handler->deleteResource($request);
        self::assertInstanceOf(DocumentInterface::class, $document);
        self::assertCount(0, $document->data()->all());
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\UnsupportedTypeException
     */
    public function testFetchResourceUnsupportedType()
    {
        $handler = $this->createHandler();

        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'tests']);

        $handler->fetchResources($request);
    }

    /**
     * @return ResourceProviderRequestHandler
     */
    private function createHandler(): ResourceProviderRequestHandler
    {
        $handler = new ResourceProviderRequestHandler();
        // JsonApiAware ...
        new JsonApiServer($handler);

        return $handler;
    }
}
