<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Serializer\DocumentDeserializerInterface;
use Enm\JsonApi\Serializer\DocumentSerializerInterface;
use Enm\JsonApi\Server\JsonApiServer;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonApiServerTest extends TestCase
{
    public function testCreateEmptyRequestBody(): void
    {
        $body = $this->createJsonApiServer()->createRequestBody(null);
        $this->assertNull($body);
    }

    public function testCreateRequestBody(): void
    {
        $body = $this->createJsonApiServer()->createRequestBody('{"data":[]}');
        $this->assertInstanceOf(DocumentInterface::class, $body);
    }

    public function testHandleException(): void
    {
        $api = $this->createJsonApiServer();
        $e = $api->handleException(new \Exception('Test'));

        $this->assertEquals(500, $e->status());
        $this->assertEquals('application/vnd.api+json', $e->headers()->getOptional('Content-Type'));
        $this->assertEquals(1, $e->document()->errors()->count());
        $this->assertEquals('Test', $e->document()->errors()->all()[0]->title());
    }

    /**
     * @return JsonApiServer
     */
    protected function createJsonApiServer(): JsonApiServer
    {
        /** @var DocumentDeserializerInterface $deserializer */
        $deserializer = $this->createMock(DocumentDeserializerInterface::class);
        /** @var DocumentSerializerInterface $serializer */
        $serializer = $this->createMock(DocumentSerializerInterface::class);

        return new JsonApiServer($deserializer, $serializer);
    }
}
