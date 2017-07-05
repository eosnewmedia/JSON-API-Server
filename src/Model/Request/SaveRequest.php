<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Exception\JsonApiException;
use Enm\JsonApi\Model\Factory\DocumentFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class SaveRequest extends \Enm\JsonApi\Model\Request\SaveRequest implements SaveRequestInterface
{
    use HttpRequestTrait;

    /**
     * @param RequestInterface $request
     * @param DocumentFactoryInterface $documentFactory
     * @param string $apiPrefix
     * @throws JsonApiException
     */
    public function __construct(
        RequestInterface $request,
        DocumentFactoryInterface $documentFactory,
        string $apiPrefix = ''
    ) {
        $this->httpRequest = $request;
        $this->apiPrefix = $apiPrefix;

        $this->validateContentType();

        list($type, $id) = explode('/', $this->getNormalizedPath());

        $documentData = json_decode((string)$request->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('Invalid json given!');
        }

        $document = $documentFactory->create($documentData);

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
        return new FetchRequest($this->httpRequest(), false, $this->apiPrefix);
    }
}
