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
 *
 * In order to make this adapter working with Zend\Paginator we need to know
 * the total items count prior to call getItems().
 *
 * Two alternatives are available:
 *
 * - call getItems() prior count()
 *
 * - call preload() prior to call count()
 *
 * In both case, if $offset and $itemCountPerPage will be the same in later calls,
 * resultset already loaded will be reused, avoiding futher remote requests.
 *
 *
 *
 *
 *
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
     * @var array
     */
    protected $preloadCache = [];

    /**
	 * @param AbstractModel $model
	 * @param FindAllCriteria $criteria
	 * @throws InvalidArgumentException
	 */
    public function __construct(AbstractModel $model, FindAllCriteria $criteria)
    {
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
     * @param string $offset
     * @param string $itemCountPerPage
     * @throws Exception\InvalidArgumentException
     * @return \Matryoshka\Model\ResultSet\ResultSetInterface
     */
    protected function loadItems($offset = null, $itemCountPerPage = null)
    {
        $criteria = clone $this->criteria;

        if ($itemCountPerPage !== null) {
            $criteria->setLimit($itemCountPerPage);
        }

        if ($offset !== null) {
            $criteria->setOffset($offset);
        }

        if ($criteria->getPage() === null) {
            $criteria->setPage(1);
        }

        /* @var $restClient RestClientInterface */
        $restClient = $this->model->getDataGateway();
        if (!$restClient instanceof RestClientInterface) {
            throw new Exception\InvalidArgumentException('Model must provide a RestClientInterface datagateway');
        }

        $resultSet = $this->model->find($criteria);
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
     * Preload items matching settings specified by the criteria object
     *
     * This methods allows to preload item using the passed criteria.
     * Example:
     *
     * // Assume page e item-count-per page are known
     * $page = 1;
     * $itemCountPerPage = 5;
     *
     * // Assume a Rest model service
     * $service->getPaginatorCriteria()->setPage($page)->setLimit($itemCountPerPage);
     *
     * // assume we're using RestPaginatorAdapter
     * $adapter = $service->getPaginatorAdapter();
     * $adapter->preload();
     *
     * // Then, we can use Paginator normally
     * $paginator = new Paginator($adapter);
     * $paginator->setCurrentPageNumber($page)
     *            ->setItemCountPerPage($itemCountPerPage);
     *
     */
    public function preload()
    {
        $this->loadItems();
    }

    /**
     * Returns an result set of items for a page
     *
     * @param  int $offset           Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return HydratingResultSet
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $cacheKey = $offset . '-' . $itemCountPerPage;

        if (isset($this->preloadCache[$cacheKey])) {
            return $this->preloadCache[$cacheKey];
        }

        return $this->loadItems($offset, $itemCountPerPage);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->count) {
            throw new Exception\RuntimeException('If your API returns the total_items value, call preload() prior using count()');
        }

        return $this->count;
    }
}
