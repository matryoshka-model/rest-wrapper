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
 * Interface HttpProfilerInterface
 *
 * @package Matryoshka\Model\Wrapper\Rest\Profiler
 */
interface ProfilerInterface
{
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