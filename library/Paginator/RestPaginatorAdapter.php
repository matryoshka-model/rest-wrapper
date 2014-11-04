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
use Matryoshka\Model\ResultSet\HydratingResultSet;
use Matryoshka\Model\Exception;
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

    protected $preloadCache = [];

    /**
	 * @param AbstractModel $model
	 * @param FindAllCriteria $criteria
	 * @throws InvalidArgumentException
	 */
    public function __construct(AbstractModel $model, FindAllCriteria $criteria)
    {
        if (!$model->getDataGateway() instanceof RestClientInterface) {
            throw new Exception\InvalidArgumentException('Model must provide a RestClientInterface datagateway');
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

    public function preload($offset = null, $itemCountPerPage = null)
    {
        $cacheKey = $offset . '-' . $itemCountPerPage;

        if (isset($this->preloadCache[$cacheKey])) {
            return $this->preloadCache[$cacheKey];
        }

        $criteria = clone $this->criteria;
        $criteria->setLimit($itemCountPerPage)->setOffset($offset);

        if ($criteria->getPage() === null) {
            $criteria->setPage(1);
        }

        $resultSet = $this->model->find($criteria);

        /* @var $restClient RestClientInterface */
        $restClient = $this->model->getDataGateway();
        $payloadData = (array) $restClient->getLastResponseData();

        $this->count = null;
        if (isset($payloadData[$this->totalItemsParamName])) {
            $this->count = $payloadData[$this->totalItemsParamName];
        }

        $offset            = $criteria->getOffset();
        $itemCountPerPage  = $criteria->getLimit();
        $cacheKey = $offset . '-' . $itemCountPerPage;

        $this->preloadCache = [$cacheKey => $resultSet];

        return $resultSet;
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
        return $this->preload($offset, $itemCountPerPage);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->count) {
            throw new Exception\RuntimeException('If your API response returns the total_items value, call getItems() prior using count()');
        }

        return $this->count;
    }
}
