<?php
/**
 * Created by visa
 * Date:  07/10/14 13.14
 * Class: AbstractCriteria.php
 */

namespace Matryoshka\Model\Wrapper\Rest\Criteria;


use Matryoshka\Model\Criteria\DeletableCriteriaInterface;
use Matryoshka\Model\Criteria\ReadableCriteriaInterface;
use Matryoshka\Model\Criteria\WritableCriteriaInterface;
use Matryoshka\Model\ModelInterface;

abstract class AbstractCriteria implements
    ReadableCriteriaInterface,
    WritableCriteriaInterface,
    DeletableCriteriaInterface
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
        $model->getDataGateway()->setMethod();
    }

    /**
     * @param ModelInterface $model
     * @param array $data
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        // TODO: Implement applyWrite() method.
    }

    function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}