<?php
declare(strict_types=1);

namespace Enm\JsonApi\Server\Tests\Model\Request;

use Enm\JsonApi\Server\Model\Request\FetchInterface;
use Enm\JsonApi\Server\Model\Request\FetchRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class FetchRequestTest extends TestCase
{

    public function testFetchRequest()
    {
        $http = new Request(
            [
                'fields' => ['test' => 'attrA,attrB'],
                'include' => 'relationA,relationA.relationB,relationA.relationC',
                'filter' => ['test' => 'test'],
                'page' => ['cursor' => 1],
                'sort' => 'test_a,-test_b',
            ]
        );
        $http->headers->set('Content-Type', 'application/vnd.api+json');

        $request = new FetchRequest($http);

        self::assertTrue(
            $request->shouldIncludeRelationship('relationA')
        );
        self::assertFalse(
            $request->shouldIncludeRelationship('test')
        );
        self::assertTrue(
            $request->shouldContainAttribute('test', 'attrA')
        );
        self::assertTrue(
            $request->shouldContainAttribute('test', 'attrB')
        );
        self::assertFalse(
            $request->shouldContainAttribute('test', 'attr')
        );
        self::assertInstanceOf(
            FetchInterface::class,
            $request->subRequest('relationA')
        );
        self::assertEquals('test_a', $request->sorting()[0]->field());
        self::assertTrue($request->sorting()[0]->ascending());
        self::assertEquals('test_b', $request->sorting()[1]->field());
        self::assertFalse($request->sorting()[1]->ascending());
        self::assertEquals(1, $request->pagination()->getRequired('cursor'));
        self::assertEquals('test', $request->filters()->getRequired('test'));

        $subRequest = $request->subRequest('relationA');
        self::assertFalse(
            $subRequest->shouldIncludeRelationship('test')
        );
        self::assertTrue(
            $subRequest->shouldContainAttribute('test', 'attrA')
        );
        self::assertTrue(
            $subRequest->shouldContainAttribute('test', 'attrB')
        );
        self::assertTrue(
            $subRequest->shouldContainAttribute('abc', 'attrB')
        );
        self::assertFalse(
            $subRequest->shouldContainAttribute('test', 'attr')
        );
    }

    public function testFetchRequestManipulation()
    {
        $http = new Request();
        $http->headers->set('Content-Type', 'application/vnd.api+json');

        $request = new FetchRequest($http);
        self::assertFalse($request->shouldIncludeRelationship('test'));
        $request->addInclude('test');
        self::assertTrue($request->shouldIncludeRelationship('test'));
    }

    public function testFetchRequestWithoutHttpRequest()
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/vnd.api+json';

        $request = new FetchRequest();
        self::assertFalse($request->shouldIncludeRelationship('test'));
        self::assertTrue($request->shouldContainAttribute('test',
            'attr'));
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\UnsupportedMediaTypeException
     */
    public function testFetchRequestWithInvalidContentType()
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

        new FetchRequest();
    }

    /**
     * @expectedException \Enm\JsonApi\Exception\InvalidRequestException
     */
    public function testFetchRequestWithInvalidFields()
    {
        $http = new Request();
        $http->query->set('fields', 'test,attr');
        $http->headers->set('Content-Type', 'application/vnd.api+json');

        $request = new FetchRequest($http);
        $request->shouldContainAttribute('test', 'attr');
    }

    public function testFetchRequestCallInvalidRelationship()
    {
        $http = new Request();
        $http->headers->set('Content-Type', 'application/vnd.api+json');

        $request = new FetchRequest($http);
        $subRequest = $request->subRequest('test');

        self::assertFalse($subRequest->shouldIncludeRelationship('test'));
    }

    public function testFetchRequestWithSubInclude()
    {
        $http = new Request(
            [
                'include' => 'relation.subRelation',
            ]
        );
        $http->headers->set('Content-Type', 'application/vnd.api+json');

        $request = new FetchRequest($http);
        self::assertFalse($request->shouldIncludeRelationship('relation'));
        $subRequest = $request->subRequest('relation');
        self::assertTrue($subRequest->shouldIncludeRelationship('subRelation'));
    }


    public function testFetchRequestShouldContainRelationships()
    {
        $http = new Request(
            [
                'include' => 'relation.subRelation',
            ]
        );
        $http->headers->set('Content-Type', 'application/vnd.api+json');

        $request = new FetchRequest($http);

        self::assertTrue($request->shouldContainRelationships());
        self::assertTrue($request->subRequest('relation')
            ->shouldContainRelationships());
    }


    public function testFetchRequestShouldContainsAttribute()
    {
        $http = new Request(
            [
                'fields' => ['test' => 'test'],
                'include' => 'relation.subRelation',
            ]
        );
        $http->headers->set('Content-Type', 'application/vnd.api+json');

        $request = new FetchRequest($http);

        self::assertTrue(
            $request->shouldContainAttribute('test', 'test')
        );
        self::assertFalse(
            $request->shouldContainAttribute('test', 'test2')
        );
        self::assertFalse(
            $request->subRequest('relation')
                ->shouldContainAttribute('test', 'test2')
        );
        self::assertTrue(
            $request->subRequest('relation')
                ->shouldContainAttribute('test', 'test')
        );
        self::assertFalse(
            $request->subRequest('relation')
                ->subRequest('subRelation')
                ->shouldContainAttribute('test', 'test2')
        );
    }
}
