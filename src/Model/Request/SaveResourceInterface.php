<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Model\Resource\ResourceInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface SaveResourceInterface extends HttpRequestInterface
{
    /**
     * @return bool
     */
    public function containsId(): bool;

    /**
     * @return ResourceInterface
     */
    public function resource(): ResourceInterface;

    /**
     * Creates a new fetch request from the current http request
     * @param bool $shouldReturnFullResource
     * @return FetchInterface
     */
    public function createFetch(bool $shouldReturnFullResource = true): FetchInterface;
}
