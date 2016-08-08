<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Service;

use Interop\Container\ContainerInterface;
use Matryoshka\Model\Wrapper\Rest\RestClient;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ApiAbstractServiceFactory
 */
class RestClientAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var string
     */
    protected $configKey = 'matryoshka-rest';

    /**
     * @var array
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $this->getConfig($container);
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
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getConfig($container)[$requestedName];

        $resourceName = $config['resource_name'];

        /** @var $httpClient Client */
        $httpClient = isset($config['http_client']) && $container->has($config['http_client']) ?
            $container->get($config['http_client']) : null;

        /** @var $baseRequest Request */
        $baseRequest = isset($config['base_request']) && $container->has($config['base_request']) ?
            $container->get($config['base_request']) : null;

        $restClient = new RestClient($resourceName, $httpClient, $baseRequest);

        if (isset($config['uri_resource_strategy']) && $container->has($config['uri_resource_strategy'])) {
            $restClient->setUriResourceStrategy($container->get($config['uri_resource_strategy']));
        }

        // Array of int code valid
        if (isset($config['valid_status_code']) && is_array($config['valid_status_code'])) {
            $restClient->setValidStatusCodes($config['valid_status_code']);
        }
        // string json/xml
        if (isset($config['request_format'])) {
            $restClient->setRequestFormat($config['request_format']);
        }
        // Profiler
        if (isset($config['profiler']) && $container->has($config['profiler'])) {
            $restClient->setProfiler($container->get($config['profiler']));
        }

        return $restClient;
    }

    /**
     * Get rest configuration, if any
     *
     * @param ContainerInterface $container
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $container->get('Config');

        if (!$container->has('Config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $container->get('Config');
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
