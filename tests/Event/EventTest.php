<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Event;

use Enm\JsonApi\Server\Event\DocumentEvent;
use Enm\JsonApi\Server\Event\DocumentResponseEvent;
use Enm\JsonApi\Server\Event\FetchEvent;
use Enm\JsonApi\Server\Event\ResourceEvent;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class EventTest extends TestCase
{
    public function testFetchEvent()
    {
        $event = new FetchEvent(
            $this->createMock(FetchInterface::class),
            'type',
            'id'
        );

        self::assertInstanceOf(FetchInterface::class, $event->fetchRequest());
        self::assertTrue($event->isSingleResourceFetched());
        self::assertEquals('type', $event->requestedType());
        self::assertEquals('id', $event->requestedId());
    }

    public function testResourceEvent()
    {
        $event = new ResourceEvent(
            $this->createMock(ResourceInterface::class),
            $this->createMock(FetchInterface::class)
        );

        self::assertInstanceOf(ResourceInterface::class, $event->getResource());
        self::assertInstanceOf(
            FetchInterface::class,
            $event->getApiRequest()
        );
    }

    public function testDocumentEvent()
    {
        $event = new DocumentEvent(
            $this->createMock(DocumentInterface::class),
            $this->createMock(Request::class)
        );

        self::assertInstanceOf(DocumentInterface::class, $event->getDocument());
        self::assertInstanceOf(
            Request::class,
            $event->getRequest()
        );
    }

    public function testDocumentResponseEvent()
    {
        $event = new DocumentResponseEvent(
            $this->createMock(DocumentInterface::class),
            $this->createMock(Request::class),
            $this->createMock(Response::class)
        );

        self::assertInstanceOf(
            Response::class,
            $event->getResponse()
        );
    }
}
