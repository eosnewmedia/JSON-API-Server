<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Model\Request;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Resource\ResourceCollectionInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Serializer\DocumentDeserializerInterface;
use Enm\JsonApi\Server\Model\Request\RelationshipModificationRequest;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RelationshipModificationRequestTest extends TestCase
{
    public function testRequest()
    {
        $deserializer = $this->createDeserializer();
        $request = new RelationshipModificationRequest(
            $this->createHttpRequest(
                'http://example.com/api/tests/test-1/relationship/abc',
                []
            ),
            $deserializer,
            '/api'
        );

        self::assertTrue($request->isMainRequestRelationshipRequest());
        self::assertTrue($request->onlyIdentifiers());
    }

    public function testSubFetchRequest()
    {
        $deserializer = $this->createDeserializer();
        $request = new RelationshipModificationRequest(
            $this->createHttpRequest(
                'http://example.com/api/tests/test-1/relationship/abc'
            ),
            $deserializer,
            '/api'
        );

        $subRequest = $request->fetch();

        self::assertEquals('GET', $subRequest->originalHttpRequest()->getMethod());
        self::assertEquals('tests', $subRequest->type());
        self::assertEquals('test-1', $subRequest->id());
        self::assertTrue($subRequest->isMainRequestRelationshipRequest());
        self::assertTrue($subRequest->onlyIdentifiers());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidSubFetchRequest()
    {
        $deserializer = $this->createDeserializer();
        $request = new RelationshipModificationRequest(
            $this->createHttpRequest(
                'http://example.com/api/tests/test-1/relationship/abc'
            ),
            $deserializer,
            '/api'
        );

        $request->fetch('example-1');
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testRequestInvalidRelationship()
    {
        $deserializer = $this->createDeserializer();
        new RelationshipModificationRequest(
            $this->createHttpRequest('http://example.com/tests/test-1/abc'),
            $deserializer
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testRequestMissingRelationship()
    {
        $deserializer = $this->createDeserializer();
        new RelationshipModificationRequest(
            $this->createHttpRequest('http://example.com/tests/test-1'),
            $deserializer
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testRequestInvalidContent()
    {
        $deserializer = $this->createDeserializer();
        new RelationshipModificationRequest(
            $this->createHttpRequest(
                'http://example.com/tests/test-1/relationship/example',
                'invalid json'
            ),
            $deserializer
        );
    }

    /**
     * @param string $uriString
     * @param array|null|string $content
     * @return RequestInterface
     */
    private function createHttpRequest(string $uriString, $content = null): RequestInterface
    {
        return new Request(
            'GET',
            new Uri($uriString),
            [
                'Content-Type' => 'application/vnd.api+json'
            ],
            is_array($content) ? json_encode(['data' => $content]) : $content
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DocumentDeserializerInterface
     */
    private function createDeserializer(): \PHPUnit_Framework_MockObject_MockObject
    {
        $deserializer = $this->createConfiguredMock(
            DocumentDeserializerInterface::class,
            [
                'deserializeDocument' => $this->createConfiguredMock(
                    DocumentInterface::class,
                    [
                        'data' => $this->createConfiguredMock(
                            ResourceCollectionInterface::class,
                            [
                                'isEmpty' => false,
                                'first' => $this->createConfiguredMock(
                                    ResourceInterface::class,
                                    [
                                        'type' => 'examples',
                                        'id' => 'example-1',
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ]
        );

        return $deserializer;
    }
}
