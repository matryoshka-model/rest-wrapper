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
//             [$response3, $result3],
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
}
