<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Model\Request;

use Enm\JsonApi\Serializer\Deserializer;
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
            ),
            new Deserializer()
        );

        self::assertEquals('test-1', $request->document()->data()->first()->id());
        self::assertInstanceOf(FetchRequestInterface::class, $request->fetch());
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidRequestInvalidType()
    {
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
            new Deserializer()
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidRequestInvalidId()
    {
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
            new Deserializer()
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
}
