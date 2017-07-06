<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\RequestHandler;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Model\Document\DocumentInterface;
use Enm\JsonApi\Server\Model\Request\FetchMainRequestProviderInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait NoRelationshipsTrait
{
    /**
     * @param FetchMainRequestProviderInterface $request
     * @return DocumentInterface
     * @throws BadRequestException
     */
    public function fetchRelationship(FetchMainRequestProviderInterface $request): DocumentInterface
    {
        throw new BadRequestException('The requested relationship (' . $request->relationship() . ') does not exists.');
    }
}
