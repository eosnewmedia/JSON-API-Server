<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Pagination;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class OffsetPaginationLinkGenerator implements PaginationLinkGeneratorInterface
{
    const OFFSET = 'offset';
    const LIMIT = 'limit';
    /**
     * @var int
     */
    private $defaultLimit;

    /**
     * @param int $defaultLimit
     */
    public function __construct(int $defaultLimit)
    {
        $this->defaultLimit = $defaultLimit;
    }

    /**
     * @param FetchRequestInterface $request
     *
     * @return int
     * @throws \Exception
     */
    protected function limit(FetchRequestInterface $request): int
    {
        $limit = (int)$request->pagination()->getOptional(self::LIMIT, $this->defaultLimit);
        if ($limit < 1) {
            throw new BadRequestException('Invalid pagination limit requested!');
        }

        return $limit;
    }

    /**
     * @param FetchRequestInterface $request
     * @param int $offset
     *
     * @return string
     * @throws \Exception
     */
    protected function createPaginatedUri(FetchRequestInterface $request, int $offset): string
    {
        $uri = $request->originalHttpRequest()->getUri();
        parse_str($uri->getQuery(), $query);

        $query['page'][self::OFFSET] = $offset;
        if ($request->pagination()->has(self::LIMIT)) {
            $query['page'][self::LIMIT] = $this->limit($request);
        }

        return (string)$uri->withQuery(http_build_query($query));
    }

    /**
     * This method adds all needed pagination links to a document
     *
     * @param DocumentInterface $document
     * @param FetchRequestInterface $request
     * @param int $resultCount
     *
     * @return void
     * @throws \Exception
     */
    public function addPaginationLinks(DocumentInterface $document, FetchRequestInterface $request, int $resultCount)
    {
        $maxOffset = ($resultCount - 1);

        $document->links()->createLink(
            self::SELF_LINK,
            (string)$request->originalHttpRequest()->getUri()
        );

        $currentOffset = (int)$request->pagination()->getOptional(self::OFFSET, 0);
        if ($currentOffset < 0 || $currentOffset > $maxOffset) {
            throw new BadRequestException('Invalid pagination offset requested!');
        }
        $limit = $this->limit($request);

        if ($currentOffset !== 0) {
            $document->links()->createLink(
                self::FIRST_LINK,
                $this->createPaginatedUri($request, 0)
            );
        }

        $previous = $currentOffset - $limit;
        if ($previous >= 0) {
            $document->links()->createLink(
                self::PREVIOUS_LINK,
                $this->createPaginatedUri($request, $previous)
            );
        } elseif ($currentOffset !== 0) {
            $document->links()->createLink(
                self::PREVIOUS_LINK,
                $this->createPaginatedUri($request, 0)
            );
        }

        $last = $resultCount - $limit;
        $next = $currentOffset + $limit;

        if ($next <= $last) {
            $document->links()->createLink(
                self::NEXT_LINK,
                $this->createPaginatedUri($request, $next)
            );
        }

        if ($last > $currentOffset) {
            $document->links()->createLink(
                self::LAST_LINK,
                $this->createPaginatedUri($request, $last)
            );
        }
    }
}
