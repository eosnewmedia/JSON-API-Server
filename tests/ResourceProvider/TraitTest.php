<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\ResourceProvider;

use Enm\JsonApi\Server\Model\Request\SaveRelationshipRequestInterface;
use Enm\JsonApi\Server\Tests\Mock\FetchOnlyMockResourceProvider;
use Enm\JsonApi\Server\Tests\Mock\MockResourceProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class TraitTest extends TestCase
{
    /**
     * @expectedException \Enm\JsonApi\Exception\BadRequestException
     */
    public function testInvalidModificationRequest()
    {
        $provider = new MockResourceProvider();
        /** @var SaveRelationshipRequestInterface $request */
        $request = $this->createConfiguredMock(
            SaveRelationshipRequestInterface::class,
            [
                'requestedAdd' => false,
                'requestedRemove' => false,
                'requestedReplace' => false,
            ]
        );
        $provider->modifyRelationship($request);
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\NotAllowedException
     */
    public function testModificationRequestNotAllowed()
    {
        $provider = new FetchOnlyMockResourceProvider();
        /** @var SaveRelationshipRequestInterface $request */
        $request = $this->createMock(SaveRelationshipRequestInterface::class);
        $provider->modifyRelationship($request);
    }
}
