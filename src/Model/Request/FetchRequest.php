<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\InvalidRequestException;
use Enm\JsonApi\Model\Common\KeyValueCollection;
use Enm\JsonApi\Model\Common\KeyValueCollectionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class FetchRequest extends AbstractHttpRequest implements FetchInterface
{
    /**
     * @var array
     */
    private $includes = [];

    /**
     * @var array
     */
    private $requestedRelationships = [];

    /**
     * @var bool
     */
    private $returnFullResource;

    /**
     * @var KeyValueCollectionInterface
     */
    private $filters;

    /**
     * @var KeyValueCollectionInterface
     */
    private $pagination;

    /**
     * @var array
     */
    private $sorting;

    /**
     * @param Request|null $request
     * @param  bool $shouldReturnFullResource
     *
     * @throws \Exception
     */
    public function __construct(Request $request = null, bool $shouldReturnFullResource = true)
    {
        parent::__construct($request);
        $this->returnFullResource = $shouldReturnFullResource;
        if ($this->getHttpRequest()->query->has('include')) {
            $include = (string)$this->getHttpRequest()->query->get('include');
            $requestedIncludes = explode(',', $include);
            foreach ($requestedIncludes as $requestedInclude) {
                $this->addInclude($requestedInclude);
            }
        }
    }

    /**
     * @param string $include
     *
     * @return FetchInterface
     */
    public function addInclude(string $include): FetchInterface
    {
        if (strpos($include, '.') === false) {
            if (!array_key_exists($include, $this->includes)) {
                $this->includes[$include] = [];
                $this->requestedRelationships[] = $include;
            }

            return $this;
        }

        list($rootInclude, $subInclude) = explode('.', $include, 2);
        $this->includes[$rootInclude][] = $subInclude;

        return $this;
    }

    /**
     * @return bool
     */
    public function shouldReturnFullResource(): bool
    {
        return $this->returnFullResource;
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return bool
     * @throws \Exception
     */
    public function shouldContainAttribute(string $type, string $name): bool
    {
        if (!$this->getHttpRequest()->query->has('fields')) {
            return true;
        }

        $fields = $this->getHttpRequest()->query->get('fields');
        if (!is_array($fields)) {
            throw new InvalidRequestException('Invalid parameter "fields"');
        }

        if (!array_key_exists($type, $fields)) {
            return true;
        }

        return in_array($name, explode(',', $fields[$type]), true);
    }

    /**
     * @return bool
     */
    public function shouldContainRelationships(): bool
    {
        return $this->shouldReturnFullResource() || count($this->includes) > 0;
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws \Exception
     */
    public function shouldIncludeRelationship(string $name): bool
    {
        return in_array($name, $this->requestedRelationships, true);
    }

    /**
     * Returns the url parameter "filter" as simple collection
     *
     * @return KeyValueCollectionInterface
     */
    public function filters(): KeyValueCollectionInterface
    {
        if (!$this->filters instanceof KeyValueCollectionInterface) {
            $this->filters = new KeyValueCollection(
                (array)$this->getHttpRequest()->query->get('filter', [])
            );
        }

        return $this->filters;
    }

    /**
     * Returns the url parameter "page" as simple collection
     *
     * @return KeyValueCollectionInterface
     */
    public function pagination(): KeyValueCollectionInterface
    {
        if (!$this->pagination instanceof KeyValueCollectionInterface) {
            $this->pagination = new KeyValueCollection(
                (array)$this->getHttpRequest()->query->get('page', [])
            );
        }

        return $this->pagination;
    }

    /**
     * Returns the url parameter "sort" as array of sort instructions
     *
     * @return SortInstruction[]
     */
    public function sorting(): array
    {
        if (!is_array($this->sorting)) {
            $this->sorting = [];
            if ($this->getHttpRequest()->query->has('sort')) {
                $sorting = explode(
                    ',',
                    (string)$this->getHttpRequest()->query->get('sort')
                );
                foreach ($sorting as $instruction) {
                    $this->sorting[] = new SortInstruction($instruction);
                }
            }
        }

        return $this->sorting;
    }

    /**
     * @param string $relationship
     * @param boolean $keepFilters
     *
     * @return FetchInterface
     * @throws \Exception
     */
    public function subRequest(string $relationship, $keepFilters = false): FetchInterface
    {
        $subRequest = $this->getHttpRequest()->duplicate();
        $subRequest->query->remove('include');
        $subRequest->query->remove('sort');
        $subRequest->query->remove('page');
        if (!$keepFilters) {
            $subRequest->query->remove('filter');
        }

        if (array_key_exists($relationship, $this->includes)) {
            $subRequest->query->set(
                'include',
                implode(',', $this->includes[$relationship])
            );
        }

        return new self(
            $subRequest,
            $this->shouldIncludeRelationship($relationship)
        );
    }
}
