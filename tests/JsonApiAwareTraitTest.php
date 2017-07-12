<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests;

use Enm\JsonApi\Server\JsonApiServer;
use Enm\JsonApi\Server\Tests\Mock\JsonAwareTestContainer;
use Enm\JsonApi\Server\Tests\Mock\MockRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonApiAwareTraitTest extends TestCase
{
    public function testJsonApi()
    {
        $jsonApiAware = new JsonAwareTestContainer();

        $api = new JsonApiServer(new MockRequestHandler());
        $jsonApiAware->setJsonApi($api);

        self::assertSame($api, $jsonApiAware->getJsonApi());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMissingJsonApi()
    {
        $jsonApiAware = new JsonAwareTestContainer();
        $jsonApiAware->getJsonApi();
    }
}
