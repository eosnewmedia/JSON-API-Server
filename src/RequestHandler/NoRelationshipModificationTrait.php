<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\NotAllowedException;
use Enm\JsonApi\Model\Request\RequestInterface;
use Enm\JsonApi\Model\Response\ResponseInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait NoRelationshipModificationTrait
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotAllowedException
     */
    public function addRelatedResources(RequestInterface $request): ResponseInterface
    {
        throw new NotAllowedException('You are not allowed to modify the relationship ' . $request->relationship());
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotAllowedException
     */
    public function replaceRelatedResources(RequestInterface $request): ResponseInterface
    {
        throw new NotAllowedException('You are not allowed to modify the relationship ' . $request->relationship());
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotAllowedException
     */
    public function removeRelatedResources(RequestInterface $request): ResponseInterface
    {
        throw new NotAllowedException('You are not allowed to modify the relationship ' . $request->relationship());
    }
}
