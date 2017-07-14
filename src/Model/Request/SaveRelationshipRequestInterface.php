<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface SaveRelationshipRequestInterface extends SaveRequestInterface
{
    /**
     * @return bool
     */
    public function requestedReplace(): bool;

    /**
     * @return bool
     */
    public function requestedAdd(): bool;

    /**
     * @return bool
     */
    public function requestedRemove(): bool;
}
