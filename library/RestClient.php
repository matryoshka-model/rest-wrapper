<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest;

use Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\DefaultStrategy;
use Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\UriResourceStrategyInterface;
use Matryoshka\Service\Api\Client\HttpApi;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Class RestClient
 */
class RestClient extends HttpApi implements RestClientInterface
{

    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @var UriResourceStrategyInterface
     */
    protected $uriResourceStrategy;

    /**
     * @var array
     */
    protected $validStatusCodes = [
        Response::STATUS_CODE_200,
        Response::STATUS_CODE_201,
        Response::STATUS_CODE_202,
        Response::STATUS_CODE_203,
        Response::STATUS_CODE_204,
        Response::STATUS_CODE_205,
        Response::STATUS_CODE_206
    ];


    /**
     * @param mixed $resourceName
     * @param Client $httpClient
     * @param Request $baseRequest
     */
    public function __construct($resourceName, Client $httpClient = null, Request $baseRequest = null)
    {
        $this->resourceName = $resourceName;
        $this->httpClient = $httpClient ? $httpClient : new Client;
        $this->baseRequest = $baseRequest ? $baseRequest : $this->httpClient->getRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id = null, array $query = [])
    {
        $request = $this->prepareRequest(Request::METHOD_DELETE, $id, [], $query);
        return $this->dispatchRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id = null, array $query = [])
    {
        $request = $this->prepareRequest(Request::METHOD_GET, $id, [], $query);
        return $this->dispatchRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function head($id = null, array $query = [])
    {
        $request = $this->prepareRequest(Request::METHOD_HEAD, $id, [], $query);
        return $this->dispatchRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function options(array $query = [])
    {
        $request = $this->prepareRequest(Request::METHOD_OPTIONS, null, [], $query);
        return $this->dispatchRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function patch($id = null, array $data, array $query = [])
    {
        $request = $this->prepareRequest(Request::METHOD_PATCH, $id, $data, $query);
        return $this->dispatchRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function post(array $data, array $query = [])
    {
        $request = $this->prepareRequest(Request::METHOD_POST, null, $data, $query);
        return $this->dispatchRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function put($id, array $data, array $query = [])
    {
        $request = $this->prepareRequest(Request::METHOD_PUT, $id, $data, $query);
        return $this->dispatchRequest($request);
    }


    /**
     * @param string $method
     * @param string $id
     * @param array $query
     * @param array $data
     * @return Request
     */
    public function prepareRequest($method, $id = null, array $data = [], array $query = [])
    {
        $request = parent::prepareRequest($method, null, $data, $query);
        $this->getUriResourceStrategy()->configureUri($request->getUri(), $this->resourceName, $id);
        return $request;
    }


    /**
     * @return UriResourceStrategyInterface
     */
    public function getUriResourceStrategy()
    {
        if (null === $this->uriResourceStrategy) {
            $this->uriResourceStrategy = new DefaultStrategy();
        }
        return $this->uriResourceStrategy;
    }

    /**
     * @param UriResourceStrategyInterface $strategy
     * @return $this
     */
    public function setUriResourceStrategy(UriResourceStrategyInterface $strategy)
    {
        $this->uriResourceStrategy = $strategy;
        return $this;
    }
}
