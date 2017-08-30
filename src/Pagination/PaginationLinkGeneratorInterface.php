<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Pagination;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface PaginationLinkGeneratorInterface
{
    /**
     * This method adds all needed pagination links to a document
     *
     * @param DocumentInterface $document
     * @param FetchRequestInterface $request
     * @param int $resultCount
     *
     * @return void
     */
    public function addPaginationLinks(DocumentInterface $document, FetchRequestInterface $request, int $resultCount);
}
