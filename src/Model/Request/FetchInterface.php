<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Model\Common\KeyValueCollectionInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface FetchInterface extends HttpRequestInterface
{
    /**
     * If only a relationships is requested and no full resource is needed
     * this method must return false, otherwise true.
     *
     * @return bool
     */
    public function shouldReturnFullResource(): bool;

    /**
     * Should relationships be contained in the resource response for the current request?
     * If a relationships is requested and no sub requests ("include") are
     * available this method must return false, otherwise true.
     *
     * @return bool
     */
    public function shouldContainRelationships(): bool;

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
     * Returns the url parameter "filter" as simple collection
     *
     * @return KeyValueCollectionInterface
     */
    public function filters(): KeyValueCollectionInterface;

    /**
     * Returns the url parameter "page" as simple collection
     *
     * @return KeyValueCollectionInterface
     */
    public function pagination(): KeyValueCollectionInterface;

    /**
     * Returns the url parameter "sort" as array of sort instructions
     *
     * @return SortInstruction[]
     */
    public function sorting(): array;

    /**
     * Creates a FetchInterface for the given relationship
     *
     * @param string $relationship
     * @param boolean $keepFilters
     *
     * @return FetchInterface
     */
    public function subRequest(string $relationship, $keepFilters = false): FetchInterface;

    /**
     * @param string $include
     *
     * @return FetchInterface
     */
    public function addInclude(string $include): FetchInterface;
}
