<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\Http\Client;

class HttpClientServiceFactory implements FactoryInterface
{

    /**
     * @var string
     */
    protected $configKey = 'matryoshka-http';

    /**
    * Create a http client service
    *
    * @param ServiceLocatorInterface $serviceLocator
    * @return Client
    */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $client = new Client();
        if (!empty($config[$this->configKey])) {
            $client->setOptions($config[$this->configKey]);
        }

        return new Client();
    }
}