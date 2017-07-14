<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRelationshipRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface RequestHandlerInterface
{
    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     */
    public function fetchResource(FetchRequestInterface $request): DocumentInterface;

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     */
    public function fetchResources(FetchRequestInterface $request): DocumentInterface;

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     */
    public function fetchRelationship(FetchRequestInterface $request): DocumentInterface;

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    public function saveResource(SaveRequestInterface $request): DocumentInterface;

    /**
     * @param AdvancedJsonApiRequestInterface $request
     * @return DocumentInterface
     */
    public function deleteResource(AdvancedJsonApiRequestInterface $request): DocumentInterface;

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return DocumentInterface
     */
    public function saveRelationship(SaveRelationshipRequestInterface $request): DocumentInterface;
}
