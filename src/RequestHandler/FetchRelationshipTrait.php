<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\JsonApiInterface;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait FetchRelationshipTrait
{
    /**
     * @param FetchRequestInterface $request
     *
     * @return DocumentInterface
     */
    abstract public function fetchResource(FetchRequestInterface $request): DocumentInterface;

    /**
     * @return JsonApiInterface
     */
    abstract protected function jsonApi(): JsonApiInterface;

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     */
    public function fetchRelationship(FetchRequestInterface $request): DocumentInterface
    {
        $request->include($request->relationship());

        $fetchResponse = $this->fetchResource($request);

        $relationship = $fetchResponse->data()->first()->relationships()->get($request->relationship());


        if ($relationship->shouldBeHandledAsCollection()) {
            $document = $this->jsonApi()->multiResourceDocument($relationship->related()->all());
        } else {
            $document = $this->jsonApi()->singleResourceDocument(
                !$relationship->related()->isEmpty() ? $relationship->related()->first() : null
            );
        }

        $document->metaInformation()->mergeCollection($relationship->metaInformation());

        foreach ($relationship->links()->all() as $link) {
            $document->links()->set($link);
        }

        return $document;
    }
}
