<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Model\Request\JsonApiRequestInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface HttpRequestInterface extends JsonApiRequestInterface
{
    public function httpRequest(): RequestInterface;
}
