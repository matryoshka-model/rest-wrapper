<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Service;


use Zend\Http\Client;
use Matryoshka\Model\Wrapper\Rest\RestClient;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


/**
 * Class ApiAbstractServiceFactory
 */
class RestClientAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var string
     */
    protected $configKey = 'rest-api'; // TODO: choose correct config node name

    /**
     * @var array
     */
    protected $config;

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator);
        if (empty($config)) {
            return false;
        }

        $serviceConfig = $this->checkHasRequestedNameConfig($config, $requestedName);
        $urlNodeConfig = $this->checkHasUrlConfig($config, $requestedName);

        return (
            $serviceConfig
            && $urlNodeConfig
        );

    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator)[$requestedName];

        $httpClient = new Client();
        $request = new Request();

        // Array of header
        if (isset($config['headers'])) {

            $headers = new Headers();
            $headers->addHeaders($config['headers']);
            $request->setHeaders($headers);
        }

        /*
        // Array of int code valid
        if (isset($config['codesStatusValid'])) {
            $client->setCodesStatusValid($config['codesStatusValid']);
        }
        // Int 0/1
        if (isset($config['returnType'])) {
            $client->setReturnType($config['returnType']);
        }
        // string json/xml
        if (isset($config['formatOutput'])) {
            $client->setFormatOutput($config['formatOutput']);
        }
        */


        $restClient = new RestClient($httpClient, $request);

        // Profiler
        if ($serviceLocator->has($config['profiler'])) {
            $restClient->setProfiler($serviceLocator->get($config['profiler']));
        }

        return $restClient;
    }

    /**
     * Get rest configuration, if any
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$serviceLocator->has('Config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $serviceLocator->get('Config');
        if (!isset($config[$this->configKey]) || !is_array($config[$this->configKey])) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

    /**
     * Check if has node config
     *
     * @param $config
     * @param $requestedName
     * @return bool
     */
    public function checkHasRequestedNameConfig($config, $requestedName)
    {
        if (isset($config[$requestedName])
            && is_array($config[$requestedName])
            && !empty($config[$requestedName])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if has node config url
     *
     * @param $config
     * @param $requestedName
     * @return bool
     */
    public function checkHasUrlConfig($config, $requestedName)
    {
        if (isset($config[$requestedName]['url'])
            && is_string($config[$requestedName]['url'])
            && !empty($config[$requestedName]['url'])
        ) {
            return true;
        }
        return false;
    }
}