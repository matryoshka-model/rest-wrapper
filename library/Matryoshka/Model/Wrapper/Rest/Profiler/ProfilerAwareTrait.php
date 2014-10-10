<?php
namespace Matryoshka\Model\Wrapper\Rest\Profiler;

/**
 * Class ProfilerAwareTrait
 *
 * @package Matryoshka\Model\Wrapper\Rest\Profiler
 */
trait ProfilerAwareTrait
{
    /**
     * ATTRIBUTE
     ******************************************************************************************************************/

    /**
     * @var ProfilerInterface
     */
    protected $profiler;

    /**
     * METHOD
     ******************************************************************************************************************/

    /**
     * @return ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * @param ProfilerInterface $profiler
     * @return $this
     */
    public function setProfiler(ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }
} 