<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperRestTest\Criteria\ActiveRecord;

use Matryoshka\Model\ModelStubInterface;
use Matryoshka\Model\Wrapper\Rest\Criteria\ActiveRecord\ActiveRecordCriteria;
use Matryoshka\Service\Api\Exception\InvalidResponseException;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Class ActiveRecordCriteriaTest
 */
class ActiveRecordCriteriaTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ActiveRecordCriteria
     */
    protected $criteria;

    public function setUp()
    {
        $this->criteria = new ActiveRecordCriteria();
        $this->criteria->setId(1);
    }

    public function testApplyDelete()
    {
        $response = new Response();
        $response->setStatusCode(200);

        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'getLastResponse'])
            ->getMock();

        $restClient->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(true));

        $restClient->expects($this->any())
            ->method('getLastResponse')
            ->will($this->returnValue($response));

        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['getDataGateway'])
            ->getMock();

        $model->expects($this->any())
            ->method('getDataGateway')
            ->will($this->returnValue($restClient));

        /** @var $model ModelStubInterface */
        $this->assertSame(1, $this->criteria->applyDelete($model));

        $response->setStatusCode(404);

        $this->assertNull($this->criteria->applyDelete($model));
    }

    public function testApply()
    {
        $data = ['test' => 'test'];
        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $restClient->expects($this->any())
            ->method('get')
            ->will($this->returnValue($data));

        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['getDataGateway'])
            ->getMock();

        $model->expects($this->any())
            ->method('getDataGateway')
            ->will($this->returnValue($restClient));

        /** @var $model ModelStubInterface */
        $this->assertEquals([$data], $this->criteria->apply($model));

        // Empty result test
        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $restClient->expects($this->any())
            ->method('get')
            ->will($this->returnValue([]));

        /** @var $model \PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['getDataGateway'])
            ->getMock();

        $model->expects($this->any())
            ->method('getDataGateway')
            ->will($this->returnValue($restClient));

        /** @var $model ModelStubInterface */
        $this->assertEquals([], $this->criteria->apply($model));
    }

    public function testApplyNotFound()
    {
        // not found item 404
        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $restClient->expects($this->any())
            ->method('get')
            ->will($this->throwException(new InvalidResponseException('Not found', 404)));


        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['getDataGateway'])
            ->getMock();

        $model->expects($this->any())
            ->method('getDataGateway')
            ->will($this->returnValue($restClient));

        /** @var $model ModelStubInterface */
        $this->assertEquals([], $this->criteria->apply($model));

        // other exceptions proxy test

        // not found item 404
        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $restClient->expects($this->any())
            ->method('get')
            ->will($this->throwException(new InvalidResponseException('Other error', 401)));

        /** @var $model \PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['getDataGateway'])
            ->getMock();

        $model->expects($this->any())
            ->method('getDataGateway')
            ->will($this->returnValue($restClient));

        $this->setExpectedException('\Matryoshka\Service\Api\Exception\InvalidResponseException');
        /** @var $model ModelStubInterface */
        $this->criteria->apply($model);
    }

    public function testApplyWrite()
    {
        $request = new Request();

        $response = new Response();
        $response->setStatusCode(200);

        $restClient = $this->getMockBuilder('Matryoshka\Model\Wrapper\Rest\RestClient')
            ->disableOriginalConstructor()
            ->setMethods(['put', 'post', 'getLastResponse', 'cloneBaseRequest'])
            ->getMock();

        $restClient->expects($this->any())
            ->method('cloneBaseRequest')
            ->will($this->returnValue($request));

        $restClient->expects($this->any())
            ->method('put')
            ->will($this->returnValue(['test' => 'test']));

        $restClient->expects($this->any())
            ->method('post')
            ->will($this->returnValue(['test' => 'test']));

        $restClient->expects($this->any())
            ->method('getLastResponse')
            ->will($this->returnValue($response));

        $model = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['getDataGateway'])
            ->getMock();

        $model->expects($this->any())
            ->method('getDataGateway')
            ->will($this->returnValue($restClient));

        $array = ['test' => 'test'];

        /** @var $model ModelStubInterface */
        $this->assertSame(1, $this->criteria->applyWrite($model, $array));
        $criteria = new ActiveRecordCriteria();
        $this->assertSame(1, $criteria->applyWrite($model, $array));

        $response->setStatusCode(204);
        $this->assertSame(0, $this->criteria->applyWrite($model, $array));


        $response->setStatusCode(404);
        $this->assertNull($this->criteria->applyWrite($model, $array));
    }
}
