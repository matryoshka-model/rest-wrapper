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
            ['get', [null, ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            ['get', [11, ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            ['head', [null, ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            ['options', [['test' => 'test']], '{"test": "test"}', 'application/json',  'json'],
            ['patch', [null, ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            ['post', [['test' => 'test'], ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            ['put', [null, ['test' => 'test'], ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            ['delete', [null, ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
//             ['get', ['id', ['test' => 'test']], '<test>test</test>', 'application/xml', 'json'],
        ];
    }

    /**
     * @return array
     */
    public function providerServiceRequestResponseException()
    {
        //Params: [$method, $params, $responseContent, $responseContentType, $responseStatusCode, $format, $exceptionType]

        $apiProblemResponse = '{
    "type": "http://example.com/probs/out-of-credit",
    "title": "You do not have enough credit.",
    "detail": "Your current balance is 30, but that costs 50.",
    "instance": "http://example.net/account/12345/msgs/abc",
    "balance": 30,
    "accounts": ["http://example.net/account/12345",
                 "http://example.net/account/67890"]
   }';

        return [
            //Bad Responses
            ['get', [null], '{"test": "test"}', 'application/json', 500, 'json'],
            ['post', [['test' => 'test']], '{"test": "test"}', 'application/json', 500, 'json'],
            ['delete', ['id'], '', 'application/json', 500, 'json'],
            ['get', [null], $apiProblemResponse, 'application/problem+json', 500, 'json', '\Matryoshka\Model\Wrapper\Rest\Exception\ApiProblem\DomainException'],
            ['get', ['id'], '', 'application/problem+json', 502, 'json', '\Matryoshka\Model\Wrapper\Rest\Exception\ApiProblem\DomainException'],
            ['get', ['id'], '', 'application/invalid-response-format', 502, 'json', '\Matryoshka\Model\Wrapper\Rest\Exception\InvalidFormatException'],
            ['get', ['id'], null, '', 502, 'json'], //content-type missing

            //Bad requests
            ['post', [['test' => 'test']], '', 'application/json', 502, 'invalid-request-format', '\Matryoshka\Model\Wrapper\Rest\Exception\InvalidFormatException'],
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


    public function testGetLastResponseData()
    {
        $this->assertNull($this->restClient->getLastResponseData());
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
    public function testHttpMethod($method, array $params, $contentResponse, $responseContentType, $typeResponse)
    {
        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch', 'getResponse'])
            ->getMock();

        $response = new Response();
        $response->setContent($contentResponse);
        $response->getHeaders()->addHeaderLine('Content-Type', $responseContentType);

        $httpClient->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue($response));

        $httpClient->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $client = new RestClient('test', $httpClient);
        $profiler = $this->getMock('Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerInterface');

        $client->setRequestFormat($typeResponse);
        $client->setProfiler($profiler);

        call_user_func_array([$client, $method], $params);

        $this->assertInternalType('array', call_user_func_array([$client, $method], $params));
        $this->assertInstanceof('\Zend\Http\Request', $client->getLastRequest());
        $this->assertSame($response, $client->getLastResponse());
        $this->assertSame($client->getResponseDecoder()->getLastPayload(), $client->getLastResponseData());

        //Test query params
        if (
            (in_array($method, ['delete', 'get', 'head', 'post']) && isset($params[1]) && $query = $params[1])
            ||
            (in_array($method, ['options']) && isset($params[0]) && $query = $params[0])
            ||
            (in_array($method, ['patch', 'put']) && isset($params[2]) && $query = $params[2])
         ) {
            $request = $client->getLastRequest();
            foreach ($query as $key => $value) {
                $this->assertEquals($value, $request->getQuery($key));
            }
        }
    }


    /**
     * @param string $method
     * @param array $params
     * @param string $responseContent
     * @param string $format
     * @param string $exceptionType
     * @dataProvider providerServiceRequestResponseException
     */
    public function testHttpMethodRequestResponseException(
        $method,
        array $params,
        $responseContent,
        $responseContentType,
        $responseStatusCode,
        $format,
        $exceptionType = '\Matryoshka\Model\Wrapper\Rest\Exception\InvalidResponseException')
    {
        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch', 'getResponse'])
            ->getMock();

        $response = new Response();
        $response->setContent($responseContent);
        if ($responseContentType) {
            $response->getHeaders()->addHeaderLine('Content-Type: ' . $responseContentType);
        }
        $response->setStatusCode($responseStatusCode);


        $httpClient->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue($response));

        $httpClient->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $client = new RestClient('test', $httpClient);
        $client->setRequestFormat($format);

        $this->setExpectedException($exceptionType);
        call_user_func_array([$client, $method], $params);
    }


    public function testPrepareRequestShouldThrowExceptionOnInvalidFormat()
    {
        $this->restClient->setRequestFormat('invalid format');

        $this->setExpectedException('\Matryoshka\Model\Wrapper\Rest\Exception\InvalidFormatException');
        $this->restClient->prepareRequest('post', null, ['foo' => 'baz']);
    }

}
