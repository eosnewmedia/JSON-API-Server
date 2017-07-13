<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait SeparatedSaveTrait
{
    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    public function saveResource(SaveRequestInterface $request): DocumentInterface
    {
        if (!$request->containsId()) {
            return $this->createResource($request);
        }

        return $this->patchResource($request);
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    abstract protected function createResource(SaveRequestInterface $request): DocumentInterface;

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    abstract protected function patchResource(SaveRequestInterface $request): DocumentInterface;
}
