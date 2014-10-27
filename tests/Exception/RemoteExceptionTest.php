<?php
namespace MatryoshkaModelWrapperRestTest\Exception;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Matryoshka\Model\Wrapper\Rest\Exception\RemoteException;

class RemoteExceptionTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [[]],
            [[[]]],
            [[null]],
            [ [['code' => 0, 'message' => 'first', 'trace' => ['file' => 'foo.php', 'line' => 11]]] ],
            [ [['code' => 0, 'message' => 'first', 'line' => 10, 'file' => 'baz.php', 'extra' => 'extra']], ['extra' => 'extra'] ],
            [ [['code' => 1, 'message' => 'test'], ['code' => 2, 'message' => 'test'], ['code' => 3, 'message' => 'test']] ],
        ];
    }


    /**
     * @dataProvider dataProvider
     */
    public function testFactory($stack, $extra = [])
    {
        $ex = RemoteException::factory($stack);

        for($i=0; $i<count($stack); $i++) {

            if (!is_array($stack[$i])) {
                continue;
            }

            $this->assertInstanceOf('\Matryoshka\Model\Wrapper\Rest\Exception\RemoteException', $ex);

            $this->assertSame(isset($stack[$i]['code']) ? $stack[$i]['code'] : 0, $ex->getCode());
            $this->assertSame(isset($stack[$i]['message']) ? $stack[$i]['message'] : '', $ex->getMessage());

            if (isset($stack[$i]['trace'])) {
                $this->assertSame($stack[$i]['trace'], $ex->getRemoteTrace());
            }
            if (isset($stack[$i]['line'])) {
                $this->assertSame($stack[$i]['line'], $ex->getLine());
            }
            if (isset($stack[$i]['file'])) {
                $this->assertSame($stack[$i]['file'], $ex->getFile());
            }

            $this->assertSame($extra, $ex->getAdditionalDetails());

            $ex = $ex->getPrevious();
        }

        $this->assertNull($ex);
    }


    public function testGetRemoteTrace()
    {
        $ex = new RemoteException();
        $this->assertSame([], $ex->getRemoteTrace());
    }

    public function testGetAdditionalDetails()
    {
        $ex = new RemoteException();
        $this->assertSame([], $ex->getAdditionalDetails());
    }

}
