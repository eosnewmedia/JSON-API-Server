<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Event;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class FetchEvent extends Event
{
    /**
     * @var FetchInterface
     */
    private $fetchRequest;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $id;

    /**
     * @param FetchInterface $fetchRequest
     * @param string $type
     * @param string $id
     */
    public function __construct(FetchInterface $fetchRequest, string $type, string $id = '')
    {
        $this->fetchRequest = $fetchRequest;
        $this->type = $type;
        $this->id = $id;
    }


    /**
     * @return FetchInterface
     */
    public function fetchRequest(): FetchInterface
    {
        return $this->fetchRequest;
    }

    /**
     * @return string
     */
    public function requestedType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isSingleResourceFetched(): bool
    {
        return $this->requestedId() !== '';
    }

    /**
     * @return string
     */
    public function requestedId(): string
    {
        return $this->id;
    }
}
