<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Paginator;

use Zend\Paginator\Adapter\AdapterInterface;
use Matryoshka\Model\AbstractModel;
use Matryoshka\Model\Wrapper\Rest\Criteria\FindAllCriteria;
use Matryoshka\Model\Wrapper\Rest\RestClientInterface;
use Matryoshka\Model\Exception\InvalidArgumentException;
use Matryoshka\Model\ResultSet\HydratingResultSet;
use Matryoshka\Model\Exception\RuntimeException;
/**
 * Class RestPaginatorAdapter
 */
class RestPaginatorAdapter implements AdapterInterface
{

    /**
     * @var AbstractModel
     */
    protected $model;

    /**
     * @var FindAllCriteria
     */
    protected $criteria;

    /**
     * @var null|int
     */
    protected $count = null;


    /**
     * @var string
     */
    protected $totalItemsParamName = 'total_items';


	/**
	 * @param AbstractModel $model
	 * @param FindAllCriteria $criteria
	 * @throws InvalidArgumentException
	 */
	public function __construct(AbstractModel $model, FindAllCriteria $criteria)
    {
        if (!$model->getDataGateway() instanceof RestClientInterface) {
            throw new InvalidArgumentException('Model must provide a RestClientInterface datagateway');
        }

        $this->model = $model;
        $this->criteria = $criteria;
    }

    /**
     * @param string $totalItemsParamName
     * @return $this
     */
    public function setTotalItemsParamName($totalItemsParamName)
    {
        $this->totalItemsParamName = (string) $totalItemsParamName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTotalItemsParamName()
    {
        return $this->totalItemsParamName;
    }

    /**
     * Returns an result set of items for a page.
     *
     * @param  int $offset           Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return HydratingResultSet
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $criteria = clone $this->criteria;
        $criteria->limit($itemCountPerPage)->offset($offset);

        $resultSet = $this->model->find($criteria);


        /* @var $restClient RestClientInterface */
        $restClient = $this->model->getDataGateway();
        $collectionData = (array) $restClient->getLastResponseDecoded();

        $this->count = null;
        if (isset($collectionData[$this->totalItemsParamName])) {
            $this->count = $collectionData[$this->totalItemsParamName];
        }

        return $resultSet;
    }

	/**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->count) {
            throw new RuntimeException('If your API response returns the total_items value, call getItems() prior using count()');
        }

        return $this->count;
    }





}
