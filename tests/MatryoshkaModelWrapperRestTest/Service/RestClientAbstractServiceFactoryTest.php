<?php
namespace MatryoshkaModelWrapperRestTest\Service;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class RestClientAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @return array
     */
    public function providerValidService()
    {
        return [
            ['RestDataGateway\Valid'],
        ];
    }

    /**
     * @return array
     */
    public function providerInvalidService()
    {
        return [
            ['RestGateway\Invalid'],
        ];
    }

    public function setUp()
    {
        $config = [
            'matryoshka-rest' => [
                'RestDataGateway\Valid' => [
                    'resource_name'   => 'valid',
                    'valid_status_code' => [
                        200,
                        201
                    ],
                    'return_type' => 1,
                    'request_format' => 'json',
                    'response_format' => 'json',
                    'uri_resource_strategy' => 'Strategy',
                    'profiler' => 'Profiler'
                ],

                'RestGateway\Invalid' => [
                ],
            ],
        ];

        $sm = $this->serviceManager = new ServiceManager(
            new Config([
                'abstract_factories' => [
                    'Matryoshka\Model\Wrapper\Rest\Service\RestClientAbstractServiceFactory',
                ]
            ])
        );

        $sm->setService('Config', $config);

        $profiler = $this->getMock('Matryoshka\Model\Wrapper\Rest\Profiler\ProfilerInterface');
        $sm->setService('Profiler', $profiler);

        $strategy = $this->getMock('Matryoshka\Model\Wrapper\Rest\UriResourceStrategy\UriResourceStrategyInterface');
        $sm->setService('Strategy', $strategy);
    }

    /**
     * @param $service
     * @dataProvider providerValidService
     */
    public function testCreateService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('\Matryoshka\Model\Wrapper\Rest\RestClient', $actual);
    }

    /**
     * @param $service
     * @dataProvider providerInvalidService
     */
    public function testNotCreateService($service)
    {
        $this->assertFalse($this->serviceManager->has($service));
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testNullConfig($service)
    {
        $sl = new ServiceManager(
            new Config(
                [
                    'abstract_factories' => [
                        'Matryoshka\Model\Wrapper\Rest\Service\RestClientAbstractServiceFactory',
                    ]
                ]
            )
        );
        $sl->get($service);
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testEmptyConfig($service)
    {
        $sl = new ServiceManager(
            new Config(
                [
                    'abstract_factories' => [
                        'Matryoshka\Model\Wrapper\Rest\Service\RestClientAbstractServiceFactory',
                    ]
                ]
            )
        );
        $sl->setService('Config', []);
        $sl->get($service);
    }
} 