<?php
namespace MatryoshkaModelWrapperRestTest\Criteria;

use Zend\Http\Request;
use Zend\Http\Response;
use Matryoshka\Model\Wrapper\Rest\Criteria\FindAllCriteria;
use Zend\Paginator\Adapter\AdapterInterface;

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
        $this->criteria->offset(33);
    }

    public function applyDataProvider()
    {
        return [
          [ [['test' => 'test']] ],
          [ [['test' => 'test2']], ['foo' => 'bar'] ],
          [ [['test' => 'test3']], ['foo' => 'bar'], 10 ],
          [ [['test' => 'test4']], ['foo' => 'bar'], 10, 4 ],
          [ [['test' => 'test5']], ['foo' => 'bar'], 10, null, 33 ],
          [ [['test' => 'test5']], ['page' => 4], 2, null, 33 ],
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
            $this->assertSame($this->criteria, $this->criteria->limit($limit));
        }

        if ($offset) {
            $page = ceil($offset / $limit);
            $this->assertSame($this->criteria, $this->criteria->offset($offset));
        }

        if ($page) {
            $expectedQuery[$this->criteria->getPageParam()] = $page;
            if (!$offset) {
                $this->assertSame($this->criteria, $this->criteria->page($page));
            }
        }

        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

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

        $this->assertEquals($data, $this->criteria->apply($model));
    }

    public function testGetPaginatorAdapter()
    {
        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['getDataGateway'])
            ->getMock();

        $model->expects($this->atLeastOnce())
            ->method('getDataGateway')
            ->will($this->returnValue($restClient));

        $paginator = $this->criteria->getPaginatorAdapter($model);
        $this->assertInstanceOf('\Zend\Paginator\Adapter\AdapterInterface', $paginator);
    }


}
