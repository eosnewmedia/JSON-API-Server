<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Exception\UnsupportedMediaTypeException;
use Enm\JsonApi\JsonApiInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait AdvancedJsonApiRequestTrait
{

    /**
     * @var RequestInterface
     */
    private $originalHttpRequest;

    /**
     * @var bool
     */
    private $onlyIdentifiers = false;

    /**
     * @var string
     */
    private $requestedRelationship = '';

    /**
     * @var string
     */
    private $apiPrefix = '';

    /**
     * @return RequestInterface
     */
    public function originalHttpRequest(): RequestInterface
    {
        return $this->originalHttpRequest;
    }

    /**
     * @return bool
     */
    public function isMainRequestRelationshipRequest(): bool
    {
        return $this->relationship() !== '';
    }

    /**
     * Indicates if the response for this request should only contain identifiers or the full resource objects
     *
     * @return bool
     */
    public function onlyIdentifiers(): bool
    {
        return $this->onlyIdentifiers;
    }

    /**
     * @return string
     */
    public function relationship(): string
    {
        return $this->requestedRelationship;
    }

    /**
     * @throws UnsupportedMediaTypeException
     */
    protected function validateContentType()
    {
        $contentTypeHeader = $this->originalHttpRequest()->getHeader('Content-Type');

        $isAvailable = count($contentTypeHeader) !== 0;
        if (!$isAvailable || strpos($contentTypeHeader[0], JsonApiInterface::CONTENT_TYPE) === false) {
            throw new UnsupportedMediaTypeException(
                'Invalid content type header: '
                . $this->originalHttpRequest()->getHeaderLine('Content-Type')
            );
        }
    }

    /**
     * Returns an array with four path segments (type, id, relationship constant, relationship name)
     * Set relationship and onlyIdentifiers automatically after parsing the path
     *
     * @return array
     * @throws BadRequestException
     */
    protected function pathSegments(): array
    {
        $path = trim($this->originalHttpRequest()->getUri()->getPath(), '/');

        preg_match(
            '/^(([a-zA-Z0-9\_\-\.\/]+.php)(\/)|)(' . trim($this->apiPrefix, '/') . ')([\/a-zA-Z0-9\_\-]+)$/',
            $path,
            $matches
        );
        $segments = explode('/', trim($matches[5], '/'));

        // fill missing segments
        while (count($segments) < 4) {
            $segments[] = '';
        }

        // parse relationship/related request
        if ((string)$segments[3] !== '') {
            if ((string)$segments[2] !== 'relationship') {
                throw new BadRequestException('Invalid relationship request!');
            }
            $this->onlyIdentifiers = true;
            $this->requestedRelationship = (string)$segments[3];
        } elseif ((string)$segments[2] !== '') {
            $this->requestedRelationship = (string)$segments[2];
        }

        return $segments;
    }
}
