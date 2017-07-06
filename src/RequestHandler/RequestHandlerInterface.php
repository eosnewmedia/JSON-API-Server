<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchMainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\MainRequestProviderInterface;
use Enm\JsonApi\Server\Model\Request\SaveMainRequestProviderInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface RequestHandlerInterface
{
    /**
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function fetchResource(FetchMainRequestProviderInterface $request): DocumentInterface;

    /**
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function fetchResources(FetchMainRequestProviderInterface $request): DocumentInterface;

    /**
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function fetchRelationship(FetchMainRequestProviderInterface $request): DocumentInterface;

    /**
     * @param SaveMainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function saveResource(SaveMainRequestProviderInterface $request): DocumentInterface;

    /**
     * @param MainRequestProviderInterface $request
     * @return DocumentInterface
     */
    public function deleteResource(MainRequestProviderInterface $request): DocumentInterface;
}
