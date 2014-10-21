<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Exception\ApiProblem;

use Matryoshka\Model\Wrapper\Rest\Exception\ExceptionInterface;
use ZF\ApiProblem\Exception\DomainException as ZFApiProblemDomainException;

class DomainException extends ZFApiProblemDomainException implements ExceptionInterface
{}