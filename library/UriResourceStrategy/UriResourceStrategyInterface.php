<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\UriResourceStrategy;

use Zend\Uri\Uri;

/**
 * Interface UriResourceStrategyInterface
 */
interface UriResourceStrategyInterface
{
    /**
     * @param Uri $baseUri
     * @param $name
     * @param null $id
     * @return mixed
     */
    public function configureUri(Uri $baseUri, $name, $id = null);
}
