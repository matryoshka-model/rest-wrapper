<?php
namespace Matryoshka\Model\Wrapper\Rest\Profiler;

/**
 * Interface HttpProfilerInterface
 *
 * @package Matryoshka\Model\Wrapper\Rest\Profiler
 */
interface ProfilerInterface
{
    /**
     * METHOD
     ******************************************************************************************************************/

    /**
     * @param $target
     * @return mixed
     */
    public function profilerStart($target);

    /**
     * @param $target
     * @return mixed>
     */
    public function profilerFinish($target);
}

