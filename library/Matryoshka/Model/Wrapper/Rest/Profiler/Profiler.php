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
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Class HttpProfiler
 *
 * @package Matryoshka\Model\Wrapper\Rest\Profiler
 */
class Profiler implements ProfilerInterface
{
    /**
     * @var array
     */
    protected $profiles = [];

    /**
     * @var null
     */
    protected $currentIndex = 0;

    /**
     * @param $target
     * @return self
     */
    public function profilerStart($target)
    {
        $profileInformation = [
            'request' => null,
            'response' => null,
            'start' => microtime(true),
            'end' => null,
            'elapse' => null
        ];

        if ($target instanceof Request) {
            $profileInformation['request']['uri'] = $target->getUriString();
            $profileInformation['request']['headers'] = $target->getHeaders();
            $profileInformation['request']['method'] = $target->getMethod();
            $profileInformation['request']['post'] = $target->getPost()->toString();
            $profileInformation['request']['query'] = $target->getPost()->toString();
            $profileInformation['request']['content'] = $target->getContent();
            $profileInformation['request']['toString'] = $target->getContent()->toString();
        }

        $this->profiles[$this->currentIndex] = $profileInformation;
        return $this;
    }

    /**
     * @param $target
     * @return self
     */
    public function profilerFinish($target)
    {
        $current = &$this->profiles[$this->currentIndex];

        $current['end'] = microtime(true);
        $current['elapse'] = $current['end'] - $current['start'];

        if ($target instanceof Response) {
            $profileInformation['response']['uri'] = $target->getUriString();
            $profileInformation['response']['headers'] = $target->getHeaders();
            $profileInformation['response']['method'] = $target->getMethod();
            $profileInformation['response']['post'] = $target->getPost()->toString();
            $profileInformation['response']['query'] = $target->getPost()->toString();
            $profileInformation['response']['content'] = $target->getContent();
            $profileInformation['response']['toString'] = $target->getContent()->toString();
        }

        $this->currentIndex++;
        return $this;
    }

    /**
     * @return array
     */
    public function getProfiles()
    {
        return $this->profiles;
    }
}
