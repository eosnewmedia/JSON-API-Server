<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface SaveRequestInterface extends \Enm\JsonApi\Model\Request\SaveRequestInterface, AdvancedJsonApiRequestInterface
{
    /**
     * Create a new fetch request from current request
     *
     * @param string $id
     *
     * @return FetchRequestInterface
     */
    public function fetch(string $id = ''): FetchRequestInterface;
}
