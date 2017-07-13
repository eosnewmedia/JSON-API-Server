<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Exception\JsonApiException;
use Enm\JsonApi\Model\Common\KeyValueCollection;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class FetchRequest extends \Enm\JsonApi\Model\Request\FetchRequest implements FetchRequestInterface
{
    use AdvancedJsonApiRequestTrait;

    /**
     * @var bool
     */
    private $isMainRequest;

    /**
     * @var string
     */
    private $requestedRelationship;

    /**
     * @var array
     */
    private $includedRelationships = [];

    /**
     * @var bool
     */
    private $onlyIdentifiers = false;

    /**
     * @param RequestInterface $request
     * @param bool $mainRequest
     * @param string $apiPrefix
     * @throws JsonApiException
     */
    public function __construct(RequestInterface $request, bool $mainRequest = true, string $apiPrefix = '')
    {
        try {
            $this->mainRequest = $request;
            $this->isMainRequest = $mainRequest;
            $this->apiPrefix = $apiPrefix;

            $this->validateContentType();

            list($type, $id, $relationship, $relationshipName) = $this->pathSegments();
            parent::__construct((string)$type, (string)$id);

            // parse relationship/related request
            if ((string)$relationship === 'relationship' && (string)$relationshipName !== '') {
                $this->onlyIdentifiers = true;
            } elseif ((string)$relationship !== '' && (string)$relationshipName === '') {
                $relationshipName = $relationship;
            }
            $this->requestedRelationship = (string)$relationshipName;


            $this->buildFromQuery();
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestException($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function isMainRequest(): bool
    {
        return $this->isMainRequest;
    }

    /**
     * @return string
     */
    public function relationship(): string
    {
        return $this->requestedRelationship;
    }

    /**
     * Indicates if the response for this request should contain attributes and relationships
     *
     * @return bool
     */
    public function requestedResourceBody(): bool
    {
        return !$this->onlyIdentifiers;
    }

    /**
     * If a "field" parameter is available and does not contains the attribute
     * name, this method must return false.
     *
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    public function requestedField(string $type, string $name): bool
    {
        if (!array_key_exists($type, $this->fields())) {
            return true;
        }

        return in_array($name, $this->fields()[$type], true);
    }

    /**
     * Indicates if resources fetched by this request should provide their relationships even if their attributes are
     * not requested (for example with sub request for "include" parameter).
     *
     * @return bool
     */
    public function requestedRelationships(): bool
    {
        return $this->requestedResourceBody() || count($this->includes()) > 0;
    }

    /**
     * @param string $relationship
     * @return bool
     */
    public function requestedInclude(string $relationship): bool
    {
        return in_array($relationship, $this->includedRelationships, true);
    }

    /**
     * Creates a new fetch resource request for the given relationship.
     * A sub request does not contain pagination and sorting.
     *
     * @param string $relationship
     * @param boolean $keepFilters
     *
     * @return FetchRequestInterface
     * @throws \Exception
     */
    public function subRequest(string $relationship, $keepFilters = false): FetchRequestInterface
    {
        $uri = $this->mainHttpRequest()->getUri();
        parse_str($uri->getQuery(), $originalQuery);

        $query = new KeyValueCollection($originalQuery);
        $query->remove('include');
        $query->remove('sort');
        $query->remove('page');
        $query->remove('filter');

        if ($keepFilters) {
            $query->set('filter', $this->filter()->all());
        }

        $includes = [];
        foreach ($this->includes() as $include) {
            if (strpos($include, '.') !== false && strpos($include, $relationship . '.') === 0) {
                $includes[] = explode('.', $include, 2)[1];
            }
        }

        if (count($includes) > 0) {
            $query->set('include', implode(',', $includes));
        }

        $subRequest = new self(
            $this->mainHttpRequest()->withUri($uri->withQuery(http_build_query($query->all()))),
            false,
            $this->apiPrefix
        );

        $subRequest->mainRequest = $this->mainHttpRequest();
        $subRequest->onlyIdentifiers = !$this->requestedInclude($relationship);

        return $subRequest;
    }

    /**
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function buildFromQuery()
    {
        parse_str($this->mainHttpRequest()->getUri()->getQuery(), $uriQuery);
        $query = new KeyValueCollection($uriQuery);

        if ($query->has('include')) {
            if (!is_string($query->getRequired('include'))) {
                throw new \InvalidArgumentException('Invalid include parameter given!');
            }

            $includes = explode(',', $query->getRequired('include'));
            foreach ($includes as $include) {
                $this->include($include);
                if (strpos($include, '.') === false) {
                    $this->includedRelationships[] = $include;
                }
            }
        }

        if ($query->has('fields')) {
            if (!is_array($query->getRequired('fields'))) {
                throw new \InvalidArgumentException('Invalid fields parameter given!');
            }
            foreach ((array)$query->getRequired('fields') as $type => $fields) {
                foreach (explode(',', $fields) as $field) {
                    $this->field($type, $field);
                }
            }
        }

        if ($query->has('filter')) {
            if (!is_array($query->getRequired('filter'))) {
                throw new \InvalidArgumentException('Invalid filter parameter given!');
            }
            $this->filter()->merge((array)$query->getRequired('filter'));
        }

        if ($query->has('page')) {
            if (!is_array($query->getRequired('page'))) {
                throw new \InvalidArgumentException('Invalid page parameter given!');
            }
            $this->pagination()->merge((array)$query->getRequired('page'));
        }

        if ($query->has('sort')) {
            if (!is_string($query->getRequired('sort'))) {
                throw new \InvalidArgumentException('Invalid sort parameter given!');
            }
            foreach (explode(',', $query->getRequired('sort')) as $field) {
                $direction = self::ORDER_ASC;
                if (strpos($field, '-') === 0) {
                    $field = substr($field, 1);
                    $direction = self::ORDER_DESC;
                }
                $this->sorting()->set($field, $direction);
            }
        }
    }
}
