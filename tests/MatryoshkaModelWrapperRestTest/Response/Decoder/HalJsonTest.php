<?php
namespace MatryoshkaModelWrapperRestTest\Response\Decoder;

use Matryoshka\Model\Wrapper\Rest\Response\Decoder\HalJson;
use Zend\Http\Headers;
use Zend\Http\Response;

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

    public function testDecode()
    {
        $contentRequest =  '{"test":"test","test1":"test1"}';
        $request = new Response();
        $request->setContent($contentRequest);
        $this->assertInternalType('array', $this->decoder->decode($request));

        $contentRequest =  '{"_links":{"self":{"href":"http://test/user"}},"_embedded":{"users":[{"test":"test","test1":"test1","_links":{"self":{"href":"http://test/user/1"}}},{"test":"test","test1":"test1","_links":{"self":{"href":"http://test/user/2"}}}]}}';
        $headers = new Headers();
        $headers->addHeaderLine('Content-Type', 'application/hal+json');
        $request->setHeaders($headers);
        $request->setContent($contentRequest);
        $this->assertInternalType('array', $this->decoder->decode($request));
    }
} 