<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\RequestHandler;

use Enm\JsonApi\Server\JsonApiServer;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\Tests\Mock\FetchOnlyMockRequestHandler;
use Enm\JsonApi\Server\Tests\Mock\MockRequestHandler;
use Enm\JsonApi\Server\Tests\Mock\SeparatedRelationshipSaveHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class TraitTest extends TestCase
{
    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidModificationRequest()
    {
        $provider = new SeparatedRelationshipSaveHandler();
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getMethod' => 'PUT'
                    ]
                )
            ]
        );

        $provider->modifyRelationship($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testModificationRequestNotAllowed()
    {
        $provider = new FetchOnlyMockRequestHandler();
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getMethod' => 'GET'
                    ]
                )
            ]
        );

        $provider->modifyRelationship($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testModificationRequestNotPossible()
    {
        $provider = new MockRequestHandler();
        new JsonApiServer($provider);
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getMethod' => 'GET'
                    ]
                )
            ]
        );

        $provider->modifyRelationship($request);
    }

    public function testAddRelatedResources()
    {
        $provider = new SeparatedRelationshipSaveHandler();
        new JsonApiServer($provider);
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getMethod' => 'POST',
                        'getBody' => '{"data": []}'
                    ]
                )
            ]
        );

        self::assertEquals(200, $provider->modifyRelationship($request)->httpStatus());
    }

    public function testReplaceRelatedResources()
    {
        $provider = new SeparatedRelationshipSaveHandler();
        new JsonApiServer($provider);
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getMethod' => 'PATCH',
                        'getBody' => '{"data": []}'
                    ]
                )
            ]
        );

        self::assertEquals(200, $provider->modifyRelationship($request)->httpStatus());
    }

    public function testRemoveRelatedResources()
    {
        $provider = new SeparatedRelationshipSaveHandler();
        new JsonApiServer($provider);
        /** @var SaveRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getMethod' => 'DELETE',
                        'getBody' => '{"data": []}'
                    ]
                )
            ]
        );

        self::assertEquals(200, $provider->modifyRelationship($request)->httpStatus());
    }
}
