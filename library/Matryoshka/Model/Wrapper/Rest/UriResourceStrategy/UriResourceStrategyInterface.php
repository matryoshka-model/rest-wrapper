<?php
namespace Matryoshka\Model\Wrapper\Rest\UriResourceStrategy;


use Zend\Uri\Uri;
interface UriResourceStrategyInterface
{
    public function configureUri(Uri $baseUri, $name, $id = null)
}