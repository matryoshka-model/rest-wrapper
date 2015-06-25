<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperRestTest\Criteria;

use Matryoshka\Model\ModelStubInterface;
use Matryoshka\Model\Wrapper\Rest\Criteria\FindAllCriteria;
use Zend\Http\Response;

/**
 * Class FindAllCriteriaTest
 */
class FindAllCriteriaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FindAllCriteria
     */
    protected $criteria;

    public function setUp()
    {
        $this->criteria = new FindAllCriteria();
    }

    public function testGetSetOffset()
    {
        $this->criteria->setLimit(10);
        $this->assertNull($this->criteria->getOffset()); //Test default

        $offset = 20;
        $this->assertSame($this->criteria, $this->criteria->setOffset($offset));
        $this->assertAttributeEquals($offset, 'offset', $this->criteria);
        $this->assertSame($offset, $this->criteria->getOffset());

        $offset = null;
        $this->assertSame($this->criteria, $this->criteria->setOffset($offset));
        $this->assertAttributeEquals($offset, 'offset', $this->criteria);
        $this->assertSame($offset, $this->criteria->getOffset());
    }

    public function testGetSetPage()
    {
        $this->assertNull($this->criteria->getPage()); //Test default

        $page = 10;
        $this->assertSame($this->criteria, $this->criteria->setPage($page));
        $this->assertAttributeEquals($page, 'page', $this->criteria);
        $this->assertAttributeEquals(null, 'offset', $this->criteria);
        $this->assertSame($page, $this->criteria->getPage());

        $page = null;
        $this->assertSame($this->criteria, $this->criteria->setPage($page));
        $this->assertAttributeEquals($page, 'page', $this->criteria);
        $this->assertAttributeEquals(null, 'offset', $this->criteria);
        $this->assertSame($page, $this->criteria->getPage());
    }

    public function testGetSetPageParam()
    {
        $this->assertEquals('page', $this->criteria->getPageParam()); //test default
        $this->assertSame($this->criteria, $this->criteria->setPageParam('foo'));
        $this->assertEquals('foo', $this->criteria->getPageParam());
    }


    public function testGetSetPageSizeParam()
    {
        $this->assertEquals('page_size', $this->criteria->getPageSizeParam()); //test default
        $this->assertSame($this->criteria, $this->criteria->setPageSizeParam('foo'));
        $this->assertEquals('foo', $this->criteria->getPageSizeParam());
    }


    public function testGetSetTotalItemsParam()
    {
        $this->assertEquals('total_items', $this->criteria->getTotalItemsParam()); //test default
        $this->assertSame($this->criteria, $this->criteria->setTotalItemsParam('foo'));
        $this->assertEquals('foo', $this->criteria->getTotalItemsParam());
    }

    public function testGetSetQuery()
    {
        $this->assertEquals([], $this->criteria->getQuery()); //test default
        $this->assertSame($this->criteria, $this->criteria->setQuery(['foo']));
        $this->assertEquals(['foo'], $this->criteria->getQuery());
    }

    public function testOffsetShouldThrowExcepetionWithoutLimit()
    {
        $this->setExpectedException('\Matryoshka\Model\Exception\RuntimeException');
        $this->criteria->setOffset(33);
    }

    /**
     * @return array
     */
    public function applyDataProvider()
    {
        return [
            [[['test' => 'test']]],
            [[['test' => 'test2']], ['foo' => 'bar']],
            [[['test' => 'test3']], ['foo' => 'bar'], 10],
            [[['test' => 'test4']], ['foo' => 'bar'], 10, 4],
            [[['test' => 'test5']], ['foo' => 'bar'], 10, null, 33],
            [[['test' => 'test5']], ['page' => 4], 2, null, 33],
        ];
    }

    /**
     *
     * @param array $data
     * @param string $query
     * @param string $limit
     * @param string $page
     * @param string $offset
     * @dataProvider applyDataProvider
     */
    public function testApply(array $data, $query = null, $limit = null, $page = null, $offset = null)
    {
        $expectedQuery = [];

        if ($query) {
            $expectedQuery = array_merge($expectedQuery, $query);
            $this->assertSame($this->criteria, $this->criteria->setQuery($query));
        }

        if ($limit) {
            $expectedQuery[$this->criteria->getPageSizeParam()] = $limit;
            $this->assertSame($this->criteria, $this->criteria->setLimit($limit));
        }

        if ($offset) {
            $page = ceil($offset / $limit) + 1;
            $this->assertSame($this->criteria, $this->criteria->setOffset($offset));
        }

        if ($page) {
            $expectedQuery[$this->criteria->getPageParam()] = $page;
            if (!$offset) {
                $this->assertSame($this->criteria, $this->criteria->setPage($page));
            }
        }

        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getLastResponseData'])
            ->getMock();

        $restClient->expects($this->atLeastOnce())
            ->method('getLastResponseData')
            ->will(
                $this->returnValue(
                    [
                        "page_count" => $page,
                        "page_size" => $limit,
                        "total_items" => 1
                    ]
                )
            );


        $restClient->expects($this->atLeastOnce())
            ->method('get')
            ->with($this->isType('null'), $this->equalTo($expectedQuery))
            ->will($this->returnValue($data));

        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['getDataGateway'])
            ->getMock();

        $model->expects($this->atLeastOnce())
            ->method('getDataGateway')
            ->will($this->returnValue($restClient));

        /** @var $model ModelStubInterface */
        $this->assertEquals($data, $this->criteria->apply($model));
    }

    public function testGetPaginatorAdapter()
    {
        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $model ModelStubInterface */
        $paginator = $this->criteria->getPaginatorAdapter($model);
        $this->assertInstanceOf('\Matryoshka\Model\Wrapper\Rest\Paginator\RestPaginatorAdapter', $paginator);
        $this->assertSame($this->criteria->getTotalItemsParam(), $paginator->getTotalItemsParamName());
    }
}
