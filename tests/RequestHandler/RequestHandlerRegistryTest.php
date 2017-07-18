<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\RequestHandler;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\JsonApiServer;
use Enm\JsonApi\Server\Model\Request\FetchRequest;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerRegistry;
use Enm\JsonApi\Server\Tests\Mock\MockRequestHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RequestHandlerRegistryTest extends TestCase
{

    public function testFetchResource()
    {
        $registry = new RequestHandlerRegistry();
        // for json api aware...
        new JsonApiServer($registry);

        $registry->addRequestHandler('tests', new MockRequestHandler());

        $document = $registry->fetchResource(
            new FetchRequest(
                new Request(
                    'GET',
                    new Uri('http://example.com/tests/test-1'),
                    [
                        'Content-Type' => 'application/vnd.api+json'
                    ]
                )
            )
        );

        self::assertCount(1, $document->data()->all());
    }

    public function testFetchResources()
    {
        $registry = new RequestHandlerRegistry();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'tests']);

        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $registry->addRequestHandler('tests', $handler);

        self::assertInstanceOf(DocumentInterface::class, $registry->fetchResources($request));
    }

    public function testFetchRelationship()
    {
        $registry = new RequestHandlerRegistry();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'tests']);

        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $registry->addRequestHandler('tests', $handler);

        self::assertInstanceOf(DocumentInterface::class, $registry->fetchRelationship($request));
    }

    public function testSaveResource()
    {
        $registry = new RequestHandlerRegistry();
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(SaveRequestInterface::class, ['type' => 'tests']);

        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $registry->addRequestHandler('tests', $handler);

        self::assertInstanceOf(DocumentInterface::class, $registry->saveResource($request));
    }

    public function testDeleteResource()
    {
        $registry = new RequestHandlerRegistry();
        /** @var AdvancedJsonApiRequestInterface $request */
        $request = $this->createConfiguredMock(AdvancedJsonApiRequestInterface::class, ['type' => 'tests']);

        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $registry->addRequestHandler('tests', $handler);

        self::assertInstanceOf(DocumentInterface::class, $registry->deleteResource($request));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\UnsupportedTypeException
     */
    public function testFetchResourceUnsupportedType()
    {
        $registry = new RequestHandlerRegistry();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(FetchRequestInterface::class, ['type' => 'tests']);
        $registry->fetchResource($request);
    }
}
