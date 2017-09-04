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
class SaveSingleResourceRequest extends \Enm\JsonApi\Model\Request\SaveSingleResourceRequest implements SaveRequestInterface
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

        $documentData = json_decode((string)$request->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('Invalid json given!');
        }

        $document = $documentDeserializer->deserializeDocument($documentData);

        parent::__construct($document, $id);

        if ($document->data()->first()->type() !== $type) {
            throw new BadRequestException('Requested resource type does not match given resource type!');
        }
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
        if ($id === '' && !$this->containsId()) {
            throw new \InvalidArgumentException('An id is required to fetch a resource!');
        }

        if ($id !== '' && $this->containsId() && $this->id() !== $id) {
            throw new \InvalidArgumentException('Invalid id given!');
        }

        $originalHttpRequest = $this->originalHttpRequest();
        if (!$this->containsId()) {
            $originalHttpRequest = $this->originalHttpRequest()->withUri(
                $this->originalHttpRequest()->getUri()->withPath(
                    $this->originalHttpRequest()->getUri()->getPath() . '/' . $id
                )
            );
        }

        return new FetchRequest($originalHttpRequest, false, $this->apiPrefix);
    }
}
