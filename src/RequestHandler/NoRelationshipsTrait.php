<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequestInterface;
use Enm\JsonApi\Server\Model\Request\SaveRequestInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait NoRelationshipsTrait
{
    /**
     * @param FetchRequestInterface $request
     * @return DocumentInterface
     * @throws BadRequestException
     */
    public function fetchRelationship(FetchRequestInterface $request): DocumentInterface
    {
        throw new BadRequestException('The requested relationship (' . $request->relationship() . ') does not exists.');
    }

    /**
     * @param SaveRequestInterface $request
     * @return DocumentInterface
     * @throws BadRequestException
     */
    public function modifyRelationship(SaveRequestInterface $request): DocumentInterface
    {
        throw new BadRequestException(
            'The requested relationship (' . $request->relationship() . ') does not exists.'
        );
    }
}
