<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\UriResourceStrategy;

use Zend\Uri\Uri;

/**
 * Class DefaultStrategy
 */
class DefaultStrategy implements UriResourceStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureUri(Uri $baseUri, $name, $id = null)
    {
        $basePath = $baseUri->getPath();
        $resourcePath = rtrim($basePath, '/') . '/' . $name;
        if ($id) {
            $resourcePath .=  '/' . $id;
        }
        $baseUri->setPath($resourcePath);
        return $baseUri;
    }
}
