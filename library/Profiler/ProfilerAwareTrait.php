<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Profiler;

/**
 * Class ProfilerAwareTrait
 *
 * @package Matryoshka\Model\Wrapper\Rest\Profiler
 */
trait ProfilerAwareTrait
{
    /**
     * @var ProfilerInterface
     */
    protected $profiler;

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
