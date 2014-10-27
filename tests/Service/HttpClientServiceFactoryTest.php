<?php
namespace MatryoshkaModelWrapperRestTest\Service;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class HttpClientServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;


    protected $testOptions = [
        'useragent'     => 'TestUserAgent',
    ];


    public function setUp()
    {
        $config = [
            'matryoshka-rest-httpclient' => $this->testOptions + [
                'uri'    => 'http://example.net',
            ],
        ];

        $sm = $this->serviceManager = new ServiceManager(
            new Config([
                'factories' => [
                    'HttpClient' => 'Matryoshka\Model\Wrapper\Rest\Service\HttpClientServiceFactory',
                ]
            ])
        );

        $sm->setService('Config', $config);
    }


    public function testCreateService()
    {
        $client = $this->serviceManager->get('HttpClient');
        $this->assertInstanceOf('\Zend\Http\Client', $client);

        $this->assertEquals('http://example.net/', (string) $client->getUri());

        $refl = new \ReflectionClass($client);
        $configProp = $refl->getProperty('config');
        $configProp->setAccessible(true);
        $config = $configProp->getValue($client);

        foreach ($this->testOptions as $key => $value) {
            $this->assertSame($value, $config[$key]);
        }

    }

}
