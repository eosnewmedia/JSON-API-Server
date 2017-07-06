<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\JsonApiException;
use Enm\JsonApi\Model\Request\JsonApiRequest;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class SimpleMainRequestProvider extends JsonApiRequest implements MainRequestProviderInterface
{
    use MainRequestProviderTrait;

    /**
     * @param RequestInterface $request
     * @param string $apiPrefix
     * @throws JsonApiException
     */
    public function __construct(RequestInterface $request, string $apiPrefix = '')
    {
        $this->mainRequest = $request;
        $this->apiPrefix = $apiPrefix;

        list($type, $id) = $this->pathSegments();

        parent::__construct($type, $id);
    }
}
