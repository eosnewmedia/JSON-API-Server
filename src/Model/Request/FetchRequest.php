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
    use HttpRequestTrait;

    /**
     * @var bool
     */
    private $mainRequest;

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
            $this->httpRequest = $request;
            $this->mainRequest = $mainRequest;
            $this->apiPrefix = $apiPrefix;

            $this->validateContentType();

            list($type, $id, $relationship, $relationshipName) = explode(
                '/',
                $this->getNormalizedPath()
            );

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
        return $this->mainRequest;
    }

    /**
     * @return string
     */
    public function requestedRelationship(): string
    {
        return $this->requestedRelationship;
    }

    /**
     * Indicates if the response for this request should contain only identifiers or full resources
     *
     * @return bool
     */
    public function shouldContainOnlyIdentifiers(): bool
    {
        return $this->onlyIdentifiers;
    }

    /**
     * Indicates if resources fetched by this request should provide their relationships even if their attributes are
     * not requested (for example with sub request for "include" parameter).
     *
     * @return bool
     */
    public function shouldProvideRelationships(): bool
    {
        return !$this->shouldContainOnlyIdentifiers() || count($this->includes()) > 0;
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
    public function shouldContainAttribute(string $type, string $name): bool
    {
        if (!array_key_exists($type, $this->fields())) {
            return true;
        }

        return in_array($name, $this->fields()[$type], true);
    }

    /**
     * If a relationship is requested via "include" parameter, this method must
     * return true, otherwise false. If a relationship should be included, the
     * related resource should contain more than a resource identifier.
     *
     * @param string $name
     *
     * @return bool
     */
    public function shouldIncludeRelationship(string $name): bool
    {
        return in_array($name, $this->includedRelationships, true);
    }

    public function httpRequest(): RequestInterface
    {
        return $this->httpRequest;
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
        $uri = $this->httpRequest()->getUri();
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
            $this->httpRequest()->withUri($uri->withQuery(http_build_query($query->all()))),
            false,
            $this->apiPrefix
        );

        $subRequest->onlyIdentifiers = !$this->shouldIncludeRelationship($relationship);

        return $subRequest;
    }

    /**
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function buildFromQuery()
    {
        parse_str($this->httpRequest()->getUri()->getQuery(), $uriQuery);
        $query = new KeyValueCollection($uriQuery);

        if ($query->has('include')) {
            $includes = explode(',', $query->getRequired('include'));
            foreach ($includes as $include) {
                $this->include($include);
                if (strpos($include, '.') === false) {
                    $this->includedRelationships[] = $include;
                }
            }
        }

        if ($query->has('fields')) {
            foreach ((array)$query->getRequired('fields') as $type => $field) {
                $this->field($type, $field);
            }
        }

        if ($query->has('filter')) {
            $this->filter()->merge((array)$query->getRequired('filter'));
        }

        if ($query->has('page')) {
            $this->pagination()->merge((array)$query->getRequired('page'));
        }

        if ($query->has('sort')) {
            $this->sorting()->merge((array)$query->getRequired('sort'));
        }
    }
}
