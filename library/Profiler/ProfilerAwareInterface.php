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
 * Interface ProfilerAwareInterface
 *
 * @package Matryoshka\Model\Wrapper\Rest\Profiler
 */
interface ProfilerAwareInterface
{
    /**
     * @return ProfilerInterface
     */
    public function getProfiler();

    /**
     * @param ProfilerInterface $profiler
     */
    public function setProfiler(ProfilerInterface $profiler);
}
