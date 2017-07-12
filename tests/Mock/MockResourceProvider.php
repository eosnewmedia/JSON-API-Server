<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Mock;

use Enm\JsonApi\Model\Resource\ResourceInterface;
use Enm\JsonApi\Server\JsonApiAwareInterface;
use Enm\JsonApi\Server\JsonApiAwareTrait;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\AdvancedJsonApiRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;
use Enm\JsonApi\Server\ResourceProvider\ResourceProviderInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class MockResourceProvider implements ResourceProviderInterface, JsonApiAwareInterface
{
    use JsonApiAwareTrait;

    /**
     * Finds a single resource by type and id
     *
     * @param FetchRequestInterface $request
     *
     * @return ResourceInterface
     */
    public function findResource(FetchRequestInterface $request): ResourceInterface
    {
        $resource = $this->jsonApi()->resource($request->type(), $request->id());

        $toOneRelationship = $this->jsonApi()->toOneRelationship(
            'example',
            $this->jsonApi()->resource('examples', 'example-1')
        );
        $toOneRelationship->links()
            ->createLink(
                'self',
                'http://example.com/' . $request->type() . '/' . $request->id() . '/relationship/' . $request->relationship()
            );

        $resource->relationships()
            ->set($toOneRelationship)
            ->set(
                $this->jsonApi()->toManyRelationship(
                    'examples',
                    [
                        $this->jsonApi()->resource('examples', 'example-1'),
                        $this->jsonApi()->resource('examples', 'example-2')
                    ]
                )
            );

        return $resource;
    }

    /**
     * Finds all resources of the given type
     *
     * @param FetchRequestInterface $request
     *
     * @return ResourceInterface[]
     */
    public function findResources(FetchRequestInterface $request): array
    {
        return [$this->jsonApi()->resource($request->type(), $request->id())];
    }

    /**
     * Creates a single resource
     *
     * @param SaveRequestInterface $request
     * @return ResourceInterface
     */
    public function createResource(SaveRequestInterface $request): ResourceInterface
    {
        return $request->document()->data()->first();
    }

    /**
     * Patches a single resource
     *
     * @param SaveRequestInterface $request
     * @return ResourceInterface
     */
    public function patchResource(SaveRequestInterface $request): ResourceInterface
    {
        return $request->document()->data()->first();
    }

    /**
     * Deletes a resource by type and id
     *
     * @param AdvancedJsonApiRequestInterface $request
     *
     * @return void
     */
    public function deleteResource(AdvancedJsonApiRequestInterface $request)
    {

    }
}
