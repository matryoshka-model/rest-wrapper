<?php
namespace MatryoshkaModelWrapperRestTest\Profiler;

use Matryoshka\Model\Wrapper\Rest\Profiler\Profiler;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Http\Client;

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
        $this->assertSame($this->profiler, $this->profiler->profilerStart());
    }

    public function testProfilerStop()
    {
        $client = new Client();
        $this->profiler->profilerStart();
        $this->assertSame($this->profiler, $this->profiler->profilerFinish($client));
    }

    public function testGetProfiles()
    {
        $this->assertEmpty($this->profiler->getProfiles());
    }
}
