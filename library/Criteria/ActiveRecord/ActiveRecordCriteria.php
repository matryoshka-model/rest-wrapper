<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Criteria\ActiveRecord;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Wrapper\Rest\RestClient;
use Zend\Http\Response;
use Matryoshka\Service\Api\Exception\ExceptionInterface;

/**
 * Class ActiveRecordCriteria
 *
 * @package Matryoshka\Model\Wrapper\Rest\Criteria
 */
class ActiveRecordCriteria extends AbstractCriteria
{

    /**
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();
        try {
            $result = $client->get($this->getId());
        } catch (ExceptionInterface $e)
        {
            if ($e->getCode() == Response::STATUS_CODE_404) {
                return [];
            } // else
            throw $e;
        }
        return (empty($result)) ? [] : [$result];
    }

    /**
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelInterface $model)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();
        $client->delete($this->getId());

        switch ($client->getLastResponse()->getStatusCode()) {
            case Response::STATUS_CODE_200:
            case Response::STATUS_CODE_204:
                return 1;
        }
        return null;
    }
}