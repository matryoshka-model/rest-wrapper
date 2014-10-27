<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Profiler;

use Zend\Http\Client;
/**
 * Interface HttpProfilerInterface
 *
 * @package Matryoshka\Model\Wrapper\Rest\Profiler
 */
interface ProfilerInterface
{
    /**
     * @return $this
     */
    public function profilerStart();

    /**
     * @param Client $target
     * @return $this
     */
    public function profilerFinish(Client $target);
}
