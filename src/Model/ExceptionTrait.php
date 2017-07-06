<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model;

use Enm\JsonApi\Exception\BadRequestException;
use Enm\JsonApi\Exception\ResourceNotFoundException;
use Enm\JsonApi\Exception\UnsupportedTypeException;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
trait ExceptionTrait
{
    /**
     * @param string $type
     * @param string $id
     *
     * @throws ResourceNotFoundException
     */
    protected function throwResourceNotFound(string $type, string $id)
    {
        throw new ResourceNotFoundException($type, $id);
    }

    /**
     * @param string $type
     *
     * @throws UnsupportedTypeException
     */
    protected function throwUnsupportedType(string $type)
    {
        throw new UnsupportedTypeException($type);
    }

    /**
     * @param string $message
     * @throws BadRequestException
     */
    protected function throwBadRequest(string $message = 'Invalid request!')
    {
        throw new BadRequestException($message);
    }
}
