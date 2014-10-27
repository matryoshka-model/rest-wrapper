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
use Zend\Http\Header\ContentType;
use Matryoshka\Model\Wrapper\Rest\Response\Decoder\DecoderInterface;
use Matryoshka\Model\Wrapper\Rest\Response\Decoder\Hal;

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
     * @var DecoderInterface
     */
    protected $responseDecoder;

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
     * @var string
     */
    protected $requestFormat = self::FORMAT_JSON;

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
        $this->getUriResourceStrategy()->configureUri($request->getUri(), $this->resourceName, $id);


        $queryParams = $request->getQuery();
        foreach ($query as $name => $value) {
            $queryParams->set($name, $value);
        }

        if (!empty($data)) {
            $request->setContent($this->encodeBodyRequest($data));
        }

        $request->getHeaders()->addHeaderLine('Content-Type', 'application/' . $this->getRequestFormat())
                              ->addHeaderLine('Accept', 'application/json')
                              ->addHeaderLine('Accept', 'application/xml');

        return $request;
    }


    /**
     * @param Request $request
     * @return array
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
        $decodedResponse = (array) $this->getResponseDecoder()->decode($response);

        if (in_array($responseStatusCode, $validStatusCodes)) {
            return $decodedResponse;
        }

        throw $this->getInvalidResponseException($decodedResponse, $response);
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

                // TODO: not yet implemented
                // break;
            default:
                throw new Exception\InvalidFormatException(sprintf(
                    'The "%s" format is invalid or not supported',
                    $requestFormat
                ));
                break;
        }

        return $bodyRequest;
    }

    /**
     * @param $bodyDecodeResponse
     * @return Exception\InvalidResponseException
     */
    protected function getInvalidResponseException(array $bodyDecodeResponse, Response $response)
    {
        $contentType = $response->getHeaders()->get('Content-Type');

        if ($contentType instanceof ContentType && $contentType->match('application/problem+*')) {

            $apiProblemDefaults = [
                'type'      => $response->getReasonPhrase(),
                'title'     => '',
                'status'    => $response->getStatusCode(),
                'detail'    => '',
                'instance'  => '',
            ];

            $bodyDecodeResponse += $apiProblemDefaults;

            //Setup remote exception
            $remoteExceptionStack = isset($bodyDecodeResponse['exception_stack']) && is_array($bodyDecodeResponse['exception_stack']) ?
                $bodyDecodeResponse['exception_stack'] : [];

            array_unshift($remoteExceptionStack, [
                'message' => $bodyDecodeResponse['detail'],
                'code'    => $bodyDecodeResponse['status'],
                'trace'   => isset($bodyDecodeResponse['trace']) ? $bodyDecodeResponse['trace'] : null,
            ]);

            //Setup exception
            $exception = new Exception\ApiProblem\DomainException(
                $bodyDecodeResponse['detail'],
                $bodyDecodeResponse['status'],
                Exception\RemoteException::factory($remoteExceptionStack) //Set remote ex chain as previous of current ex
            );
            $exception->setType($bodyDecodeResponse['type']);
            $exception->setTitle($bodyDecodeResponse['title']);
            foreach ($apiProblemDefaults as $key => $value) {
                unset($bodyDecodeResponse[$key]);
            }
            $exception->setAdditionalDetails($bodyDecodeResponse);
        } else {
            $exception = new Exception\InvalidResponseException($response->getContent(), $response->getStatusCode());
        }

        return $exception;
    }


    public function getResponseDecoder()
    {
        if (null === $this->responseDecoder) {
            $this->setResponseDecoder(new Hal);
        }

        return $this->responseDecoder;
    }

    public function setResponseDecoder(DecoderInterface $decoder)
    {
        $this->responseDecoder = $decoder;
        return $this;
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
     * {@inheritdoc}
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastResponseData()
    {
        return $this->getResponseDecoder()->getLastPayload();
    }
}
