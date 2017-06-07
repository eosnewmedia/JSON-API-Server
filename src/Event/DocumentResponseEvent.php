<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Event;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class DocumentResponseEvent extends DocumentEvent
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @param DocumentInterface $document
     * @param Request $request
     * @param Response $response
     */
    public function __construct(DocumentInterface $document, Request $request, Response $response)
    {
        parent::__construct($document, $request);
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
