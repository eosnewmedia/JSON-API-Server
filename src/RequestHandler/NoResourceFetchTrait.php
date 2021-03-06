<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\NotAllowedException;
use Enm\JsonApi\Model\Request\RequestInterface;
use Enm\JsonApi\Model\Response\ResponseInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait NoResourceFetchTrait
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotAllowedException
     */
    public function fetchResource(RequestInterface $request): ResponseInterface
    {
        throw new NotAllowedException('You are not allowed to fetch a resource of type ' . $request->type());
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotAllowedException
     */
    public function fetchResources(RequestInterface $request): ResponseInterface
    {
        throw new NotAllowedException('You are not allowed to fetch a resource of type ' . $request->type());
    }
}
