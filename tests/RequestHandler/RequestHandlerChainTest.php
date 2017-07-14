<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\RequestHandler;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\JsonApiServer;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRelationshipRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerChain;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerRegistry;
use Enm\JsonApi\Server\RequestHandler\ResourceProviderRequestHandler;
use Enm\JsonApi\Server\Tests\Mock\FetchOnlyMockRequestHandler;
use Enm\JsonApi\Server\Tests\Mock\FetchOnlyMockResourceProvider;
use Enm\JsonApi\Server\Tests\Mock\MockRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RequestHandlerChainTest extends TestCase
{

    public function testFetchResourceFirstHandler()
    {
        $chain = $this->createChain();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'tests']);

        self::assertInstanceOf(DocumentInterface::class, $chain->fetchResource($request));
    }

    public function testFetchResourceSecondHandler()
    {
        $chain = $this->createChain();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'fetchOnlyTests']);

        self::assertInstanceOf(DocumentInterface::class, $chain->fetchResource($request));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\UnsupportedTypeException
     */
    public function testFetchResourceNoHandler()
    {
        $chain = $this->createChain();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'invalidType']);
        $chain->fetchResource($request);
    }

    public function testFetchResources()
    {
        $chain = $this->createChain();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'tests']);

        self::assertInstanceOf(DocumentInterface::class, $chain->fetchResources($request));
    }

    public function testFetchRelationship()
    {
        $chain = $this->createChain();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            ['type' => 'tests', 'relationship' => 'example']
        );

        self::assertInstanceOf(DocumentInterface::class, $chain->fetchRelationship($request));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testFetchRelationshipNotPossible()
    {
        $chain = $this->createChain();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            ['type' => 'fetchOnlyTests', 'relationship' => 'example']
        );

        $chain->fetchRelationship($request);
    }

    public function testSaveResource()
    {
        $chain = $this->createChain();
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(SaveRequestInterface::class, ['type' => 'tests']);

        self::assertInstanceOf(DocumentInterface::class, $chain->saveResource($request));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testCreateResourceNotAllowed()
    {
        $chain = $this->createChain();
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(SaveRequestInterface::class, ['type' => 'fetchOnlyTests']);

        $chain->saveResource($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testCreateResourceNotAllowedWithProvider()
    {
        $chain = $this->createChain();
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(SaveRequestInterface::class, ['type' => 'fetchOnlyExamples']);

        $chain->saveResource($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testPatchResourceNotAllowed()
    {
        $chain = $this->createChain();
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            ['type' => 'fetchOnlyTests', 'containsId' => true]
        );

        $chain->saveResource($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testPatchResourceNotAllowedWithProvider()
    {
        $chain = $this->createChain();
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            ['type' => 'fetchOnlyExamples', 'containsId' => true]
        );

        $chain->saveResource($request);
    }

    public function testDeleteResource()
    {
        $chain = $this->createChain();
        /** @var AdvancedJsonApiRequestInterface $request */
        $request = $this->createConfiguredMock(AdvancedJsonApiRequestInterface::class, ['type' => 'tests']);

        self::assertInstanceOf(DocumentInterface::class, $chain->deleteResource($request));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testDeleteResourceNotAllowed()
    {
        $chain = $this->createChain();
        /** @var AdvancedJsonApiRequestInterface $request */
        $request = $this->createConfiguredMock(AdvancedJsonApiRequestInterface::class, ['type' => 'fetchOnlyTests']);

        $chain->deleteResource($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testDeleteResourceNotAllowedWithProvider()
    {
        $chain = $this->createChain();
        /** @var AdvancedJsonApiRequestInterface $request */
        $request = $this->createConfiguredMock(AdvancedJsonApiRequestInterface::class, ['type' => 'fetchOnlyExamples']);

        $chain->deleteResource($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testModifyRelationshipNotAllowed()
    {
        $chain = $this->createChain();
        /** @var SaveRelationshipRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRelationshipRequestInterface::class,
            ['type' => 'fetchOnlyTests']
        );

        $chain->saveRelationship($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testModifyRelationshipNotAllowedWithProvider()
    {
        $chain = $this->createChain();
        /** @var SaveRelationshipRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRelationshipRequestInterface::class,
            ['type' => 'fetchOnlyExamples']
        );

        $chain->saveRelationship($request);
    }

    /**
     * @return RequestHandlerChain
     */
    private function createChain(): RequestHandlerChain
    {
        $chain = new RequestHandlerChain();
        new JsonApiServer($chain);

        $registry = new RequestHandlerRegistry();
        $registry->addRequestHandler('tests', new MockRequestHandler());
        $registry->addRequestHandler('fetchOnlyTests', new FetchOnlyMockRequestHandler());
        $providerHandler = new ResourceProviderRequestHandler();
        $providerHandler->addResourceProvider('fetchOnlyExamples', new FetchOnlyMockResourceProvider());
        $registry->addRequestHandler('fetchOnlyExamples', $providerHandler);

        $chain->addRequestHandler($registry);

        return $chain;
    }
}
