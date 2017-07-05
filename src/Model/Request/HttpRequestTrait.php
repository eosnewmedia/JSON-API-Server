<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\UnsupportedMediaTypeException;
use Enm\JsonApi\JsonApiInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait HttpRequestTrait
{

    /**
     * @var RequestInterface
     */
    private $httpRequest;

    private $apiPrefix;

    /**
     * @return RequestInterface
     */
    public function httpRequest(): RequestInterface
    {
        return $this->httpRequest;
    }

    /**
     * @throws UnsupportedMediaTypeException
     */
    protected function validateContentType()
    {
        $contentTypeHeader = $this->httpRequest()->getHeader('Content-Type');

        $isAvailable = count($contentTypeHeader) !== 0;
        if (!$isAvailable || strpos($contentTypeHeader[0], JsonApiInterface::CONTENT_TYPE) === false) {
            throw new UnsupportedMediaTypeException('Invalid content type: ' . $contentTypeHeader[0]);
        }
    }

    /**
     * Returns the requested path in the format "{type}/{id}/({relationship}|relationship/{relationship})"
     *
     * @return string
     */
    protected function getNormalizedPath(): string
    {
        return trim(
            ltrim(
                trim(
                    $this->httpRequest()->getUri()->getPath(),
                    '/'
                ),
                trim(
                    $this->apiPrefix,
                    '/'
                )
            ),
            '/'
        );
    }
}
