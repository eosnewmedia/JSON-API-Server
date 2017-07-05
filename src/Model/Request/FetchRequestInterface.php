<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface FetchRequestInterface extends \Enm\JsonApi\Model\Request\FetchRequestInterface, HttpRequestInterface
{
    /**
     * @return bool
     */
    public function isMainRequest(): bool;

    /**
     * @return string
     */
    public function requestedRelationship(): string;

    /**
     * Indicates if the response for this request should contain only identifiers or full resources
     *
     * @return bool
     */
    public function shouldContainOnlyIdentifiers(): bool;

    /**
     * Indicates if resources fetched by this request should provide their relationships even if their attributes are
     * not requested (for example with sub request for "include" parameter).
     *
     * @return bool
     */
    public function shouldProvideRelationships(): bool;

    /**
     * If a "field" parameter is available and does not contains the attribute
     * name, this method must return false.
     *
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    public function shouldContainAttribute(string $type, string $name): bool;

    /**
     * If a relationship is requested via "include" parameter, this method must
     * return true, otherwise false. If a relationship should be included, the
     * related resource should contain more than a resource identifier.
     *
     * @param string $name
     *
     * @return bool
     */
    public function shouldIncludeRelationship(string $name): bool;

    /**
     * Creates a new fetch resource request for the given relationship.
     * A sub request does not contain pagination and sorting.
     *
     * @param string $relationship
     * @param boolean $keepFilters
     *
     * @return FetchRequestInterface
     */
    public function subRequest(string $relationship, $keepFilters = false): FetchRequestInterface;
}
