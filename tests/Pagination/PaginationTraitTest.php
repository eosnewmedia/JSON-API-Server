<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Pagination;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Pagination\PaginationLinkGeneratorInterface;
use Enm\JsonApi\Server\Pagination\PaginationTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class PaginationTraitTest extends TestCase
{
    /**
     * @expectedException \Enm\JsonApi\Exception\JsonApiException
     * @expectedExceptionMessage Pagination link generator is not available!
     */
    public function testMissingPaginationLinkGenerator()
    {
        /** @var PaginationTrait $mock */
        $mock = $this->getMockForTrait(PaginationTrait::class);

        $reflection = new \ReflectionObject($mock);

        $method = $reflection->getMethod('paginate');
        $method->setAccessible(true);

        $method->invokeArgs(
            $mock,
            [
                $this->createMock(DocumentInterface::class),
                $this->createMock(FetchRequestInterface::class),
                10
            ]
        );
    }

    public function testPaginationLinkGenerator()
    {
        /** @var PaginationTrait $mock */
        $mock = $this->getMockForTrait(PaginationTrait::class);

        /** @var PaginationLinkGeneratorInterface $paginationLinkGenerator */
        $paginationLinkGenerator = $this->createMock(PaginationLinkGeneratorInterface::class);

        $mock->setPaginationLinkGenerator($paginationLinkGenerator);

        $reflection = new \ReflectionObject($mock);

        $method = $reflection->getMethod('paginate');
        $method->setAccessible(true);

        $method->invokeArgs(
            $mock,
            [
                $this->createMock(DocumentInterface::class),
                $this->createMock(FetchRequestInterface::class),
                10
            ]
        );

        // no exception was thrown
        self::assertTrue(true);
    }
}
