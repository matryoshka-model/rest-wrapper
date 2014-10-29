<?php
namespace MatryoshkaModelWrapperRestTest\Exception;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Matryoshka\Model\Wrapper\Rest\Exception\RemoteException;
use Zend\Http\Response;
use Matryoshka\Model\Wrapper\Rest\Exception\InvalidResponseException;

class InvalidResponseExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSetResponse()
    {
        $response = new Response();
        $ex = new InvalidResponseException();

        $ex->setResponse($response);
        $this->assertSame($response, $ex->getResponse());
    }

}
