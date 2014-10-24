<?php
namespace MatryoshkaModelWrapperRestTest\Profiler;

use Matryoshka\Model\Wrapper\Rest\Profiler\Profiler;
use Zend\Http\Request;
use Zend\Http\Response;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Matryoshka\Model\Wrapper\Rest\Profiler\Profiler
     */
    protected $profiler;

    public function setUp()
    {
        $this->profiler = new Profiler();
    }

    public function testProfilerStart()
    {
        $request = new Request();
        $this->assertSame($this->profiler, $this->profiler->profilerStart($request));
    }

    public function testProfilerStop()
    {
        $response = new Response();
        $request = new Request();
        $this->profiler->profilerStart($request);
        $this->assertSame($this->profiler, $this->profiler->profilerFinish($response));
    }

    public function testGetProfiles()
    {
        $this->assertEmpty($this->profiler->getProfiles());
    }
}
