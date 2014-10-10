<?php
namespace Matryoshka\Model\Wrapper\Rest\Profiler;

/**
 * Interface ProfilerAwareInterface
 *
 * @package Matryoshka\Model\Wrapper\Rest\Profiler
 */
interface ProfilerAwareInterface
{
    /**
     * METHOD
     ******************************************************************************************************************/

    /**
     * @return ProfilerInterface
     */
    public function getProfiler();

    /**
     * @param ProfilerInterface $profiler
     */
    public function setProfiler(ProfilerInterface $profiler);
}
