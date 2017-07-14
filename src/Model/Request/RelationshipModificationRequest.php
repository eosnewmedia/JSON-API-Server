<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Exception\JsonApiException;
use Enm\JsonApi\Serializer\DocumentDeserializerInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class RelationshipModificationRequest extends \Enm\JsonApi\Model\Request\RelationshipModificationRequest implements SaveRelationshipRequestInterface
{
    use AdvancedJsonApiRequestTrait;

    /**
     * @param RequestInterface $request
     * @param DocumentDeserializerInterface $documentDeserializer
     * @param string $apiPrefix
     * @throws JsonApiException
     */
    public function __construct(
        RequestInterface $request,
        DocumentDeserializerInterface $documentDeserializer,
        string $apiPrefix = ''
    ) {
        $this->originalHttpRequest = $request;
        $this->apiPrefix = $apiPrefix;

        $this->validateContentType();

        list($type, $id) = $this->pathSegments();

        if (!$this->isMainRequestRelationshipRequest()) {
            throw new BadRequestException('Missing relationship which should became modified!');
        }

        if (!$this->onlyIdentifiers()) {
            throw new BadRequestException('Related resources can not be modified by a relationship update!');
        }

        $body = (string)$request->getBody();
        $documentData = $body !== '' ? json_decode($body, true) : [];
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('Invalid json given!');
        }

        $document = $documentDeserializer->deserializeDocument($documentData);

        parent::__construct($type, $id, $document);
    }

    /**
     * @return bool
     */
    public function requestedReplace(): bool
    {
        return strtoupper($this->originalHttpRequest()->getMethod()) === 'PATCH';
    }

    /**
     * @return bool
     */
    public function requestedAdd(): bool
    {
        return strtoupper($this->originalHttpRequest()->getMethod()) === 'POST';
    }

    /**
     * @return bool
     */
    public function requestedRemove(): bool
    {
        return strtoupper($this->originalHttpRequest()->getMethod()) === 'DELETE';
    }

    /**
     * Create a new fetch request from current request
     *
     * @param string $id
     *
     * @return FetchRequestInterface
     * @throws JsonApiException|\InvalidArgumentException
     */
    public function fetch(string $id = ''): FetchRequestInterface
    {
        if ($id !== '' && $this->id() !== $id) {
            throw new \InvalidArgumentException('Invalid id given!');
        }

        return new FetchRequest($this->originalHttpRequest()->withMethod('GET'), false, $this->apiPrefix);
    }
}
