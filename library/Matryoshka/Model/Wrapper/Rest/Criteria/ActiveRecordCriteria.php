<?php
namespace Matryoshka\Model\Wrapper\Rest\Criteria;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\ModelInterface;

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
        // TODO: Implement applyDelete() method.
    }

    /**
     * Apply
     * @param ModelInterface $model
     * @return mixed
     */
    public function apply(ModelInterface $model)
    {
        // TODO: Implement applyDelete() method.
    }

    /**
     * @param ModelInterface $model
     * @param array $data
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        /** @var  $client \Matryoshka\Model\Wrapper\Rest\Client */
        $client = $model->getDataGateway();
        $client->setMethod('POST');
        $client->sendRequest();
    }

} 