<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\ResourceProvider;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Enm\JsonApi\Server\Model\Request\SaveRelationshipRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait SeparateRelationshipSaveTrait
{
    /**
     * @param SaveRelationshipRequestInterface $request
     * @return RelationshipInterface
     * @throws BadRequestException
     */
    public function modifyRelationship(SaveRelationshipRequestInterface $request): RelationshipInterface
    {
        if ($request->requestedAdd()) {
            return $this->addRelated($request);
        }

        if ($request->requestedRemove()) {
            return $this->removeRelated($request);
        }

        if ($request->requestedReplace()) {
            return $this->replaceRelationship($request);
        }

        throw new BadRequestException('Invalid relationship modification request!');
    }

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return RelationshipInterface
     */
    abstract protected function replaceRelationship(SaveRelationshipRequestInterface $request): RelationshipInterface;

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return RelationshipInterface
     */
    abstract protected function addRelated(SaveRelationshipRequestInterface $request): RelationshipInterface;

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return RelationshipInterface
     */
    abstract protected function removeRelated(SaveRelationshipRequestInterface $request): RelationshipInterface;
}
