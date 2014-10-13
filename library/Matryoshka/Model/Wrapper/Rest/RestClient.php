<?php
namespace Matryoshka\Model\Wrapper\Rest;

use Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerAwareInterface;
use Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerAwareTrait;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Stdlib\ResponseInterface;
use ZendXml\Security;

class RestClient implements RestClientInterface, ProfilerAwareInterface
{
    use ProfilerAwareTrait;

    const FORMAT_OUTPUT_JSON = 'json';
    const FORMAT_OUTPUT_XML  = 'xml';

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
     * @var array
     */
    protected $codesStatusValid = [Response::STATUS_CODE_200];

    /**
     * @var string
     */
    protected $formatResponse = self::FORMAT_OUTPUT_JSON;

    /**
     * @var int
     */
    protected $returnType = Json::TYPE_ARRAY;

    /**
     * @param Client $httpClient
     * @param array $options
     */
    function __construct(Client $httpClient, $options)
    {
        $this->httpClient = $httpClient;
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
    public  function dispatchRequest(Request $request)
    {
        if ($this->profiler) {
            $this->getProfiler()->profilerStart($request);
        }

        // Send request ad setting current request to default setting
        $response = $this->httpClient->dispatch($request);
        $this->currentRequest = $this->cloneDefaultRequest();

        if ($this->profiler) {
            $this->getProfiler()->profilerFinish( $this->httpClient->getResponse());
        }

        $codesStatusValid = $this->getCodesStatusValid();
        $codeStatusResponse = $response->getStatusCode();
        $bodyDecodeResponse = $this->decodeBodyResponse($response);

        if (in_array($codeStatusResponse, $codesStatusValid)) {
            return $bodyDecodeResponse;
        }

        throw $this->getExceptionInvalidResponse($bodyDecodeResponse);
    }
    /**
     * @param Response $response
     * @return array|object
     * @throws Exception\InvalidFormatOutputException
     */
    protected function decodeBodyResponse(Response $response)
    {
        $bodyResponse = $response->getBody();
        $formatOutput = $this->getFormatResponse();

        switch ($formatOutput) {
            case self::FORMAT_OUTPUT_JSON:
                return Json::decode($bodyResponse, $this->getReturnType());
                break;
            case self::FORMAT_OUTPUT_XML:
                $xml = Security::scan($response->getBody());
                return Json::decode(Json::encode((array) $xml), $this->getReturnType());
                break;
            default:
                throw new Exception\InvalidFormatOutputException(sprintf(
                    'The format output "%s" is invalid',
                    $formatOutput
                ));
                break;
        }
    }

    /**
     * @param $bodyDecodeResponse
     * @return Exception\InvalidResponseException
     */
    protected function getExceptionInvalidResponse($bodyDecodeResponse)
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
     * @return array
     */
    public function getCodesStatusValid()
    {
        return $this->codesStatusValid;
    }

    /**
     * @param array $codesStatusValid
     */
    public function setCodesStatusValid($codesStatusValid)
    {
        $this->codesStatusValid = $codesStatusValid;
    }

    /**
     * @return string
     */
    public function getFormatResponse()
    {
        return $this->formatResponse;
    }

    /**
     * @param string $formatResponse
     */
    public function setFormatResponse($formatResponse)
    {
        $this->formatResponse = $formatResponse;
    }

    /**
     * @return int
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param int $returnType
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
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
     * @return Request|null
     */
    public function cloneDefaultRequest()
    {
        return clone $this->getDefaultRequest();
    }

    /**
     * @return Request|null
     */
    public function cloneHttpClient()
    {
        return clone $this->httpClient();
    }
}