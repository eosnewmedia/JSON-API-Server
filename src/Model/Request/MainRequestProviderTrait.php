<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\UnsupportedMediaTypeException;
use Enm\JsonApi\JsonApiInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait MainRequestProviderTrait
{

    /**
     * @var RequestInterface
     */
    private $mainRequest;

    private $apiPrefix;

    /**
     * @return RequestInterface
     */
    public function mainRequest(): RequestInterface
    {
        return $this->mainRequest;
    }

    /**
     * @throws UnsupportedMediaTypeException
     */
    protected function validateContentType()
    {
        $contentTypeHeader = $this->mainRequest()->getHeader('Content-Type');

        $isAvailable = count($contentTypeHeader) !== 0;
        if (!$isAvailable || strpos($contentTypeHeader[0], JsonApiInterface::CONTENT_TYPE) === false) {
            throw new UnsupportedMediaTypeException('Invalid content type: ' . $contentTypeHeader[0]);
        }
    }

    /**
     * Returns an array with four path segments (type, id, relationship constant, relationship name)
     *
     * @return array
     */
    protected function pathSegments(): array
    {
        $segments = explode(
            '/',
            trim(
                ltrim(
                    trim(
                        $this->mainRequest()->getUri()->getPath(),
                        '/'
                    ),
                    trim(
                        $this->apiPrefix,
                        '/'
                    )
                ),
                '/'
            )
        );

        // fill missing segments
        while (count($segments) < 4) {
            $segments[] = '';
        }

        return $segments;
    }
}
