<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Model\Request;

use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequest;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class AdvancedJsonApiRequestTest extends TestCase
{
    public function testSimpleRequest()
    {
        $request = new AdvancedJsonApiRequest($this->createHttpRequest('http://example.com/tests/test-1'));

        self::assertEquals('tests', $request->type());
        self::assertEquals('test-1', $request->id());
        self::assertFalse($request->isMainRequestRelationshipRequest());
        self::assertFalse($request->onlyIdentifiers());
    }

    public function testRelationshipRequest()
    {
        $request = new AdvancedJsonApiRequest($this->createHttpRequest('http://example.com/tests/test-1/relationship/abc'));

        self::assertEquals('tests', $request->type());
        self::assertEquals('test-1', $request->id());
        self::assertTrue($request->isMainRequestRelationshipRequest());
        self::assertEquals('abc', $request->relationship());
        self::assertTrue($request->onlyIdentifiers());
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidRelationshipRequest()
    {
        new AdvancedJsonApiRequest($this->createHttpRequest('http://example.com/tests/test-1/relationships/abc'));
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
