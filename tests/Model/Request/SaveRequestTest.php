<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Model\Request;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Model\Resource\ResourceCollectionInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Serializer\Deserializer;
use Enm\JsonApi\Serializer\DocumentDeserializerInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequest;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class SaveRequestTest extends TestCase
{
    public function testSaveRequest()
    {
        /** @var DocumentDeserializerInterface $deserializer */
        $deserializer = $this->createDeserializer();

        $request = new SaveRequest(
            $this->createHttpRequest(
                'http://example.com/tests/test-1',
                [
                    'type' => 'tests',
                    'id' => 'test-1',
                    'attributes' => [
                        'title' => 'Lorem Ipsum'
                    ]
                ]
            ), $deserializer
        );

        self::assertInstanceOf(FetchRequestInterface::class, $request->fetch());
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidRequestInvalidType()
    {
        /** @var DocumentDeserializerInterface $deserializer */
        $deserializer = $this->createDeserializer();

        new SaveRequest(
            $this->createHttpRequest(
                'http://example.com/test/test-1',
                [
                    'type' => 'tests',
                    'id' => 'test-1',
                    'attributes' => [
                        'title' => 'Lorem Ipsum'
                    ]
                ]
            ),
            $deserializer
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidRequestInvalidId()
    {
        /** @var DocumentDeserializerInterface $deserializer */
        $deserializer = $this->createDeserializer();

        new SaveRequest(
            $this->createHttpRequest(
                'http://example.com/tests/test-2',
                [
                    'type' => 'tests',
                    'id' => 'test-1',
                    'attributes' => [
                        'title' => 'Lorem Ipsum'
                    ]
                ]
            ),
            $deserializer
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidRequestEmptyData()
    {
        new SaveRequest(
            $this->createHttpRequest('http://example.com/tests/test-1'),
            new Deserializer()
        );
    }

    /**
     * @param string $uriString
     * @param array|null $content
     * @return RequestInterface
     */
    private function createHttpRequest(string $uriString, array $content = null): RequestInterface
    {
        return new Request(
            'GET',
            new Uri($uriString),
            [
                'Content-Type' => 'application/vnd.api+json'
            ],
            is_array($content) ? json_encode(['data' => $content]) : null
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
                                        'type' => 'tests',
                                        'id' => 'test-1',
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
