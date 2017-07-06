<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Model\Request;

use Enm\JsonApi\Server\Model\Request\SimpleMainRequestProvider;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class SimpleRequestTest extends TestCase
{
    public function testSimpleRequest()
    {
        $request = new SimpleMainRequestProvider($this->createHttpRequest('http://example.com/tests/test-1'));

        self::assertEquals('tests', $request->type());
        self::assertEquals('test-1', $request->id());
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
