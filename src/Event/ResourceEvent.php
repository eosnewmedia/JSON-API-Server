<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Event;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Model\Resource\ResourceInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class ResourceEvent extends Event
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @var FetchInterface
     */
    private $apiRequest;

    /**
     * @param ResourceInterface $resource
     * @param FetchInterface $apiRequest
     */
    public function __construct(ResourceInterface $resource, FetchInterface $apiRequest)
    {
        $this->resource = $resource;
        $this->apiRequest = $apiRequest;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }

    /**
     * @return FetchInterface
     */
    public function getApiRequest(): FetchInterface
    {
        return $this->apiRequest;
    }
}
