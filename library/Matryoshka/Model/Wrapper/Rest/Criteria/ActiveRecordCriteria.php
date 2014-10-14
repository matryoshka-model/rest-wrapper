<?php
namespace Matryoshka\Model\Wrapper\Rest\Criteria;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Wrapper\Rest\RestClient;

/**
 * Class ActiveRecordCriteria
 *
 * @package Matryoshka\Model\Wrapper\Rest\Criteria
 */
class ActiveRecordCriteria extends AbstractCriteria
{
    /**
     * @param ModelInterface $model
     */
    public function applyDelete(ModelInterface $model)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();
        return $client->delete($this->getId());
    }

    /**
     * Apply
     * @param ModelInterface $model
     * @return mixed
     */
    public function apply(ModelInterface $model)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();
        return $client->get($this->getId());
    }

    /**
     * @param ModelInterface $model
     * @param array $data
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();

        if ($this->id) {
            $client->put($this->id, $data);
        } else {
            $client->post($data);
        }

        //FIXME: handle result and, if POST, inject the new id into data
        return 1;
    }

}