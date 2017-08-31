<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Pagination;

use Enm\JsonApi\Model\Common\KeyValueCollection;
use Enm\JsonApi\Model\Document\Document;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Pagination\OffsetPaginationLinkGenerator;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class OffsetPaginationLinkGeneratorTest extends TestCase
{
    public function testAddPaginationLinksWithoutPaginationInQuery()
    {
        $generator = new OffsetPaginationLinkGenerator(10);
        $document = new Document();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getUri' => new Uri('http://example.com/api/tests')
                    ]
                ),
                'pagination' => new KeyValueCollection([])
            ]
        );

        $generator->addPaginationLinks($document, $request, 100);

        self::assertTrue($document->links()->has('self'));
        self::assertFalse($document->links()->has('first'));
        self::assertFalse($document->links()->has('previous'));
        self::assertTrue($document->links()->has('next'));
        self::assertTrue($document->links()->has('last'));


        $next = new Uri($document->links()->get('next')->href());
        parse_str($next->getQuery(), $nextQuery);
        self::assertArraySubset(['page' => ['offset' => 10]], $nextQuery);

        $last = new Uri($document->links()->get('last')->href());
        parse_str($last->getQuery(), $lastQuery);
        self::assertArraySubset(['page' => ['offset' => 90]], $lastQuery);
    }

    public function testAddPaginationLinksWithPaginationQuery()
    {
        $generator = new OffsetPaginationLinkGenerator(10);
        $document = new Document();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getUri' => new Uri('http://example.com/api/tests?page[offset]=10&page[limit]=5')
                    ]
                ),
                'pagination' => new KeyValueCollection(['offset' => 10, 'limit' => 5])
            ]
        );

        $generator->addPaginationLinks($document, $request, 100);

        self::assertTrue($document->links()->has('self'));
        self::assertTrue($document->links()->has('first'));
        self::assertTrue($document->links()->has('previous'));
        self::assertTrue($document->links()->has('next'));
        self::assertTrue($document->links()->has('last'));

        $first = new Uri($document->links()->get('first')->href());
        parse_str($first->getQuery(), $firstQuery);
        self::assertArraySubset(['page' => ['offset' => 0, 'limit' => 5]], $firstQuery);

        $previous = new Uri($document->links()->get('previous')->href());
        parse_str($previous->getQuery(), $previousQuery);
        self::assertArraySubset(['page' => ['offset' => 5, 'limit' => 5]], $previousQuery);

        $next = new Uri($document->links()->get('next')->href());
        parse_str($next->getQuery(), $nextQuery);
        self::assertArraySubset(['page' => ['offset' => 15, 'limit' => 5]], $nextQuery);
    }

    public function testAddPaginationLinksWithPaginationPrevious()
    {
        $generator = new OffsetPaginationLinkGenerator(10);
        $document = new Document();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getUri' => new Uri('http://example.com/api/tests?page[offset]=10&page[limit]=5')
                    ]
                ),
                'pagination' => new KeyValueCollection(['offset' => 2, 'limit' => 5])
            ]
        );

        $generator->addPaginationLinks($document, $request, 100);

        $previous = new Uri($document->links()->get('previous')->href());
        parse_str($previous->getQuery(), $previousQuery);
        self::assertArraySubset(['page' => ['offset' => 0, 'limit' => 5]], $previousQuery);
    }

    public function testAddPaginationLinksWithPaginationQueryLast()
    {
        $generator = new OffsetPaginationLinkGenerator(10);
        $document = new Document();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getUri' => new Uri('http://example.com/api/tests?page[offset]=90&page[limit]=10')
                    ]
                ),
                'pagination' => new KeyValueCollection(['offset' => 90, 'limit' => 10])
            ]
        );

        $generator->addPaginationLinks($document, $request, 100);

        self::assertTrue($document->links()->has('self'));
        self::assertTrue($document->links()->has('first'));
        self::assertTrue($document->links()->has('previous'));
        self::assertFalse($document->links()->has('next'));
        self::assertFalse($document->links()->has('last'));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testAddPaginationLinksWithInvalidLimit()
    {
        $generator = new OffsetPaginationLinkGenerator(10);
        $document = new Document();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getUri' => new Uri('http://example.com/api/tests?page[offset]=9&page[limit]=0')
                    ]
                ),
                'pagination' => new KeyValueCollection(['offset' => 9, 'limit' => 0])
            ]
        );

        $generator->addPaginationLinks($document, $request, 100);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testAddPaginationLinksWithInvalidOffset()
    {
        $generator = new OffsetPaginationLinkGenerator(10);
        $document = new Document();
        /** @var FetchRequestInterface $request */
        $request = $this->createConfiguredMock(
            FetchRequestInterface::class,
            [
                'originalHttpRequest' => $this->createConfiguredMock(
                    RequestInterface::class,
                    [
                        'getUri' => new Uri('http://example.com/api/tests?page[offset]=-9&page[limit]=0')
                    ]
                ),
                'pagination' => new KeyValueCollection(['offset' => -9, 'limit' => 0])
            ]
        );

        $generator->addPaginationLinks($document, $request, 100);
    }
}
