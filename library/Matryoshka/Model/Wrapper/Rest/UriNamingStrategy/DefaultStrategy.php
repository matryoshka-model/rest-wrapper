<?php
namespace Matryoshka\Model\Wrapper\Rest\UriNamingStrategy;

class DefaultStrategy implements UriNamingStrategyInterface
{
    public function getResourcePath($name, $id = null)
    {
        return '/' . $name . ($id ? '/' . $id : '');
    }
}