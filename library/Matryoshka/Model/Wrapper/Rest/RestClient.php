<?php
namespace Matryoshka\Model\Wrapper\Rest;

use Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerAwareInterface;
use Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerAwareTrait;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Stdlib\ResponseInterface;

class RestClient implements RestClientInterface, ProfilerAwareInterface
{
    use ProfilerAwareTrait;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var Request
     */
    protected $defaultRequest;

    /**
     * @var Request
     */
    protected $currentRequest;

    /**
     * @param Client $httpClient
     * @param array $options
     */
    function __construct(Client $httpClient, Request $request = null)
    {
        $this->httpClient = $httpClient;
        $this->defaultRequest = $request;
        var_dump($request);
        die();
    }

    /**
     * @return Request
     */
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }

    /**
     * @param Request $currentRequest
     */
    public function setCurrentRequest(Request $currentRequest)
    {
        $this->currentRequest = $currentRequest;
    }

    /**
     * @return Request
     */
    public function getDefaultRequest()
    {
        return $this->defaultRequest;
    }

    /**
     * @param Request $defaultRequest
     */
    public function setDefaultRequest(Request $defaultRequest)
    {
        $this->defaultRequest = $defaultRequest;
    }

    /**
     * @return Request|null
     */
    public function cloneDefaultRequest()
    {
        if (is_a($this->defaultRequest)) {
            return clone $this->getDefaultRequest();
        }
        return null;
    }

    /**
     * @return Request|null
     */
    public function cloneHttpClient()
    {
        if (is_a($this->httpClient)) {
            return clone $this->httpClient();
        }
        return null;
    }

    /**
     * @param $id
     * @param array $data
     * @param array $query
     *
     */
    public function put($id, array $data, array $query = [])
    {
        $request = $this->getCurrentRequest();
        $request->setMethod(Request::METHOD_PUT);

        $this->dispactRequest($request);
    }

    /**
     * @param null $id
     * @param array $query
     */
    public function get($id = null, array $query = [])
    {
        $request = $this->getCurrentRequest();
        $request->setMethod(Request::METHOD_GET);

        $this->dispactRequest($request);
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        $request = $this->getCurrentRequest();
        $request->setMethod(Request::METHOD_DELETE);

        $this->dispactRequest($request);
    }

    /**
     * @param array $data
     * @param array $query
     */
    public function post(array $data, array $query = [])
    {
        $request = $this->getCurrentRequest();
        // Settings
        $request->setMethod(Request::METHOD_POST);
        $request->setContent($data);
        if ($query) {
            $request->setQuery($query);
        }

        $this->dispactRequest($request);
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public  function dispactRequest(Request $request)
    {
        if ($this->profiler) {
            $this->getProfiler()->profilerStart($request);
        }

        $response = $this->httpClient->dispatch($this->getCurrentRequest());
        $this->currentRequest = $this->cloneDefaultRequest();

        if ($this->profiler) {
            $this->getProfiler()->profilerFinish( $this->httpClient->getResponse());
        }

        return $response;
    }
}