<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Pagination;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class OffsetPaginationLinkGenerator implements PaginationLinkGeneratorInterface
{
    /**
     * @var int
     */
    private $defaultSize;

    /**
     * @param int $defaultSize
     */
    public function __construct($defaultSize)
    {
        $this->defaultSize = $defaultSize;
    }

    /**
     * @param FetchRequestInterface $request
     * @return int
     */
    protected function size(FetchRequestInterface $request): int
    {
        $size = (int)$request->pagination()->getOptional('size', $this->defaultSize);
        if ($size < 1) {
            $size = $this->defaultSize;
        }

        return $size;
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

        $query['page']['offset'] = $offset;
        if ($request->pagination()->has('size')) {
            $query['page']['size'] = $this->size($request);
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
        //pagination links if paginated
        if (!$request->pagination()->isEmpty()) {
            $maxOffset = ($resultCount - 1);

            $document->links()->createLink(
                'self',
                (string)$request->originalHttpRequest()->getUri()
            );

            $currentOffset = (int)$request->pagination()->getOptional('offset', 0);
            if ($currentOffset < 0) {
                $currentOffset = 0;
            }
            $currentSize = $this->size($request);

            if ($currentOffset !== 0) {
                $document->links()->createLink(
                    'first',
                    $this->createPaginatedUri($request, 0)
                );
            }

            $previous = $currentOffset - $currentSize;
            if ($previous >= 0) {
                $document->links()->createLink(
                    'previous',
                    $this->createPaginatedUri($request, $previous)
                );
            }

            $next = $currentOffset + $currentSize;
            if ($next < $maxOffset - $currentOffset) {
                $document->links()->createLink(
                    'next',
                    $this->createPaginatedUri($request, $next)
                );
            }

            $last = $maxOffset - $currentSize;
            if ($last > $currentOffset) {
                $document->links()->createLink(
                    'last',
                    $this->createPaginatedUri($request, $last)
                );
            }
        }
    }
}
