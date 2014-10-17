<?php
namespace MatryoshkaModelWrapperRestTest\Exception;


use Matryoshka\Model\Wrapper\Rest\Exception\InvalidResponseException;

class InvalidResponseExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $exception = new InvalidResponseException();
        $exception->setStatus('test');
        $this->assertSame('test', $exception->getStatus());

        $exception->setTitle('test');
        $this->assertSame('test', $exception->getStatus());

        $exception->setStatus('test');
        $this->assertSame('test', $exception->getTitle());

        $exception->setType('test');
        $this->assertSame('test', $exception->getType());
    }
} 