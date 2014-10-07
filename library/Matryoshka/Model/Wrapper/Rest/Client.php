<?php
namespace Matryoshka\Model\Wrapper\Rest;

use Zend\Http\Client as ZendClient;
use Zend\Http\Response;
use Zend\Json\Json;
use ZendXml\Security;

class Client extends ZendClient
{
    /**
     * CONSTANT
     ******************************************************************************************************************/

    const FORMAT_OUTPUT_JSON = 'json';
    const FORMAT_OUTPUT_XML  = 'xml';

    /**
     * ATTRIBUTE
     ******************************************************************************************************************/

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
     * METHOD
     ******************************************************************************************************************/

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
                return Json::decode($bodyResponse, $this->returnType());
                break;
            case self::FORMAT_OUTPUT_XML:
                $xml = Security::scan($response->getBody());
                return Json::decode(Json::encode((array) $xml), $this->returnType());
                break;
            default:
                throw new Exception\InvalidFormatOutputException(sprintf("The format output %s is invalid", $formatOutput));
                break;
        }
    }

    /**
     * @param $bodyDecodeResponse
     * @return Exception\InvalidResponseException
     */
    protected function getExceptionInvalidResponse($bodyDecodeResponse)
    {
        if (is_array($bodyDecodeResponse)) {
            $exception = new Exception\InvalidResponseException($bodyDecodeResponse['detail']);
            $exception->setStatus($bodyDecodeResponse['status']);
            $exception->setType($bodyDecodeResponse['type']);
            $exception->setTitle($bodyDecodeResponse['title']);
        }

        if (is_object($bodyDecodeResponse)) {
            $exception = new Exception\InvalidResponseException($bodyDecodeResponse->detail);
            $exception->setStatus($bodyDecodeResponse->status);
            $exception->setType($bodyDecodeResponse->type);
            $exception->setTitle($bodyDecodeResponse->title);
        }

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