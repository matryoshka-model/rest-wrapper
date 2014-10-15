<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest;

use Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerAwareInterface;
use Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerAwareTrait;
use Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\DefaultStrategy;
use Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\UriResourceStrategyInterface;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Json;
use ZendXml\Security;

/**
 * Class RestClient
 */
class RestClient implements RestClientInterface, ProfilerAwareInterface
{
    use ProfilerAwareTrait;

    const FORMAT_JSON = 'json';
    const FORMAT_XML  = 'xml';

    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @var UriResourceStrategyInterface
     */
    protected $uriResourceStrategy;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var Request
     */
    protected $baseRequest;

    /**
     * @var array
     */
    protected $validStatusCodes = [Response::STATUS_CODE_200];

    /**
     * @var string
     */
    protected $responseFormat = self::FORMAT_JSON;

    /**
     * @var string
     */
    protected $requestFormat = self::FORMAT_JSON;

    /**
     * @var int 0/1
     */
    protected $returnType = Json::TYPE_ARRAY;

    /**
     * @var Request
     */
    protected $lastRequest = null;

    /**
     * @var Response
     */
    protected $lastResponse = null;

    /**
     * @param $resourceName
     * @param Client $httpClient
     * @param Request $baseRequest
     */
    public function __construct($resourceName, Client $httpClient = null, Request $baseRequest = null)
    {
        $this->resourceName = $resourceName;
        $this->httpClient = $httpClient ? $httpClient : new Client();
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
        $request = $this->cloneBaseRequest();
        $request->setMethod($method);
        $this->getUriResourceStrategy()->configureUri($request->getUri(), $this->responseFormat, $id);


        $queryParams = $request->getQuery();
        foreach ($query as $name => $value) {
            $queryParams->set($name, $value);
        }

        if (!empty($data)) {
            $request->setContent($this->encodeBodyRequest($data));
        }

        return $request;
    }


    /**
     * @param Request $request
     * @return array|object
     */
    public function dispatchRequest(Request $request)
    {
        if ($this->profiler) {
            $this->getProfiler()->profilerStart($request);
        }

        // Send request
        /** @var $response Response */
        $response = $this->httpClient->dispatch($request);
        $this->lastRequest = $request;
        $this->lastResponse = $response;

        if ($this->profiler) {
            $this->getProfiler()->profilerFinish($this->httpClient->getResponse());
        }

        $validStatusCodes = $this->getValidStatusCodes();
        $responseStatusCode = $response->getStatusCode();
        $decodedResponse = $this->decodeBodyResponse($response);

        if (in_array($responseStatusCode, $validStatusCodes)) {
            return $decodedResponse;
        }

        throw $this->getInvalidResponseException($decodedResponse);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function encodeBodyRequest(array $data)
    {
        $requestFormat = $this->getRequestFormat();

        switch ($requestFormat) {
            case self::FORMAT_JSON:
                $bodyRequest = Json::encode($data);
                break;
            case self::FORMAT_XML:
                // TODO
//                 break;
            default:
                throw new Exception\InvalidFormatOutputException(sprintf(
                    'The format "%s" is invalid',
                    $requestFormat
                ));
                break;
        }

        return $bodyRequest;
    }

    /**
     * @param Response $response
     * @return array|object
     * @throws Exception\InvalidFormatOutputException
     */
    protected function decodeBodyResponse(Response $response)
    {
        $bodyResponse = $response->getBody();
        $responseFormat = $this->getResponseFormat();

        switch ($responseFormat) {
            case self::FORMAT_JSON:
                return Json::decode($bodyResponse, $this->getReturnType());
                break;
            case self::FORMAT_XML:
                $xml = Security::scan($response->getBody());
                return Json::decode(Json::encode((array) $xml), $this->getReturnType());
                break;
            default:
                throw new Exception\InvalidFormatOutputException(sprintf(
                    'The format "%s" is invalid',
                    $responseFormat
                ));
                break;
        }
    }

    /**
     * @param $bodyDecodeResponse
     * @return Exception\InvalidResponseException
     */
    protected function getInvalidResponseException($bodyDecodeResponse)
    {
        if (is_object($bodyDecodeResponse)) {
            $bodyDecodeResponse = (array) $bodyDecodeResponse;
        }

        $exception = new Exception\InvalidResponseException($bodyDecodeResponse['detail']);
        $exception->setStatus($bodyDecodeResponse['status']);
        $exception->setType($bodyDecodeResponse['type']);
        $exception->setTitle($bodyDecodeResponse['title']);

        return $exception;
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


    /**
     * @return array
     */
    public function getValidStatusCodes()
    {
        return $this->validStatusCodes;
    }

    /**
     * @param array $validStatusCodes
     * @return $this
     */
    public function setValidStatusCodes(array $validStatusCodes)
    {
        $this->validStatusCodes = $validStatusCodes;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseFormat()
    {
        return $this->responseFormat;
    }

    /**
     * @param $responseFormat
     * @return $this
     */
    public function setResponseFormat($responseFormat)
    {
        $this->responseFormat = $responseFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestFormat()
    {
        return $this->requestFormat;
    }

    /**
     * @param $requestFormat
     * @return $this
     */
    public function setRequestFormat($requestFormat)
    {
        $this->requestFormat = $requestFormat;
        return $this;
    }

    /**
     * @return int
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param $returnType
     * @return $this
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * @return Request
     */
    public function getBaseRequest()
    {
        return $this->baseRequest;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setBaseRequest(Request $request)
    {
        $this->baseRequest = $request;
        return $this;
    }

    /**
     * @return Request
     */
    public function cloneBaseRequest()
    {
        return unserialize(serialize($this->baseRequest));
    }

    /**
     * @return Request
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
