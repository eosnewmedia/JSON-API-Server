<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Model\Request;

use Enm\JsonApi\Server\Model\Request\FetchRequest;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class FetchRequestTest extends TestCase
{
    public function testRelationshipRequest()
    {
        $request = new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/api/tests/test-1/relationship/example'
            ),
            true,
            '/api'
        );

        self::assertEquals('tests', $request->type());
        self::assertEquals('test-1', $request->id());
        self::assertEquals('example', $request->relationship());
        self::assertFalse($request->requestedResourceBody());
    }

    public function testRelatedRequest()
    {
        $request = new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/api/tests/test-1/example'
            ),
            true,
            '/api'
        );

        self::assertEquals('tests', $request->type());
        self::assertEquals('test-1', $request->id());
        self::assertEquals('example', $request->relationship());
        self::assertTrue($request->requestedResourceBody());
    }

    public function testSubRequest()
    {
        $request = new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/api/tests?include=example&fields[tests]=abc&filter[test]=ok&page[offset]=0&sort=abc'
            ),
            true,
            '/api'
        );

        self::assertTrue($request->isMainRequest());

        $subRequest = $request->subRequest('example');

        self::assertFalse($subRequest->isMainRequest());
        self::assertTrue($subRequest->filter()->isEmpty());
        self::assertTrue($subRequest->pagination()->isEmpty());
        self::assertTrue($subRequest->sorting()->isEmpty());
        self::assertTrue($subRequest->requestedField('tests', 'abc'));
        self::assertFalse($subRequest->requestedField('tests', 'def'));
        self::assertTrue($subRequest->requestedRelationships());
        self::assertFalse($subRequest->subRequest('test')->requestedRelationships());
    }

    public function testSubRequestKeepFilter()
    {
        $request = new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/api/tests?include=example&fields[tests]=abc&filter[test]=ok&page[offset]=0&sort=abc'
            ),
            true,
            '/api'
        );

        self::assertTrue($request->isMainRequest());

        $subRequest = $request->subRequest('example', true);

        self::assertFalse($subRequest->isMainRequest());
        self::assertEquals('ok', $subRequest->filter()->getOptional('test'));
        self::assertCount(0, $subRequest->pagination()->all());
        self::assertCount(0, $subRequest->sorting()->all());
        self::assertTrue($subRequest->requestedField('tests', 'abc'));
        self::assertFalse($subRequest->requestedField('tests', 'def'));
    }

    public function testIncludes()
    {
        $request = new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/api/tests/test-1?include=example,example.relationA.subA'
            ),
            true,
            '/api'
        );

        self::assertEquals('tests', $request->type());
        self::assertEquals('test-1', $request->id());

        $exampleRequest = $request->subRequest('example');
        self::assertTrue($exampleRequest->requestedResourceBody());
        self::assertFalse(
            $exampleRequest->subRequest('relationA')->requestedResourceBody()
        );
        self::assertTrue(
            $exampleRequest->subRequest('relationA')->subRequest('subA')->requestedResourceBody()
        );
    }

    public function testFields()
    {
        $request = new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/tests?fields[tests]=example&fields[examples]='
            )
        );

        self::assertTrue($request->requestedField('tests', 'example'));
        self::assertFalse($request->requestedField('tests', 'example2'));
        self::assertTrue($request->requestedField('dummies', 'dummy'));
        self::assertFalse($request->requestedField('examples', 'example'));
    }

    public function testPagination()
    {
        $request = new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/tests?page[offset]=0'
            )
        );

        self::assertEquals(0, $request->pagination()->getRequired('offset'));
    }

    public function testSorting()
    {
        $request = new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/tests?sort=example,-test'
            )
        );

        self::assertArraySubset(
            [
                'example' => FetchRequestInterface::ORDER_ASC,
                'test' => FetchRequestInterface::ORDER_DESC,
            ],
            $request->sorting()->all()
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidPagination()
    {
        new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/tests?page=0'
            )
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidSorting()
    {
        new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/tests?sort[]=example&sort[]=-test'
            )
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidInclude()
    {
        new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/tests?include[]=test'
            )
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidFields()
    {
        new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/tests?fields=test,test2'
            )
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidFilter()
    {
        new FetchRequest(
            $this->createHttpRequest(
                'http://example.com/tests?filter=test'
            )
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\UnsupportedMediaTypeException
     */
    public function testInvalidContentType()
    {
        /** @var RequestInterface $httpRequest */
        $httpRequest = $this->createMock(RequestInterface::class);
        new FetchRequest($httpRequest);
    }

    /**
     * @param string $uriString
     * @return RequestInterface
     */
    private function createHttpRequest(string $uriString): RequestInterface
    {
        return new Request(
            'GET',
            new Uri($uriString),
            [
                'Content-Type' => 'application/vnd.api+json'
            ]
        );
    }
}
