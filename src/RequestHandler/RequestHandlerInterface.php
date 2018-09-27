<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Model\Request\RequestInterface;
use Enm\JsonApi\Model\Response\ResponseInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface RequestHandlerInterface
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function fetchResource(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function fetchResources(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function fetchRelationship(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function createResource(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function patchResource(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function deleteResource(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function addRelatedResources(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function replaceRelatedResources(RequestInterface $request): ResponseInterface;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function removeRelatedResources(RequestInterface $request): ResponseInterface;
}
