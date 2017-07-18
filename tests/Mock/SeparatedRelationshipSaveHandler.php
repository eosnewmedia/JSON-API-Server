<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Mock;

use Enm\JsonApi\JsonApiAwareInterface;
use Enm\JsonApi\JsonApiAwareTrait;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\RequestHandler\FetchOnlyTrait;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface;
use Enm\JsonApi\Server\RequestHandler\SeparatedRelationshipSaveTrait;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class SeparatedRelationshipSaveHandler implements RequestHandlerInterface, JsonApiAwareInterface
{
    use  JsonApiAwareTrait, FetchOnlyTrait, SeparatedRelationshipSaveTrait {
        SeparatedRelationshipSaveTrait::modifyRelationship insteadof FetchOnlyTrait;
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     */
    public function fetchResource(FetchRequestInterface $request): DocumentInterface
    {
        throw new \RuntimeException('No needed for this test!');
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     */
    public function fetchResources(FetchRequestInterface $request): DocumentInterface
    {
        throw new \RuntimeException('No needed for this test!');
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws \RuntimeException
     */
    public function fetchRelationship(FetchRequestInterface $request): DocumentInterface
    {
        throw new \RuntimeException('No needed for this test!');
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    protected function replaceRelatedResources(SaveRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->multiResourceDocument($request->document()->data()->all());
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    protected function addRelatedResources(SaveRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->multiResourceDocument($request->document()->data()->all());
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    protected function removeRelatedResources(SaveRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->multiResourceDocument($request->document()->data()->all());
    }
}
