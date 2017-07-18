<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\ResourceProvider;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait SeparatedRelationshipSaveTrait
{
    /**
     * @param SaveRequestInterface $request
     * @return RelationshipInterface
     * @throws BadRequestException
     */
    public function modifyRelationship(SaveRequestInterface $request): RelationshipInterface
    {
        switch (strtoupper($request->originalHttpRequest()->getMethod())) {
            case 'POST':
                return $this->addRelated($request);
            case 'DELETE':
                return $this->removeRelated($request);
            case 'PATCH':
                return $this->replaceRelationship($request);
        }

        throw new BadRequestException('Invalid relationship modification request!');
    }

    /**
     * @param SaveRequestInterface $request
     * @return RelationshipInterface
     */
    abstract protected function replaceRelationship(SaveRequestInterface $request): RelationshipInterface;

    /**
     * @param SaveRequestInterface $request
     * @return RelationshipInterface
     */
    abstract protected function addRelated(SaveRequestInterface $request): RelationshipInterface;

    /**
     * @param SaveRequestInterface $request
     * @return RelationshipInterface
     */
    abstract protected function removeRelated(SaveRequestInterface $request): RelationshipInterface;
}
