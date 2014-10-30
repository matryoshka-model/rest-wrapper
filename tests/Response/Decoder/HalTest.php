<?php
namespace MatryoshkaModelWrapperRestTest\Response\Decoder;

use Zend\Http\Headers;
use Zend\Http\Response;
use Zend\Json\Json;
use Matryoshka\Model\Wrapper\Rest\Response\Decoder\Hal;

class HalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Hal
     */
    protected $decoder;

    public function setUp()
    {
        $this->decoder = new Hal();
    }


    /**
     * @return array
     */
    public function decoderDataProvider()
    {
        //Test simple Json response
        $response1 = new Response();
        $response1->setContent('{"test":"test","test1":"test1"}');
        $response1->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $result1 = ['test' => 'test', 'test1' => 'test1'];

        //Test HAL json
        $response2 = new Response();
        $response2->setContent('{"_links":{"self":{"href":"http://test/user"}},"_embedded":{"users":[{"test":"test","test1":"test1","_links":{"self":{"href":"http://test/user/1"}}},{"test":"foo","test1":"baz","_links":{"self":{"href":"http://test/user/2"}}}]}}');
        $response2->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
        $result2 = [
            ['test' => 'test', 'test1' => 'test1'],
            ['test' => 'foo', 'test1' => 'baz'],
        ];

        //Test empty string
        $response3 = new Response();
        $response3->setContent('');
        $response3->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $result3 = [];

        //Test empty Json list
        $response4 = new Response();
        $response4->setContent('[]');
        $response4->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $result4 = [];


        //Test empty HAL json
        $response5 = new Response();
        $response5->setContent('{"_links":{"self":{"href":"http:\/\/example.net\/user"}},"_embedded":{"users":[]},"page_count":0,"page_size":10,"total_items":0}');
        $response5->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
        $result5 = [];


//         $response3 = new Response();
//         $response3->setContent('<resource rel="self" href="/" xmlns:ex="http://example.org/rels/">
//   <link rel="ex:look" href="/bleh" />
//   <link rel="ex:search" href="/search?term={searchTerm}" />
//   <resource rel="ex:member" name="1" href="/foo">
//     <link rel="ex:created_by" href="/some_dude" />
//     <example>bar</example>
//     <resource rel="ex:status" href="/foo;status">
//       <some_property>disabled</some_property>
//     </resource>
//   </resource>
//   <resource rel="ex:member" name="2" href="/bar">
//     <link rel="ex:created_by" href="http://example.com/some_other_guy" />
//     <example>bar</example>
//     <resource rel="ex:status" href="/foo;status">
//       <some_property>disabled</some_property>
//     </resource>
//   </resource>
//   <link rel="ex:widget" name="1" href="/chunky" />
//   <link rel="ex:widget" name="2" href="/bacon" />
// </resource>');
//         $response3->getHeaders()->addHeaderLine('Content-Type', 'application/hal+xml');
//         $result3 = [
//             ['test' => 'test', 'test1' => 'test1'],
//             ['test' => 'foo', 'test1' => 'baz'],
//         ];

        return [
            [$response1, $result1],
            [$response2, $result2],
            [$response3, $result3],
            [$response4, $result4],
            [$response5, $result5],
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
        $this->assertEquals(Json::decode($response->getBody(), Json::TYPE_ARRAY), $this->decoder->getLastPayload());
    }

    /**
     * @expectedException \Matryoshka\Model\Wrapper\Rest\Exception\InvalidResponseException
     */
    public function testDecodeShouldThrowExceptionWhenContentTypeMissing()
    {
        $response = new Response();
        $this->decoder->decode($response);
    }

    /**
     * @expectedException \Matryoshka\Model\Wrapper\Rest\Exception\InvalidFormatException
     */
    public function testDecodeShouldThrowExceptionWhenInvalidResponseFormat()
    {
        $response = new Response();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/invalid');
        $this->decoder->decode($response);
    }

    public function testGetLastPayLoad()
    {
        $this->assertNull($this->decoder->getLastPayload());
    }

    public function testGetAcceptHeader()
    {
        $accept = $this->decoder->getAcceptHeader();
        $this->assertInstanceOf('\Zend\Http\Header\Accept', $accept);
        $this->assertTrue((bool) $accept->match('application/json'));
    }
}
