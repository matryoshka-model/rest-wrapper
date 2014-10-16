<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Criteria;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Wrapper\Rest\RestClient;
use Zend\Http\Response;

/**
 * Class ActiveRecordCriteria
 *
 * @package Matryoshka\Model\Wrapper\Rest\Criteria
 */
class ActiveRecordCriteria extends AbstractCriteria
{
    /**
     * @param ModelInterface $model
     * @return int|null
     */
    public function applyDelete(ModelInterface $model)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();
        $client->delete($this->getId());

        switch ($client->getLastResponse()->getStatusCode()) {
            case Response::STATUS_CODE_200:
                return 1;
            case Response::STATUS_CODE_204:
                return 0;
        }
        return null;
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
     * @return int|null
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();

        if ($this->id) {
            $data = $client->put($this->getId(), $data);
        } else {
            $data = $client->post($data);
        }

        switch ($client->getLastResponse()->getStatusCode()) {
            case Response::STATUS_CODE_200:
            case Response::STATUS_CODE_201:
                return 1;
            case Response::STATUS_CODE_204:
                return 0;
        }
        return null;
    }
}
