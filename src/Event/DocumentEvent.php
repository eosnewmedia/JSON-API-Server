<?php
declare(strict_types = 1);

namespace Enm\JsonApi\Server\Event;

use Enm\JsonApi\Model\Document\DocumentInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class DocumentEvent extends Event
{
    /**
     * @var DocumentInterface
     */
    private $document;
    
    /**
     * @var Request
     */
    private $request;
    
    /**
     * @param DocumentInterface $document
     * @param Request $request
     */
    public function __construct(DocumentInterface $document, Request $request)
    {
        $this->document = $document;
        $this->request  = $request;
    }
    
    /**
     * @return DocumentInterface
     */
    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }
    
    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
