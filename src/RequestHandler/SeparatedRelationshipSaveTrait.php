<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait SeparatedRelationshipSaveTrait
{
    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws BadRequestException
     */
    public function modifyRelationship(SaveRequestInterface $request): DocumentInterface
    {
        switch (strtoupper($request->originalHttpRequest()->getMethod())) {
            case 'POST':
                return $this->addRelatedResources($request);
            case 'DELETE':
                return $this->removeRelatedResources($request);
            case 'PATCH':
                return $this->replaceRelatedResources($request);
        }

        throw new BadRequestException('Invalid relationship modification request!');
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    abstract protected function replaceRelatedResources(SaveRequestInterface $request): DocumentInterface;

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    abstract protected function addRelatedResources(SaveRequestInterface $request): DocumentInterface;

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    abstract protected function removeRelatedResources(SaveRequestInterface $request): DocumentInterface;
}
