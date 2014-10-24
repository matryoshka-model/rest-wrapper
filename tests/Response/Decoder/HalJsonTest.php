<?php
namespace MatryoshkaModelWrapperRestTest\Response\Decoder;

use Matryoshka\Model\Wrapper\Rest\Response\Decoder\HalJson;
use Zend\Http\Headers;
use Zend\Http\Response;
use Zend\Json\Json;

class HalJsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HalJson
     */
    protected $decoder;

    public function setUp()
    {
        $this->decoder = new HalJson();
    }


    /**
     * @return array
     */
    public function decoderDataProvider()
    {
        $response1 = new Response();
        $response1->setContent('{"test":"test","test1":"test1"}');
        $response1->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $result1 = ['test' => 'test', 'test1' => 'test1'];

        $response2 = new Response();
        $response2->setContent('{"_links":{"self":{"href":"http://test/user"}},"_embedded":{"users":[{"test":"test","test1":"test1","_links":{"self":{"href":"http://test/user/1"}}},{"test":"foo","test1":"baz","_links":{"self":{"href":"http://test/user/2"}}}]}}');
        $response2->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
        $result2 = [
            ['test' => 'test', 'test1' => 'test1'],
            ['test' => 'foo', 'test1' => 'baz'],
        ];

        return [
            [$response1, $result1],
            [$response2, $result2],
        ];
    }


    /**
     * @param Response $response
     * @param string $result
     * @dataProvider decoderDataProvider
     */
    public function testDecode(Response $response, array $result)
    {
        $this->assertEquals($result, $this->decoder->decode($response));
        $this->assertEquals(Json::decode($response->getBody(), Json::TYPE_ARRAY), $this->decoder->getRawDecodedData());
    }
}
