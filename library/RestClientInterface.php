<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest;

/**
 * Interface RestClientInterface
 */
interface RestClientInterface
{
    /**
     * @return string
     */
    public function getResourceName();

    /**
     * @return null|Request
     */
    public function getLastRequest();

    /**
     * @return null|Response
     */
    public function getLastResponse();

    /**
     * @return null|array
     */
    public function getLastResponseDecoded();

    /**
     * @param null $id
     * @param array $query
     * @return mixed
     */
    public function delete($id = null, array $query = []);

    /**
     * @param null $id
     * @param array $query
     * @return mixed
     */
    public function get($id = null, array $query = []);

    /**
     * @param null $id
     * @param array $query
     * @return mixed
     */
    public function head($id = null, array $query = []);

    /**
     * @param array $query
     * @return mixed
     */
    public function options(array $query = []);

    /**
     * @param null $id
     * @param array $data
     * @param array $query
     * @return mixed
     */
    public function patch($id = null, array $data, array $query = []);

    /**
     * @param array $data
     * @param array $query
     * @return mixed
     */
    public function post(array $data, array $query = []);

    /**
     * @param $id
     * @param array $data
     * @param array $query
     * @return mixed
     */
    public function put($id, array $data, array $query = []);
}
