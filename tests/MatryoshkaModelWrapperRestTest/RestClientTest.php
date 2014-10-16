<?php
namespace MatryoshkaModelWrapperRestTest;

use Matryoshka\Model\Wrapper\Rest\RestClient;
use Zend\Http\Request;
use Zend\Http\Response;

class RestClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * @return array
     */
    public function providerServiceResponse()
    {
        return [
            ['get', [null, ['test' => 'test']], '{"test": "test"}', 'json'],
            ['head', [null, ['test' => 'test']], '{"test": "test"}', 'json'],
            ['options', [['test' => 'test']], '{"test": "test"}', 'json'],
            ['patch', [null, ['test' => 'test']], '{"test": "test"}', 'json'],
            ['post', [['test' => 'test'], ['test' => 'test']], '{"test": "test"}', 'json'],
            ['put', [null, ['test' => 'test'], ['test' => 'test']], '{"test": "test"}', 'json'],
            ['delete', [null, ['test' => 'test']], '{"test": "test"}', 'json'],
            ['get', [null, ['test' => 'test']], '<test>test</test>', 'xml']
        ];
    }

    /**
     * @return array
     */
    public function providerServiceRequestResponseException()
    {
        return [
            ['get', [null, ['test' => 'test']], '{"test": "test"}', 'error'],
            ['post', [['test' => 'test'], ['test' => 'test']], '{"test": "test"}', 'error'],
        ];
    }

    /**
     * @return array
     */
    public function providerServiceResponseCodeException()
    {
        return [
            ['get', [null, ['test' => 'test']], '{"test": "test"}', 'json']
        ];
    }

    public function setUp()
    {
        $this->restClient = new RestClient('resource');
    }

    public function testGetResourceName()
    {
        $this->assertSame('resource', $this->restClient->getResourceName());
    }

    public function testGetSetUriResourceStrategy()
    {
        $this->assertInstanceOf('Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\UriResourceStrategyInterface', $this->restClient->getUriResourceStrategy());
        $strategy = $this->getMock('Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\UriResourceStrategyInterface');
        $this->restClient->setUriResourceStrategy($strategy);
        $this->assertSame($strategy, $this->restClient->getUriResourceStrategy());
    }

    public function testGetSetValidStatusCodes()
    {
        $this->assertSame($this->restClient, $this->restClient->setValidStatusCodes([200, 201]));
        $this->assertCount(2, $this->restClient->getValidStatusCodes());
    }

    public function testGetSetRequestFormat()
    {
        $this->assertSame($this->restClient, $this->restClient->setRequestFormat('json'));
        $this->assertSame('json', $this->restClient->getRequestFormat());
    }

    public function testGetSetResponseFormat()
    {
        $this->assertSame($this->restClient, $this->restClient->setResponseFormat('json'));
        $this->assertSame('json', $this->restClient->getResponseFormat());
    }

    public function testGetSetReturnType()
    {
        $this->assertSame($this->restClient, $this->restClient->setReturnType(1));
        $this->assertSame(1, $this->restClient->getReturnType());
    }

    public function testGetSetBaseRequest()
    {
        $request = new Request();
        $this->assertSame($this->restClient, $this->restClient->setBaseRequest($request));
        $this->assertSame($request, $this->restClient->getBaseRequest());
    }

    public function testGetLastRequest()
    {
        $this->assertNull($this->restClient->getLastRequest());
    }

    public function testGetLastResponse()
    {
        $this->assertNull($this->restClient->getLastResponse());
    }

    public function testCloneBaseRequest()
    {
        $request = $this->restClient->getBaseRequest();
        $cloneRequest = $this->restClient->cloneBaseRequest();
        $this->assertInstanceOf('Zend\Http\Request', $cloneRequest);
        $this->assertNotSame($request, $cloneRequest);
    }

    /**
     * @param $contentResponse
     * @dataProvider providerServiceResponse
     */
    public function testHttpMethod($method, array $params, $contentResponse, $typeResponse)
    {
        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch', 'getResponse'])
            ->getMock();

        $response = new Response();
        $response->setContent($contentResponse);

        $httpClient->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue($response));

        $httpClient->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $client = new RestClient('test', $httpClient);
        $client->setResponseFormat($typeResponse);
        $profiler = $this->getMock('Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerInterface');

        $client->setRequestFormat($typeResponse);
        $client->setProfiler($profiler);

        call_user_func_array([$client, $method], $params);

        $this->assertInternalType('array', call_user_func_array([$client, $method], $params));
    }

    /**
     * @param $contentResponse
     * @dataProvider providerServiceRequestResponseException
     * @expectedException \Matryoshka\Model\Wrapper\Rest\Exception\InvalidFormatOutputException
     */
    public function testHttpMethodRequestResponseException($method, array $params, $contentResponse, $typeResponse)
    {
        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch', 'getResponse'])
            ->getMock();

        $response = new Response();
        $response->setContent($contentResponse);

        $httpClient->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue($response));

        $httpClient->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $client = new RestClient('test', $httpClient);
        $client->setResponseFormat($typeResponse);
        $profiler = $this->getMock('Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerInterface');

        $client->setRequestFormat($typeResponse);
        $client->setProfiler($profiler);

        call_user_func_array([$client, $method], $params);
    }

    /**
     * @param $contentResponse
     * @dataProvider providerServiceResponseCodeException
     * @expectedException \Matryoshka\Model\Wrapper\Rest\Exception\InvalidResponseException
     */
    public function testHttpMethodResponseCodeException($method, array $params, $contentResponse, $typeResponse)
    {
        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch', 'getResponse'])
            ->getMock();

        $response = new Response();
        $response->setContent($contentResponse);
        $response->setStatusCode(500);
        $response->setContent('{"detail":"mock error","status":500,"type":"mock type","title":"mock title"}');

        $httpClient->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue($response));

        $httpClient->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $client = new RestClient('test', $httpClient);
        $client->setResponseFormat($typeResponse);
        $profiler = $this->getMock('Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerInterface');

        $client->setRequestFormat($typeResponse);
        $client->setReturnType(0);
        $client->setProfiler($profiler);

        call_user_func_array([$client, $method], $params);
    }

} 