<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Model\Request;

use Enm\JsonApi\Server\Model\Request\SaveResourceRequest;
use Enm\JsonApi\Server\Model\Request\FetchInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class SaveResourceRequestTest extends TestCase
{
    public function testCreateRequest()
    {
        $request = new SaveResourceRequest(
            $this->getJsonRequest([
                'data' => [
                    'type' => 'test',
                    'attributes' => ['test' => 'test'],
                    'meta' => ['metaTest' => 'meta'],
                    'relationships' => [
                        'toOne' => [
                            'data' => [
                                'type' => 'relationA',
                                'id' => '1',
                            ],
                        ],
                        'emptyToOne' => ['data' => null],
                        'toMany' => [
                            'data' => [
                                [
                                    'type' => 'relationB',
                                    'id' => '1',
                                ],
                            ],
                        ],
                        'emptyToMany' => ['data' => []],
                    ],
                ],
            ])
        );

        self::assertRegExp(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $request->resource()->getId()
        ); // validate generation of a valid uuid
        self::assertCount(1, $request->resource()->attributes()->all());
        self::assertCount(4, $request->resource()->relationships()->all());
        self::assertEquals('test', $request->resource()->getType());
        self::assertEquals('meta', $request->resource()->metaInformations()->getRequired('metaTest'));
        self::assertInstanceOf(FetchInterface::class, $request->createFetch());
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testCreateRequestInvalidRelationship()
    {
        new SaveResourceRequest(
            $this->getJsonRequest([
                'data' => [
                    'type' => 'test',
                    'attributes' => ['test' => 'test'],
                    'relationships' => [
                        'toOne' => [
                            'type' => 'relationA',
                            'id' => '1',
                        ],
                    ],
                ],
            ])
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testRequestNoJson()
    {
        new SaveResourceRequest(
            $this->getJsonRequest(null)
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testRequestInvalidType()
    {
        new SaveResourceRequest(
            $this->getJsonRequest(['data' => ['type' => true]])
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testRequestInvalidAttributes()
    {
        new SaveResourceRequest(
            $this->getJsonRequest([
                'data' => [
                    'type' => 'test',
                    'attributes' => 'invalid',
                ],
            ])
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testRequestInvalidMeta()
    {
        new SaveResourceRequest(
            $this->getJsonRequest([
                'data' => [
                    'type' => 'test',
                    'attributes' => [],
                    'meta' => 'no-meta',
                ],
            ])
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testRequestInvalidRelationships()
    {
        new SaveResourceRequest(
            $this->getJsonRequest([
                'data' => [
                    'type' => 'test',
                    'relationships' => 'invalid',
                ],
            ])
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testRequestNoData()
    {
        new SaveResourceRequest(
            $this->getJsonRequest(['data' => null])
        );
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testRequestInvalidId()
    {
        new SaveResourceRequest(
            $this->getJsonRequest(['data' => ['type' => 'test', 'id' => true]])
        );
    }

    public function testPatchResource()
    {
        $request = new SaveResourceRequest(
            $this->getJsonRequest([
                'data' => [
                    'type' => 'test',
                    'id' => 'test-1',
                    'attributes' => ['test' => 'test'],
                    'relationships' => [
                        'toOne' => [
                            'data' => [
                                'type' => 'relationA',
                                'id' => '1',
                            ],
                        ],
                        'emptyToOne' => ['data' => null],
                        'toMany' => [
                            'data' => [
                                [
                                    'type' => 'relationB',
                                    'id' => '1',
                                ],
                            ],
                        ],
                        'emptyToMany' => ['data' => []],
                    ],
                ],
            ])
        );

        self::assertCount(1, $request->resource()->attributes()->all());
        self::assertCount(4, $request->resource()->relationships()->all());
        self::assertEquals('test', $request->resource()->getType());
        self::assertEquals('test-1', $request->resource()->getId());
    }

    /**
     * @param mixed $data
     *
     * @return Request
     */
    private function getJsonRequest($data): Request
    {
        $http = new Request([], [], [], [], [], [], json_encode($data));
        $http->headers->set('Content-Type', 'application/vnd.api+json');

        return $http;
    }
}
