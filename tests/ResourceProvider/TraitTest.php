<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\ResourceProvider;

use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\Tests\Mock\FetchOnlyMockResourceProvider;
use Enm\JsonApi\Server\Tests\Mock\MockResourceProvider;
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
        $provider = new MockResourceProvider();
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
        $provider = new FetchOnlyMockResourceProvider();
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
}
