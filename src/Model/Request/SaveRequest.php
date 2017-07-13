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
class SaveRequest extends \Enm\JsonApi\Model\Request\SaveRequest implements SaveRequestInterface
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
     * @return FetchRequestInterface
     * @throws JsonApiException
     */
    public function fetch(): FetchRequestInterface
    {
        return new FetchRequest($this->originalHttpRequest(), false, $this->apiPrefix);
    }
}
