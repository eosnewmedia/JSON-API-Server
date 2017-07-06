<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Model\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
interface SaveMainRequestProviderInterface extends \Enm\JsonApi\Model\Request\SaveRequestInterface, MainRequestProviderInterface
{
    /**
     * Create a new fetch request from current request
     *
     * @return FetchMainRequestProviderInterface
     */
    public function fetch(): FetchMainRequestProviderInterface;
}
