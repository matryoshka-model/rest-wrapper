<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperRestTest\UriResourceStrategy;

use Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\DefaultStrategy;
use Zend\Uri\Uri;

/**
 * Class DefaultStrategyTest
 */
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
