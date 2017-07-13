<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Mock;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\JsonApiAwareInterface;
use Enm\JsonApi\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\RequestHandler\FetchOnlyTrait;
use Enm\JsonApi\Server\RequestHandler\NoRelationshipsTrait;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class FetchOnlyMockRequestHandler implements RequestHandlerInterface, JsonApiAwareInterface
{
    use FetchOnlyTrait;
    use NoRelationshipsTrait;
    use JsonApiAwareTrait;

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     */
    public function fetchResource(FetchRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()
            ->singleResourceDocument(
                $this->jsonApi()->resource($request->type(), $request->id())
            );
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     */
    public function fetchResources(FetchRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()
            ->multiResourceDocument(
                [$this->jsonApi()->resource($request->type(), $request->id())]
            );
    }
}
