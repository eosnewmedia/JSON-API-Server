<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Model\Request\JsonApiRequestInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface AdvancedJsonApiRequestInterface extends JsonApiRequestInterface
{
    /**
     * @return RequestInterface
     */
    public function originalHttpRequest(): RequestInterface;

    /**
     * Indicates if a relationship is requested
     *
     * @return bool
     */
    public function isMainRequestRelationshipRequest(): bool;

    /**
     * Indicates if the response for this request should only contain identifiers or the full resource objects
     *
     * @return bool
     */
    public function onlyIdentifiers(): bool;

    /**
     * Returns the name of the requested relationship if the main request is a relationship request
     *
     * @return string
     */
    public function relationship(): string;
}
