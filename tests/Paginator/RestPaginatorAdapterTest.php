<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperRestTest\Paginator;

use Matryoshka\Model\ModelStubInterface;
use Matryoshka\Model\ResultSet\ArrayObjectResultSet;
use Matryoshka\Model\Wrapper\Rest\Criteria\FindAllCriteria;
use Matryoshka\Model\Wrapper\Rest\Paginator\RestPaginatorAdapter;
use Matryoshka\Model\Wrapper\Rest\RestClient;
use Matryoshka\Model\Exception\RuntimeException;
use Matryoshka\Service\Api\Profiler\ProfilerInterface;
use Matryoshka\Service\Api\Response\Decoder\DecoderInterface;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Class RestPaginatorAdapterTest
 */
class RestPaginatorAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RestPaginatorAdapter
     */
    protected $paginatorAdapter;

    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelMock;

    protected $criteria;

    public function setUp()
    {
        /** @var $httpClient Request */
        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch', 'getResponse'])
            ->getMock();

        $client = new RestClient('test', $httpClient);
        /** @var $profiler ProfilerInterface */
        $profiler = $this->getMock('Matryoshka\Service\Api\Profiler\ProfilerInterface');

        $client->setRequestFormat('application/json');
        $client->setProfiler($profiler);

        $this->restClient = $client;

        $modelMock = $this->getMockBuilder('\Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['find', 'getDataGateway'])
            ->getMockForAbstractClass();

        $this->modelMock = $modelMock;

        $criteria = new FindAllCriteria();
        $this->criteria = $criteria;

        /** @var $modelMock ModelStubInterface */
        $this->paginatorAdapter = new RestPaginatorAdapter($modelMock, $criteria);
    }

    public function testGetSetTotalItemsParamName()
    {
        $this->assertSame('total_items', $this->paginatorAdapter->getTotalItemsParamName()); // Default value
        $this->assertSame($this->paginatorAdapter, $this->paginatorAdapter->setTotalItemsParamName('foo'));
        $this->assertSame('foo', $this->paginatorAdapter->getTotalItemsParamName());
    }

    /**
     * @expectedException \Matryoshka\Model\Exception\InvalidArgumentException
     */
    public function testGetItemsShouldThrowExceptionWhenInvalidDataGateway()
    {
        $this->modelMock->expects($this->any())->method('getDataGateway')->willReturn(new \stdClass());
        $this->paginatorAdapter->getItems(null, null);
    }

    public function testPreload()
    {
        $resultSet = new ArrayObjectResultSet();

        $this->modelMock->expects($this->atLeastOnce())->method('getDataGateway')->willReturn($this->restClient);

        $count = 0;
        $this->modelMock->expects($this->atMost(1))
            ->method('find')
            ->with(
                $this->callback(
                    function ($c) use (&$count) {
                        // Due to phpunit bug, this callback is called multiple time
                        $count++;
                        if ($count > 1) {
                            return true;
                        }
                        $ok = $c instanceof FindAllCriteria &&
                              $c->getLimit() === null &&
                              $c->getOffset() === null &&
                              $c->getPage() == 1;
                        $c->setLimit(10);
                        $c->setOffset(0);
                        return $ok;
                    }
                )
            )
            ->willReturn($resultSet);

        $decoderMock = $this->getMockBuilder('\Matryoshka\Service\Api\Response\Decoder\DecoderInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getLastResponseData'])
            ->getMockForAbstractClass();

        $decoderMock->expects($this->atMost(1))
            ->method('getLastPayload')
            ->willReturn(['total_items' => 100]);

        /** @var $decoderMock DecoderInterface */
        $this->restClient->setResponseDecoder($decoderMock);

        $this->paginatorAdapter->preload(); // Assume no-cache, expected methods will be called
        // at most one time

        $this->assertSame($resultSet, $this->paginatorAdapter->getItems(0, 10)); // Assume cache hint

        $this->assertCount(100, $this->paginatorAdapter);
    }


    public function testGetItems()
    {
        $resultSet = new ArrayObjectResultSet();

        $this->modelMock->expects($this->atLeastOnce())->method('getDataGateway')->willReturn($this->restClient);

        $count = 0;
        $this->modelMock->expects($this->atMost(1))
            ->method('find')
            ->with(
                $this->callback(
                    function ($c) use (&$count) {
                        // Due to phpunit bug, this callback is called multiple time
                        $count++;
                        if ($count > 1) {
                            return true;
                        }
                        $ok = $c instanceof FindAllCriteria && $c->getLimit() === 20 && $c->getOffset() === 30;
                        return $ok;
                    }
                )
            )
            ->willReturn($resultSet);

        $decoderMock = $this->getMockBuilder('\Matryoshka\Service\Api\Response\Decoder\DecoderInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getLastResponseData'])
            ->getMockForAbstractClass();

        $decoderMock->expects($this->atMost(1))
            ->method('getLastPayload')
            ->willReturn(['total_items' => 1000]);

        /** @var $decoderMock DecoderInterface */
        $this->restClient->setResponseDecoder($decoderMock);

        $this->assertSame($resultSet, $this->paginatorAdapter->getItems(30, 20));

        $this->assertCount(1000, $this->paginatorAdapter);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCountShouldThrowExceptionWhenCalledPriorToPreload()
    {
        $this->paginatorAdapter->count();
    }
}
