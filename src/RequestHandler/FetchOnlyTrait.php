<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\NotAllowedException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\HttpRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait FetchOnlyTrait
{
    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws NotAllowedException
     */
    public function saveResource(SaveRequestInterface $request): DocumentInterface
    {
        if ($request->containsId()) {
            throw new NotAllowedException('You are not allowed to patch resources of type ' . $request->type());
        }

        throw new NotAllowedException('You are not allowed to create resources of type ' . $request->type());
    }

    /**
     * @param HttpRequestInterface $request
     * @return DocumentInterface
     * @throws NotAllowedException
     */
    public function deleteResource(HttpRequestInterface $request): DocumentInterface
    {
        throw new NotAllowedException('You are not allowed to delete resources of type ' . $request->type());
    }
}
