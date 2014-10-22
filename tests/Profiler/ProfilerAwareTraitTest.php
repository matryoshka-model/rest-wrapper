<?php
namespace MatryoshkaModelWrapperRestTest\Profiler;


class ProfilerAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $mockTrait
     */
    protected $mockTrait;

    public function setUp()
    {
        $this->mockTrait = $this->getMockForTrait('Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerAwareTrait');
    }

    public function testProfilerAwareTraitGetSet()
    {
        $profiler = $this->getMock('Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerInterface');
        $this->mockTrait->setProfiler($profiler);
        $this->assertSame($profiler,   $this->mockTrait->getProfiler());
    }
} 