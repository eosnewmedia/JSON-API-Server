<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

use Enm\JsonApi\Exception\UnsupportedMediaTypeException;
use Enm\JsonApi\Server\JsonApi;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
abstract class AbstractHttpRequest implements HttpRequestInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request|null $request
     *
     * @throws UnsupportedMediaTypeException
     */
    public function __construct(Request $request = null)
    {
        if (!$request instanceof Request) {
            $request = Request::createFromGlobals();
        }
        $this->request = $request;

        if ($this->request->headers->get('Content-Type') !== JsonApi::CONTENT_TYPE) {
            throw new UnsupportedMediaTypeException('Invalid content type requested!');
        }
    }

    /**
     * @return Request
     */
    public function getHttpRequest(): Request
    {
        return $this->request;
    }
}
