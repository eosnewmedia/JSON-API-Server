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

        $last = new Uri($document->links()->get('last')->href());
        parse_str($last->getQuery(), $lastQuery);
        self::assertArraySubset(['page' => ['offset' => 89]], $lastQuery);
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
                        'getUri' => new Uri('http://example.com/api/tests?page[offset]=9&page[limit]=5')
                    ]
                ),
                'pagination' => new KeyValueCollection(['offset' => 9, 'limit' => 5])
            ]
        );

        $generator->addPaginationLinks($document, $request, 100);

        self::assertTrue($document->links()->has('self'));
        self::assertTrue($document->links()->has('first'));
        self::assertTrue($document->links()->has('previous'));
        self::assertTrue($document->links()->has('next'));
        self::assertTrue($document->links()->has('last'));
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
                        'getUri' => new Uri('http://example.com/api/tests?page[offset]=89&page[limit]=10')
                    ]
                ),
                'pagination' => new KeyValueCollection(['offset' => 89, 'limit' => 10])
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
