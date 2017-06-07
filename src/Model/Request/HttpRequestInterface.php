<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface HttpRequestInterface
{
    /**
     * @return Request
     */
    public function getHttpRequest(): Request;
}
