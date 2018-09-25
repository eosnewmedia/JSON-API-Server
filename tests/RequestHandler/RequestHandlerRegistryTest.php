<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\RequestHandler;

use Enm\JsonApi\Model\Request\RequestInterface;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RequestHandlerRegistryTest extends TestCase
{
    public function testFetchResource(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->fetchResource($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testFetchResources(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->fetchResources($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }


    public function testFetchRelationship(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->fetchRelationship($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCreateResource(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->createResource($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPatchResource(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->patchResource($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testDeleteResource(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->deleteResource($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testAddRelatedResources(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->addRelatedResources($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testReplaceRelatedResources(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->replaceRelatedResources($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testRemoveRelatedResources(): void
    {
        try {
            /** @var RequestInterface $request */
            $request = $this->createMock(RequestInterface::class);

            $this->createRequestHandler()->removeRelatedResources($request);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\UnsupportedTypeException
     */
    public function testEmptyRegistry(): void
    {
        /** @var RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);
        (new RequestHandlerRegistry())->removeRelatedResources($request);
        $this->fail('No exception was thrown.');
    }

    /**
     * @return RequestHandlerRegistry
     */
    protected function createRequestHandler(): RequestHandlerRegistry
    {
        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $registry = new RequestHandlerRegistry();
        $registry->addHandler('', $handler);
        return $registry;
    }
}
