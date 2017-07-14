<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Mock;

use Enm\JsonApi\Exception\ResourceNotFoundException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\JsonApiAwareInterface;
use Enm\JsonApi\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\ExceptionTrait;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRelationshipRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\RequestHandler\RequestHandlerInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class MockRequestHandler implements RequestHandlerInterface, JsonApiAwareInterface
{
    use JsonApiAwareTrait;
    use ExceptionTrait;

    /**
     * @var bool
     */
    private $exception;

    /**
     * @param bool $exception
     */
    public function __construct(bool $exception = false)
    {
        $this->exception = $exception;
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     *
     * @throws ResourceNotFoundException
     */
    public function fetchResource(FetchRequestInterface $request): DocumentInterface
    {
        if ($this->exception) {
            $this->throwResourceNotFound($request->type(), $request->id());
        }

        $resource = $this->jsonApi()->resource($request->type(), $request->id());
        $resource->attributes()->set('title', 'Test');


        $resource->relationships()->set(
            $this->jsonApi()->toManyRelationship(
                'examples',
                [
                    $this->jsonApi()->resource('examples', 'example-1')
                ]
            )
        );

        return $this->jsonApi()->singleResourceDocument($resource);
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     */
    public function fetchResources(FetchRequestInterface $request): DocumentInterface
    {
        if ($this->exception) {
            $this->throwUnsupportedType($request->type());
        }

        $resource = $this->jsonApi()->resource($request->type(), $request->type() . '-1');
        $resource->attributes()->set('title', 'Test');
        $resource->attributes()->set('description', 'Lorem Ipsum');

        return $this->jsonApi()->multiResourceDocument([$resource]);
    }

    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     */
    public function fetchRelationship(FetchRequestInterface $request): DocumentInterface
    {
        $resource = $this->jsonApi()->resource($request->relationship(), $request->relationship() . '-1');
        $resource->attributes()->set('title', 'Test ' . $request->relationship());
        $resource->relationships()->set(
            $this->jsonApi()->toOneRelationship(
                'test',
                $this->jsonApi()->resource('tests', 'test-1')
            )
        );


        return $this->jsonApi()->multiResourceDocument([$resource]);
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     */
    public function saveResource(SaveRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->singleResourceDocument($request->document()->data()->first());
    }

    /**
     * @param AdvancedJsonApiRequestInterface $request
     * @return DocumentInterface
     */
    public function deleteResource(AdvancedJsonApiRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->singleResourceDocument()->withHttpStatus(204);
    }

    /**
     * @param SaveRelationshipRequestInterface $request
     * @return DocumentInterface
     */
    public function saveRelationship(SaveRelationshipRequestInterface $request): DocumentInterface
    {
        return $this->jsonApi()->multiResourceDocument()->withHttpStatus(202);
    }
}
