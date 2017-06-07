<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\InvalidRequestException;
use Enm\JsonApi\Model\Common\KeyValueCollection;
use Enm\JsonApi\Model\Common\KeyValueCollectionInterface;
use Enm\JsonApi\Model\Resource\JsonResource;
use Enm\JsonApi\Model\Resource\Relationship\ToManyRelationship;
use Enm\JsonApi\Model\Resource\Relationship\ToOneRelationship;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class SaveResourceRequest extends AbstractHttpRequest implements SaveResourceInterface
{
    /**
     * @var bool
     */
    private $containsId = false;

    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @param Request|null $request
     * @throws \Exception
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        try {
            $body = $this->createBodyCollection(json_decode($this->getHttpRequest()->getContent(), true));
            $this->resource = $this->buildJsonResource($body->createSubCollection('data'));
        } catch (\InvalidArgumentException $e) {
            throw new InvalidRequestException($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function containsId(): bool
    {
        return $this->containsId;
    }

    /**
     * @return ResourceInterface
     */
    public function resource(): ResourceInterface
    {
        return $this->resource;
    }

    /**
     * Creates a new fetch request from the current http request
     *
     * @param bool $shouldReturnFullResource
     * @return FetchInterface
     * @throws \Exception
     */
    public function createFetch(bool $shouldReturnFullResource = true): FetchInterface
    {
        return new FetchRequest($this->getHttpRequest(), $shouldReturnFullResource);
    }

    /**
     * @param ResourceInterface $resource
     * @param KeyValueCollectionInterface $relationships
     *
     * @return void
     * @throws \Exception
     */
    private function buildRelationships(ResourceInterface $resource, KeyValueCollectionInterface $relationships)
    {
        foreach ($relationships->all() as $name => $relationship) {
            if (!array_key_exists('data', $relationship)) {
                throw new InvalidRequestException('Invalid relationship requested');
            }

            if (!is_array($relationship['data'])) {
                $resource->relationships()->set(new ToOneRelationship($name));
                continue;
            }

            $data = $relationships->createSubCollection($name)->createSubCollection('data');

            if ($data->isEmpty()) {
                $resource->relationships()->set(new ToManyRelationship($name));
                continue;
            }

            if ($data->has('type')) {
                $resource->relationships()->set(
                    new ToOneRelationship(
                        $name,
                        $this->buildJsonResource($data)
                    )
                );
                continue;
            }

            $identifiers = [];
            foreach ($data->all() as $identifierData) {
                $identifiers[] = $this->buildJsonResource(
                    new KeyValueCollection($identifierData)
                );
            }
            $resource->relationships()->set(
                new ToManyRelationship($name, $identifiers)
            );
        }
    }

    /**
     * @param KeyValueCollectionInterface $collection
     *
     * @return JsonResource
     * @throws \Exception
     */
    private function buildJsonResource(KeyValueCollectionInterface $collection): JsonResource
    {
        if ($collection->getOptional('id', '') !== '') {
            $this->containsId = true;
            if (!is_string($collection->getRequired('id'))) {
                throw new \InvalidArgumentException('Id have to be a string!');
            }
        } else {
            $collection->set('id', $this->generateId());
        }

        $resource = new JsonResource(
            $collection->getRequired('type'),
            $collection->getRequired('id'),
            $collection->createSubCollection('attributes', false)->all()
        );

        $resource->metaInformations()->mergeCollection($collection->createSubCollection('meta', false));

        $this->buildRelationships(
            $resource,
            $collection->createSubCollection('relationships', false)
        );

        return $resource;
    }

    /**
     * @param mixed $body
     * @return KeyValueCollectionInterface
     * @throws \InvalidArgumentException
     */
    protected function createBodyCollection($body): KeyValueCollectionInterface
    {
        if (!is_array($body)) {
            throw new \InvalidArgumentException();
        }

        $bodyCollection = new KeyValueCollection($body);

        $data = $bodyCollection->createSubCollection('data');
        if (!is_string($data->getRequired('type'))) {
            throw new \InvalidArgumentException();
        }

        return $bodyCollection;
    }

    /**
     * @return string
     * @see http://www.php.net/manual/en/function.uniqid.php#94959
     */
    protected function generateId(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            random_int(0, 0xffff), random_int(0, 0xffff),

            // 16 bits for "time_mid"
            random_int(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            random_int(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            random_int(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }
}
