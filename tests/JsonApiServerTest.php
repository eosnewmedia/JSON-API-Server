<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests;

use Enm\JsonApi\Server\JsonApiServer;
use Enm\JsonApi\Server\Tests\Mock\MockRequestHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonApiServerTest extends TestCase
{

    public function testFetchResource()
    {
        $server = new JsonApiServer(new MockRequestHandler(), 'api');

        $response = $server->handleFetch(
            $this->createHttpRequest('GET', 'http://example.com/api/tests/test-1')
        );

        self::assertEquals(200, $response->getStatusCode());

        self::assertArraySubset(
            [
                'data' => [
                    'type' => 'tests',
                    'id' => 'test-1',
                    'attributes' => [
                        'title' => 'Test'
                    ]
                ]
            ],
            json_decode((string)$response->getBody(), true)
        );
    }

    public function testFetchResourceNotFound()
    {
        $server = new JsonApiServer(new MockRequestHandler(true));

        $response = $server->handleFetch(
            $this->createHttpRequest('GET', 'http://example.com/tests/test-1')
        );

        self::assertEquals(404, $response->getStatusCode());
        self::assertCount(1, json_decode((string)$response->getBody(), true)['errors']);
    }

    public function testCreateResource()
    {
        $server = new JsonApiServer(new MockRequestHandler());

        $response = $server->handleSave(
            $this->createHttpRequest(
                'POST',
                'http://example.com/tests',
                [
                    'type' => 'tests',
                    'id' => 'test-2'
                ]
            )
        );
        $data = json_decode((string)$response->getBody(), true)['data'];

        self::assertEquals('tests', $data['type']);
        self::assertEquals('test-2', $data['id']);
    }

    public function testCreateResourceInvalidUri()
    {
        $server = new JsonApiServer(new MockRequestHandler());

        $response = $server->handleSave(
            $this->createHttpRequest(
                'POST',
                'http://example.com/tests/tests-2',
                [
                    'type' => 'tests',
                    'id' => 'test-2'
                ]
            )
        );

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testPatchResource()
    {
        $server = new JsonApiServer(new MockRequestHandler());

        $response = $server->handleSave(
            $this->createHttpRequest(
                'PATCH',
                'http://example.com/tests/test-2',
                [
                    'type' => 'tests',
                    'id' => 'test-2'
                ]
            )
        );
        $data = json_decode((string)$response->getBody(), true)['data'];

        self::assertEquals('tests', $data['type']);
        self::assertEquals('test-2', $data['id']);
    }

    public function testPatchResourceInvalidUri()
    {
        $server = new JsonApiServer(new MockRequestHandler());

        $response = $server->handleSave(
            $this->createHttpRequest(
                'PATCH',
                'http://example.com/tests',
                [
                    'type' => 'tests',
                    'id' => 'test-2'
                ]
            )
        );

        self::assertEquals(400, $response->getStatusCode());
    }


    public function testDeleteResource()
    {
        $server = new JsonApiServer(new MockRequestHandler());

        $response = $server->handleDelete(
            $this->createHttpRequest('DELETE', 'http://example.com/tests/test-1')
        );

        self::assertEquals(204, $response->getStatusCode());
        self::assertEquals('', (string)$response->getBody());
    }

    public function testDeleteResourceWithoutId()
    {
        $server = new JsonApiServer(new MockRequestHandler());

        $response = $server->handleDelete(
            $this->createHttpRequest('DELETE', 'http://example.com/tests')
        );

        self::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @param string $method
     * @param string $uriString
     * @param array|null $content
     * @return RequestInterface
     */
    private function createHttpRequest(string $method, string $uriString, array $content = null): RequestInterface
    {
        return new Request(
            $method,
            new Uri($uriString),
            [
                'Content-Type' => 'application/vnd.api+json'
            ],
            is_array($content) ? json_encode(['data' => $content]) : null
        );
    }
}
