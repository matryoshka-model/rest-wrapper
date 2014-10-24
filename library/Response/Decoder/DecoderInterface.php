<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Response\Decoder;

use Zend\Http\Response;
interface DecoderInterface
{
    /**
     * @param Response $response
     */
    public function decode(Response $response);
}
