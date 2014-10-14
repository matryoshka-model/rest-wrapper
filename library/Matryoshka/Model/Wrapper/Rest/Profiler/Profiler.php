<?php
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
     * ATTRIBUTE
     ******************************************************************************************************************/

    /**
     * @var array
     */
    protected $profiles = [];

    /**
     * @var null
     */
    protected $currentIndex = 0;

    /**
     * METHOD
     ******************************************************************************************************************/

    /**
     * @param $target
     * @return self
     */
    public function profilerStart($target)
    {
        $profileInformation = array(
            'request' => null,
            'response' => null,
            'start' => microtime(true),
            'end' => null,
            'elapse' => null
        );

        if ($target instanceof Request) {
            $profileInformation['request'] = $target;
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
            $current['response'] = $target;
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