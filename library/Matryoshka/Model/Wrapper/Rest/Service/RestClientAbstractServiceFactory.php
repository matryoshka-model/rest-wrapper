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
    protected $configKey = 'matryoshka-rest'; // TODO: choose correct config node name

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

        return (
            $serviceConfig
            && !empty($config[$requestedName]['resource_name'])
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


        $resourceName = $config['resource_name'];

        $httpClient = isset($config['http_client']) && $serviceLocator->has($config['http_client']) ?
                $serviceLocator->get($config['http_client']) : null;

        $baseRequest = isset($config['base_request']) && $serviceLocator->has($config['base_request']) ?
                $serviceLocator->get($config['base_request']) : null;

        $restClient = new RestClient($resourceName, $httpClient, $baseRequest);

        if (isset($config['uri_resource_strategy']) && $serviceLocator->has($config['uri_resource_strategy'])) {
            $restClient->setUriResourceStrategy($serviceLocator->get($config['uri_resource_strategy']));
        }

        // Array of int code valid
        if (isset($config['valid_status_code']) && is_array($config['valid_status_code'])) {
            $restClient->setValidStatusCodes($config['valid_status_code']);
        }
        // string json/xml
        if (isset($config['request_format'])) {
            $restClient->setRequestFormat($config['request_format']);
        }
        // string json/xml
        if (isset($config['response_format'])) {
            $restClient->setResponseFormat($config['response_format']);
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
}
