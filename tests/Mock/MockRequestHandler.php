<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Mock;

use Enm\JsonApi\Exception\ResourceNotFoundException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\JsonApiAwareInterface;
use Enm\JsonApi\Server\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\ExceptionTrait;
use Enm\JsonApi\Server\Model\Request\FetchMainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\MainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\SaveMainRequestProviderInterface;
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
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     *
     * @throws ResourceNotFoundException
     */
    public function fetchResource(FetchMainRequestProviderInterface $request): DocumentInterface
    {
        if ($this->exception) {
            $this->throwResourceNotFound($request->type(), $request->id());
        }

        $resource = $this->jsonApi()->resource($request->type(), $request->id());
        $resource->attributes()->set('title', 'Test');

        return $this->jsonApi()->singleResourceDocument($resource);
    }

    /**
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function fetchResources(FetchMainRequestProviderInterface $request): DocumentInterface
    {
        if ($this->exception) {
            $this->throwUnsupportedType($request->type());
        }

        $resource = $this->jsonApi()->resource($request->type(), $request->id());
        $resource->attributes()->set('title', 'Test');
        $resource->attributes()->set('description', 'Lorem Ipsum');

        return $this->jsonApi()->multiResourceDocument([$resource]);
    }

    /**
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function fetchRelationship(FetchMainRequestProviderInterface $request): DocumentInterface
    {
    }

    /**
     * @param SaveMainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function saveResource(SaveMainRequestProviderInterface $request): DocumentInterface
    {
        return $this->jsonApi()->singleResourceDocument($request->document()->data()->first());
    }

    /**
     * @param MainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function deleteResource(MainRequestProviderInterface $request): DocumentInterface
    {
        return $this->jsonApi()->singleResourceDocument()->withHttpStatus(204);
    }
}
