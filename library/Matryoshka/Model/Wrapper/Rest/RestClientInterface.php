<?php
namespace Matryoshka\Model\Wrapper\Rest;


interface RestClientInterface
{
    /**
     * @return string
     */
    public function getResourceName();

    public function delete($id = null, array $query = []);

    public function get($id = null, array $query = []);

    public function head($id = null, array $query = []);

    public function options(array $query = []);

    public function patch($id = null, array $data, array $query = []);

    public function post(array $data, array $query = []);

    public function put($id, array $data, array $query = []);

}