<?php
namespace Matryoshka\Model\Wrapper\Rest\Profiler;

use Zend\Http\Client;

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
            'headers' => '',
            'uri' => null,
            'body' => null,
            'start' => microtime(true),
            'end' => null,
            'elapse' => null
        );

        if ($target instanceof Client) {
            $profileInformation['headers'] = $target->getHeaders();
            $profileInformation['uri'] = $target->getUri();
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

        if ($target instanceof Client) {
            $current['body'] = $target->getResponse()->getBody();
        }

        $this->currentIndex++;
        return $this;
    }



} 