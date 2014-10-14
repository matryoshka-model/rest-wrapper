<?php
namespace Matryoshka\Model\Wrapper\Rest\UriResourceStrategy;

use Zend\Uri\Uri;

class DefaultStrategy implements UriResourceStrategyInterface
{
    public function configureUri(Uri $baseUri, $name, $id = null)
    {
        $basePath = $baseUri->getPath();
        $resourcePath = rtrim($basePath, '/') . '/' . $name;
        if ($id) {
            $resourcePath .= $id;
        }
        $baseUri->setPath($resourcePath);
        return $baseUri;
    }
}