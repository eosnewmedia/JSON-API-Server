<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Acceptance;

use Enm\JsonApi\Server\JsonApi;
use Enm\JsonApi\Server\Model\Request\SaveResourceRequest;
use Enm\JsonApi\Server\Model\Request\FetchRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonApiAcceptanceTest extends TestCase
{
    public function testFetchResource()
    {
        $api = new JsonApi(new AcceptanceProvider());
        $response = $api->fetchResource(
            AcceptanceProvider::TYPE,
            'test-1',
            new FetchRequest($this->createRequest())
        );

        self::assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        self::assertEquals(AcceptanceProvider::TYPE, $data['data']['type']);
        self::assertEquals('test-1', $data['data']['id']);
        self::assertEquals('Test', $data['data']['attributes']['name']);
        self::assertEquals(
            AcceptanceProvider::TYPE,
            $data['data']['relationships'][AcceptanceProvider::TO_ONE_RELATION]['data']['type']
        );
        self::assertArrayNotHasKey(
            'attributes',
            $data['data']['relationships'][AcceptanceProvider::TO_ONE_RELATION]['data']
        );
        self::assertArrayNotHasKey('includes', $data);
    }

    public function testFetchResourceWithInclude()
    {
        $api = new JsonApi(new AcceptanceProvider());
        $response = $api->fetchResource(
            AcceptanceProvider::TYPE,
            'test-1',
            new FetchRequest(
                $this->createRequest(
                    [
                        'include' => AcceptanceProvider::TO_ONE_RELATION . ',' . AcceptanceProvider::TO_MANY_RELATION,
                    ]
                )
            )
        );

        $data = json_decode($response->getContent(), true);
        self::assertEquals('Test', $data['data']['attributes']['name']);
        self::assertArrayHasKey('included', $data);
        self::assertArrayHasKey('attributes', $data['included'][0]);
    }

    public function testFetchResourceWithFields()
    {
        $api = new JsonApi(new AcceptanceProvider());
        $response = $api->fetchResource(
            AcceptanceProvider::TYPE,
            'test-1',
            new FetchRequest($this->createRequest(['fields' => [AcceptanceProvider::TYPE => 'name']]))
        );

        $data = json_decode($response->getContent(), true);
        self::assertEquals('Test', $data['data']['attributes']['name']);
    }

    public function testFetchResources()
    {
        $api = new JsonApi(new AcceptanceProvider());
        $response = $api->fetchResources(
            AcceptanceProvider::TYPE,
            new FetchRequest($this->createRequest())
        );

        $data = json_decode($response->getContent(), true);

        self::assertCount(2, $data['data']);
        self::assertEquals(AcceptanceProvider::TYPE, $data['data'][0]['type']);
        self::assertEquals('test-1', $data['data'][0]['id']);
        self::assertEquals('Test', $data['data'][0]['attributes']['name']);
        self::assertArrayNotHasKey(
            'attributes',
            $data['data'][0]['relationships'][AcceptanceProvider::TO_ONE_RELATION]['data']
        );
        self::assertArrayNotHasKey('includes', $data);
    }

    public function testFetchToOneRelationship()
    {
        $api = new JsonApi(new AcceptanceProvider());

        $response = $api->fetchRelationship(
            AcceptanceProvider::TYPE,
            'test-1',
            new FetchRequest($this->createRequest()),
            AcceptanceProvider::TO_ONE_RELATION
        );

        $data = json_decode($response->getContent(), true);

        self::assertEquals(AcceptanceProvider::TYPE, $data['data']['type']);
        self::assertEquals('abc', $data['data']['id']);
        self::assertArrayNotHasKey('attributes', $data['data']);
        self::assertArrayNotHasKey('includes', $data);
    }

    public function testFetchToManyRelationship()
    {
        $api = new JsonApi(new AcceptanceProvider());
        $response = $api->fetchRelationship(
            AcceptanceProvider::TYPE,
            'test-1',
            new FetchRequest($this->createRequest()),
            AcceptanceProvider::TO_MANY_RELATION
        );

        $data = json_decode($response->getContent(), true);

        self::assertCount(1, $data['data']);
        self::assertEquals(AcceptanceProvider::TYPE, $data['data'][0]['type']);
        self::assertEquals('xyz', $data['data'][0]['id']);
        self::assertArrayNotHasKey('attributes', $data['data'][0]);
        self::assertArrayNotHasKey('includes', $data);
    }

    public function testFetchRelatedToOne()
    {
        $api = new JsonApi(new AcceptanceProvider());

        $response = $api->fetchRelated(
            AcceptanceProvider::TYPE,
            'test-1',
            new FetchRequest($this->createRequest()),
            AcceptanceProvider::TO_ONE_RELATION
        );

        $data = json_decode($response->getContent(), true);

        self::assertEquals(AcceptanceProvider::TYPE, $data['data']['type']);
        self::assertEquals('abc', $data['data']['id']);
        self::assertArrayHasKey('attributes', $data['data']);
        self::assertArrayNotHasKey('includes', $data);
    }

    public function testFetchRelatedToMany()
    {
        $api = new JsonApi(new AcceptanceProvider());

        $response = $api->fetchRelated(
            AcceptanceProvider::TYPE,
            'test-1',
            new FetchRequest($this->createRequest()),
            AcceptanceProvider::TO_MANY_RELATION
        );

        $data = json_decode($response->getContent(), true);

        self::assertCount(1, $data['data']);
        self::assertEquals(AcceptanceProvider::TYPE, $data['data'][0]['type']);
        self::assertEquals('xyz', $data['data'][0]['id']);
        self::assertArrayHasKey('attributes', $data['data'][0]);
        self::assertArrayNotHasKey('includes', $data);
    }

    public function testCreateResource()
    {
        $api = new JsonApi(new AcceptanceProvider());

        $response = $api->createResource(
            AcceptanceProvider::TYPE,
            new SaveResourceRequest(
                $this->createRequest(
                    [],
                    [
                        'type' => AcceptanceProvider::TYPE,
                        'attributes' => ['name' => 'Test 56'],
                    ]
                )
            )
        );

        $data = json_decode($response->getContent(), true);
        self::assertEquals('Test 56', $data['data']['attributes']['name']);
    }

    public function testPatchResource()
    {
        $api = new JsonApi(new AcceptanceProvider());

        $response = $api->patchResource(
            AcceptanceProvider::TYPE,
            'test-1',
            new SaveResourceRequest(
                $this->createRequest(
                    [],
                    [
                        'type' => AcceptanceProvider::TYPE,
                        'id' => 'test-1',
                        'attributes' => ['name' => 'Test 56'],
                    ]
                )
            )
        );

        $data = json_decode($response->getContent(), true);
        self::assertEquals('Test 56', $data['data']['attributes']['name']);
    }

    public function testDeleteResource()
    {
        $api = new JsonApi(new AcceptanceProvider());

        $response = $api->deleteResource(AcceptanceProvider::TYPE, 'test-1');

        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @param array $query
     * @param array $data
     *
     * @return Request
     */
    private function createRequest(array $query = [], array $data = []): Request
    {
        $request = new Request(
            $query,
            [],
            [],
            [],
            [],
            [],
            json_encode(['data' => $data])
        );

        $request->headers->set('Content-Type', 'application/vnd.api+json');

        return $request;
    }
}
