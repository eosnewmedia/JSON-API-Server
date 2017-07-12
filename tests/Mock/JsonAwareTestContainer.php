<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Mock;

use Enm\JsonApi\JsonApiInterface;
use Enm\JsonApi\Server\JsonApiAwareInterface;
use Enm\JsonApi\Server\JsonApiAwareTrait;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class JsonAwareTestContainer implements JsonApiAwareInterface
{
    use JsonApiAwareTrait;

    /**
     * @return JsonApiInterface
     */
    public function getJsonApi(): JsonApiInterface
    {
        return $this->jsonApi();
    }
}
