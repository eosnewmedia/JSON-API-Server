<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\NotAllowedException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\MainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\SaveMainRequestProviderInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait FetchOnlyTrait
{
    /**
     * @param SaveMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws NotAllowedException
     */
    public function saveResource(SaveMainRequestProviderInterface $request): DocumentInterface
    {
        if ($request->containsId()) {
            throw new NotAllowedException('You are not allowed to patch resources of type ' . $request->type());
        }

        throw new NotAllowedException('You are not allowed to create resources of type ' . $request->type());
    }

    /**
     * @param MainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws NotAllowedException
     */
    public function deleteResource(MainRequestProviderInterface $request): DocumentInterface
    {
        throw new NotAllowedException('You are not allowed to delete resources of type ' . $request->type());
    }
}
