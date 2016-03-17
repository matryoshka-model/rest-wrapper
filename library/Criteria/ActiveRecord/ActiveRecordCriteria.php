<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Criteria\ActiveRecord;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\ModelStubInterface;
use Matryoshka\Model\Wrapper\Rest\RestClient;
use Matryoshka\Service\Api\Exception\ExceptionInterface;
use Zend\Http\Response;

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
    public function apply(ModelStubInterface $model)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();
        try {
            $result = $client->get($this->getId());
        } catch (ExceptionInterface $e) {
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
    public function applyWrite(ModelStubInterface $model, array &$data)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();

        if ($this->hasId()) {
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
    public function applyDelete(ModelStubInterface $model)
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
