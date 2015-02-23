<?php
namespace MatryoshkaModelWrapperRestTest\UriResourceStrategy;

use Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\DefaultStrategy;
use Zend\Uri\Uri;

class DefaultStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultStrategy
     */
    protected $strategy;

    public function setUp()
    {
        $this->strategy = new DefaultStrategy();
    }

    public function testConfigureUri()
    {
        $uri = new Uri('http://example.com/');

        $this->assertSame($uri, $this->strategy->configureUri($uri, 'test', 12));
        $this->assertSame('http://example.com/test/12', $uri->toString());
    }
}
