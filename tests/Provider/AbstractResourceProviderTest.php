<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Provider;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\SaveResourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class AbstractResourceProviderTest extends TestCase
{
    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testExceptionOnCreate()
    {
        (new TestProvider())->createResource($this->createMock(SaveResourceInterface::class));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testExceptionOnPacth()
    {
        (new TestProvider())->patchResource($this->createMock(SaveResourceInterface::class));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testExceptionOnDelete()
    {
        (new TestProvider())->deleteResource('test', '1');
    }

    public function testCreateKeyValueCollection()
    {
        (new TestProvider())->executeCreateKeyValueCollection();
        self::assertTrue(true);
    }

    public function testCreateInvalidRequestException()
    {
        (new TestProvider())->executeCreateInvalidRequestException();
        self::assertTrue(true);
    }

    public function testCreateHttpEException()
    {
        (new TestProvider())->executeCreateHttpException();
        self::assertTrue(true);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\UnsupportedTypeException
     */
    public function testCreateUnsupportedTypeException()
    {
        (new TestProvider())->findResources('test', $this->createMock(FetchInterface::class));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\ResourceNotFoundException
     */
    public function testCreateResourceNotException()
    {
        (new TestProvider())->findResource('test', '1', $this->createMock(FetchInterface::class));
    }
}
