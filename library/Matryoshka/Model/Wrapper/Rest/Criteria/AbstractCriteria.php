<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Criteria;

use Matryoshka\Model\Criteria\DeletableCriteriaInterface;
use Matryoshka\Model\Criteria\ReadableCriteriaInterface;
use Matryoshka\Model\Criteria\WritableCriteriaInterface;
use Matryoshka\Model\ModelInterface;

/**
 * Class AbstractCriteria
 */
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
}