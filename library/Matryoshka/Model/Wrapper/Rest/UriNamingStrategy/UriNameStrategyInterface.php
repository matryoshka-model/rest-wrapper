<?php
namespace Matryoshka\Model\Wrapper\Rest\UriNamingStrategy;

interface UriNamingStrategyInterface
{
    public function getResourcePath($name, $id = null);
}