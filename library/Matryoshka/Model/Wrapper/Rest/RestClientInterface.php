<?php
namespace Matryoshka\Model\Wrapper\Rest;


use Matryoshka\Model\ModelInterface;
use Zend\Stdlib\RequestInterface;

interface RestClientInterface
{
    public function put($id, array $data, array $query = []);

    public function get($id = null, array $query = []);

    public function delete($id);

    public function post(array $data, array $query = []);

} 