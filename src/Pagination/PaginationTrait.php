<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Pagination;

use Enm\JsonApi\Exception\JsonApiException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait PaginationTrait
{
    /**
     * @var PaginationLinkGeneratorInterface
     */
    private $paginationLinkGenerator;

    /**
     * @param PaginationLinkGeneratorInterface $paginationLinkGenerator
     *
     * @return void
     */
    public function setPaginationLinkGenerator(PaginationLinkGeneratorInterface $paginationLinkGenerator)
    {
        $this->paginationLinkGenerator = $paginationLinkGenerator;
    }

    /**
     * @param DocumentInterface $document
     * @param FetchRequestInterface $request
     * @param int $resultCount
     *
     * @return void
     * @throws JsonApiException
     */
    protected function paginate(DocumentInterface $document, FetchRequestInterface $request, int $resultCount)
    {
        if (!$this->paginationLinkGenerator instanceof PaginationLinkGeneratorInterface) {
            throw new JsonApiException('Pagination link generator is not available!');
        }
        $this->paginationLinkGenerator->addPaginationLinks($document, $request, $resultCount);

        $document->metaInformation()->set('totalResources', $resultCount);
    }
}
