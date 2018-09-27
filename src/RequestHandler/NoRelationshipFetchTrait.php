<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\NotAllowedException;
use Enm\JsonApi\Model\Request\RequestInterface;
use Enm\JsonApi\Model\Response\ResponseInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait NoRelationshipFetchTrait
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotAllowedException
     */
    public function fetchRelationship(RequestInterface $request): ResponseInterface
    {
        throw new NotAllowedException('You are not allowed to fetch the relationship ' . $request->relationship());
    }
}
