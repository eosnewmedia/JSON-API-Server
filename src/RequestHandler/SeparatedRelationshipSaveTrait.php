<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\SaveRelationshipRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait SeparatedRelationshipSaveTrait
{
    /**
     * @param SaveRelationshipRequestInterface $request
     * @return DocumentInterface
     * @throws BadRequestException
     */
    public function saveRelationship(SaveRelationshipRequestInterface $request): DocumentInterface
    {
        if ($request->requestedAdd()) {
            return $this->addRelatedResources($request);
        }

        if ($request->requestedRemove()) {
            return $this->removeRelatedResources($request);
        }

        if ($request->requestedReplace()) {
            return $this->replaceRelatedResources($request);
        }

        throw new BadRequestException('Invalid relationship modification request!');
    }

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return DocumentInterface
     */
    abstract protected function replaceRelatedResources(SaveRelationshipRequestInterface $request): DocumentInterface;

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return DocumentInterface
     */
    abstract protected function addRelatedResources(SaveRelationshipRequestInterface $request): DocumentInterface;

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return DocumentInterface
     */
    abstract protected function removeRelatedResources(SaveRelationshipRequestInterface $request): DocumentInterface;
}
