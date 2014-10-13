<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Service;

use Matryoshka\Model\Wrapper\Rest\RestClient;
use Zend\Http\Client;
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
        $requestNodeConfig = $this->checkHasRequestedNameConfig($config, $requestedName, $serviceLocator);

        return (
            $serviceConfig
            && $requestNodeConfig
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
        $request = $serviceLocator->get($config['request']);

        $restClient = new RestClient($httpClient, $request);

        // Array of int code valid
        if (isset($config['codesStatusValid'])) {
            $restClient->setCodesStatusValid($config['codesStatusValid']);
        }
        // Int 0/1
        if (isset($config['returnType'])) {
            $restClient->setReturnType($config['returnType']);
        }
        // string json/xml
        if (isset($config['formatResponse'])) {
            $restClient->setFormatResponse($config['formatResponse']);
        }
        // Profiler
        if (isset($config['profiler']) && $serviceLocator->has($config['profiler'])) {
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
     * Check if has node config request
     *
     * @param $config
     * @param $requestedName
     * @param ServiceLocatorInterface $serviceLocator
     * @return bool
     */
    public function checkHasDefaultRequestConfig($config, $requestedName, ServiceLocatorInterface $serviceLocator)
    {
        if (isset($config[$requestedName]['request'])
            && is_string($config[$requestedName]['request'])
            && !empty($config[$requestedName]['request'])
            && $serviceLocator->has($config[$requestedName]['request'])
        ) {
            return true;
        }
        return false;
    }
}
