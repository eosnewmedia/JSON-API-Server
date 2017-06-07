<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests;

use Enm\JsonApi\Model\Common\KeyValueCollection;
use Enm\JsonApi\Server\JsonApi;
use Enm\JsonApi\Model\Error\ErrorInterface;
use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipCollectionInterface;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Enm\JsonApi\Model\Resource\Relationship\ToManyRelationship;
use Enm\JsonApi\Model\Resource\Relationship\ToOneRelationship;
use Enm\JsonApi\Model\Resource\ResourceCollectionInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\Provider\ResourceProviderInterface;
use Enm\JsonApi\Serializer\DocumentSerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonApiTest extends TestCase
{
    public function testFetchResource()
    {
        $jsonApi = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $jsonApi->setSerializer($this->createDocumentSerializerForSingleFetch());
        $jsonApi->setEventDispatcher($this->createMock(EventDispatcherInterface::class));

        $result = json_decode(
            $jsonApi->fetchResource(
                'test', '1', $this->createMock(FetchInterface::class)
            )->getContent()
            , true
        );

        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('type', $result['data']);
        self::assertArrayHasKey('id', $result['data']);
        self::assertArrayHasKey('attributes', $result['data']);
    }

    public function testFetchResources()
    {
        $jsonApi = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $jsonApi->setSerializer($this->createDocumentSerializerForCollectionFetch());

        $result = json_decode(
            $jsonApi->fetchResources(
                'test', $this->createMock(FetchInterface::class)
            )->getContent()
            , true
        );

        self::assertArrayHasKey('data', $result);
        self::assertCount(3, $result['data']);
        self::assertArrayHasKey('type', $result['data'][0]);
        self::assertArrayHasKey('id', $result['data'][0]);
        self::assertArrayHasKey('attributes', $result['data'][0]);
    }

    public function testFetchAndNormalizeResources()
    {
        $jsonApi = new JsonApi(
            $this->createConfiguredMock(
                ResourceProviderInterface::class,
                [
                    'findResources' => [
                        $this->createConfiguredMock(
                            ResourceInterface::class,
                            [
                                'getType' => 'test',
                                'getId' => 'test-1',
                                'attributes' => new KeyValueCollection(['test' => 'test']),
                            ]
                        ),
                    ],
                ]
            )
        );

        $result = json_decode(
            $jsonApi->fetchResources(
                'test',
                $this->createConfiguredMock(
                    FetchInterface::class,
                    ['shouldContainAttribute' => false]
                )
            )->getContent()
            , true
        );

        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('type', $result['data'][0]);
        self::assertArrayHasKey('id', $result['data'][0]);
        self::assertArrayNotHasKey('attributes', $result['data'][0]);
    }

    public function testFetchResourcesWithIncludes()
    {
        $jsonApi = new JsonApi(
            $this->createConfiguredMock(
                ResourceProviderInterface::class,
                [
                    'findResources' => [
                        $this->createConfiguredMock(
                            ResourceInterface::class,
                            [
                                'getType' => 'test',
                                'getId' => 'test-1',
                                'relationships' => $this->createConfiguredMock(
                                    RelationshipCollectionInterface::class,
                                    [
                                        'all' => [
                                            $this->createConfiguredMock(
                                                RelationshipInterface::class,
                                                [
                                                    'getType' => RelationshipInterface::TYPE_ONE,
                                                    'getName' => 'relationA',
                                                    'related' => $this->createConfiguredMock(
                                                        ResourceCollectionInterface::class,
                                                        [
                                                            'all' => [
                                                                $this->createConfiguredMock(ResourceInterface::class,
                                                                    [
                                                                        'getType' => 'related',
                                                                        'getId' => 'related-1',
                                                                    ]
                                                                ),
                                                            ],
                                                        ]
                                                    ),
                                                ]
                                            ),
                                        ],
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]
            )
        );

        $result = json_decode(
            $jsonApi->fetchResources(
                'test',
                $this->createConfiguredMock(
                    FetchInterface::class,
                    ['shouldIncludeRelationship' => true]
                )
            )->getContent()
            , true
        );

        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('included', $result);
        self::assertEquals('related', $result['included'][0]['type']);
        self::assertEquals('related-1', $result['included'][0]['id']);
    }

    public function testFetchToOneRelationship()
    {
        $jsonApi = new JsonApi(
            $this->createConfiguredMock(
                ResourceProviderInterface::class,
                [
                    'findRelationship' => new ToOneRelationship('empty-to-one'),
                ]
            )
        );

        $response = $jsonApi->fetchRelationship(
            'test', '1', $this->createMock(FetchInterface::class), 'empty-to-one'
        );

        self::assertEquals(
            null, json_decode($response->getContent(), true)['data']
        );
    }

    public function testFetchToManyRelationship()
    {
        $jsonApi = new JsonApi(
            $this->createConfiguredMock(
                ResourceProviderInterface::class,
                [
                    'findRelationship' => new ToManyRelationship('empty-to-many'),
                ]
            )
        );

        $response = $jsonApi->fetchRelationship(
            'test', '1', $this->createMock(FetchInterface::class), 'empty-to-many'
        );

        self::assertCount(
            0, json_decode($response->getContent(), true)['data']
        );
    }

    public function testFetchRelatedToMany()
    {
        $jsonApi = new JsonApi(
            $this->createConfiguredMock(
                ResourceProviderInterface::class,
                [
                    'findRelationship' => new ToManyRelationship('empty-to-many'),
                ]
            )
        );

        $response = $jsonApi->fetchRelated(
            'test', '1', $this->createMock(FetchInterface::class), 'empty-to-many'
        );

        self::assertCount(
            0, json_decode($response->getContent(), true)['data']
        );
    }

    public function testFetchRelatedToOne()
    {
        $jsonApi = new JsonApi(
            $this->createConfiguredMock(
                ResourceProviderInterface::class,
                [
                    'findRelationship' => new ToOneRelationship('empty-to-one'),
                ]
            )
        );

        $response = $jsonApi->fetchRelated(
            'test', '1', $this->createMock(FetchInterface::class), 'empty-to-one'
        );

        self::assertNull(json_decode($response->getContent(), true)['data']);
    }


    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testFetchInvalidRelationship()
    {
        $jsonApi = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $jsonApi->fetchRelationship(
            'test', '1', $this->createMock(FetchInterface::class), 'parent'
        );
    }

    public function testCreateResource()
    {
        $api = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $response = $api->createResource(
            'test',
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        [
                            'getType' => 'test'
                        ]
                    )
                ]
            )
        );

        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testCreateResourceInvalidType()
    {
        $api = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $api->createResource(
            'test',
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        ['getType' => 'invalid']
                    )
                ]
            )
        );
    }

    public function testPatchResource()
    {
        $api = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $response = $api->patchResource('test', 'test-1',
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'containsId' => true,
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        [
                            'getType' => 'test',
                            'getId' => 'test-1'
                        ]
                    )
                ]
            )
        );

        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testPatchResourceInvalidType()
    {
        $api = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $api->patchResource(
            'test',
            'test-1',
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'containsId' => true,
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        [
                            'getType' => 'invalid',
                            'getId' => 'test-1'
                        ]
                    )
                ]
            )
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testPatchResourceInvalidId()
    {
        $api = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $api->patchResource(
            'test',
            'test-1',
            $this->createConfiguredMock(
                SaveResourceInterface::class,
                [
                    'resource' => $this->createConfiguredMock(
                        ResourceInterface::class,
                        [
                            'getType' => 'test',
                            'getId' => 'invalid'
                        ]
                    )
                ]
            )
        );
    }

    public function testDeleteResource()
    {
        $api = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $response = $api->deleteResource('test', 'test-1');

        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testFetchInvalidRelated()
    {
        $jsonApi = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $jsonApi->fetchRelated(
            'test', '1', $this->createMock(FetchInterface::class), 'parent'
        );
    }

    public function testHandleError()
    {
        $jsonApi = new JsonApi($this->createMock(ResourceProviderInterface::class));
        $jsonApi->setSerializer($this->createDocumentSerializerForError());

        $response = $jsonApi->handleError(
            $this->createConfiguredMock(
                ErrorInterface::class,
                ['getStatus' => 500]
            )
        );

        self::assertEquals(500, $response->getStatusCode());
        self::assertArrayHasKey(
            'errors',
            json_decode($response->getContent(), true)
        );
        self::assertCount(
            1,
            json_decode($response->getContent(), true)['errors']
        );
    }

    /**
     * @return DocumentSerializerInterface
     */
    private function createDocumentSerializerForSingleFetch(): DocumentSerializerInterface
    {
        return $this->createConfiguredMock(
            DocumentSerializerInterface::class,
            [
                'serializeDocument' => [
                    'data' => [
                        'type' => 'test',
                        'id' => '1',
                        'attributes' => ['test' => 'test'],
                    ],
                ],
            ]
        );
    }

    /**
     * @return DocumentSerializerInterface
     */
    private function createDocumentSerializerForCollectionFetch(): DocumentSerializerInterface
    {
        return $this->createConfiguredMock(
            DocumentSerializerInterface::class,
            [
                'serializeDocument' => [
                    'data' => [
                        [
                            'type' => 'test',
                            'id' => '1',
                            'attributes' => ['test' => 'test'],
                        ],
                        [
                            'type' => 'test',
                            'id' => '2',
                            'attributes' => ['test' => 'test'],
                        ],
                        [
                            'type' => 'test',
                            'id' => '3',
                            'attributes' => ['test' => 'test'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @return DocumentSerializerInterface
     */
    private function createDocumentSerializerForError(): DocumentSerializerInterface
    {
        return $this->createConfiguredMock(
            DocumentSerializerInterface::class,
            [
                'serializeDocument' => [
                    'errors' => [
                        [
                            'status' => 500,
                            'title' => 'test',
                        ],
                    ],
                ],
            ]
        );
    }
}
