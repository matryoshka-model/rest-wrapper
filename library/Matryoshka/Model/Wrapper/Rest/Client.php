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
use Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerInterface;
use Zend\Http\Client as ZendClient;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Uri\Http;
use ZendXml\Security;

/**
 * Class Client
 */
class Client extends ZendClient
{
    const FORMAT_OUTPUT_JSON = 'json';
    const FORMAT_OUTPUT_XML  = 'xml';


    /**
     * @var array
     */
    protected $codesStatusValid = [Response::STATUS_CODE_200];

    /**
     * @var string
     */
    protected $formatOutput = self::FORMAT_OUTPUT_JSON;

    /**
     * @var int
     */
    protected $returnType = Json::TYPE_ARRAY;

    /**
     * @var
     */
    protected $profiler;

    /**
     * @return array|object
     */
    public function sendRequest()
    {
        $codesStatusValid = $this->getCodesStatusValid();
        // Send request
        $response = $this->send();
        $codeStatusResponse = $response->getStatusCode();

        $bodyDecodeResponse = $this->decodeBodyResponse($response);

        if (in_array($codeStatusResponse, $codesStatusValid)) {
            return $bodyDecodeResponse;
        }

        throw $this->getExceptionInvalidResponse($bodyDecodeResponse);
    }

    protected function doRequest(Http $uri, $method, $secure = false, $headers = array(), $body = '')
    {


        return parent::doRequest($uri, $method, $secure, $headers, $body);
    }


    /**
     * @param Response $response
     * @return array|object
     * @throws Exception\InvalidFormatOutputException
     */
    protected function decodeBodyResponse(Response $response)
    {
        $bodyResponse = $response->getBody();
        $formatOutput = $this->getFormatOutput();

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
     * @param array $codesStatusValid
     */
    public function setCodesStatusValid(array $codesStatusValid)
    {
        $this->codesStatusValid = $codesStatusValid;
    }

    /**
     * @return array
     */
    public function getCodesStatusValid()
    {
        return $this->codesStatusValid;
    }

    /**
     * @param string $formatOutput
     */
    public function setFormatOutput($formatOutput)
    {
        $this->formatOutput = $formatOutput;
    }

    /**
     * @return string
     */
    public function getFormatOutput()
    {
        return $this->formatOutput;
    }

    /**
     * @param int $returnType
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * @return int
     */
    public function getReturnType()
    {
        return $this->returnType;
    }
}