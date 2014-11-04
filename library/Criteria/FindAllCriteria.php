<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Criteria;


use Matryoshka\Model\Exception;
use Matryoshka\Model\ModelInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Matryoshka\Model\Criteria\AbstractCriteria;
use Matryoshka\Model\Wrapper\Rest\RestClient;
use Matryoshka\Model\Criteria\PaginableCriteriaInterface;
use Matryoshka\Model\Wrapper\Rest\Paginator\RestPaginatorAdapter;


/**
 * Class FindAllCriteria
 */
class FindAllCriteria extends AbstractCriteria implements PaginableCriteriaInterface
{

    /**
     * @var int|null
     */
    protected $page = null;

    /**
     * @var string
     */
    protected $pageParamName = 'page';

    /**
     * @var string
     */
    protected $pageSizeParamName = 'page_size';

    /**
     * @var string
     */
    protected $totalItemsParamName = 'total_items';

    /**
     *
     * @var array
     */
    protected $query = [];


    /**
     * @param string $name
     * @return $this
     */
    public function setPageParam($name)
    {
        $this->pageParamName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageParam()
    {
        return $this->pageParamName;
    }

    /**
     * Set the page size parameter for paginated requests.
     *
     * @param string $name
     * @return $this
     */
    public function setPageSizeParam($name)
    {
        $this->pageSizeParamName = (string) $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageSizeParam()
    {
        return $this->pageSizeParamName;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setTotalItemsParam($name)
    {
        $this->totalItemsParamName = (string) $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTotalItemsParam()
    {
        return $this->totalItemsParamName;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOffset($offset)
    {
        if (null === $offset) {
            $this->page = null;
            $this->offset = null;
            return $this;
        }

        if (null === $this->limit) {
            throw new Exception\RuntimeException(__METHOD__ .'() unsupported without limit. Use setPage() or set a limit prior to call '. __METHOD__ . '()');
        }

        $this->offset = (int) $offset;
        $this->page = ceil($this->offset / $this->limit);
        return $this;
    }

    protected function updateOffset()
    {
        if ($this->limit && $this->page) {
            $this->offset = $this->limit * ($this->page -1);
        } else {
            $this->offset = null;
        }
    }

    /**
     * Set Page
     *
     * This method will reset the offset value.
     *
     * @param int|null $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = null === $page ? null : (int) $page;
        $this->updateOffset();
        return $this;
    }

    /**
     * @return int!null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        /* @var $client RestClient */
        $client = $model->getDataGateway();

        $query = $this->getQuery();

        if ($this->limit) {
            $query[$this->pageSizeParamName] = $this->limit;
        }

        if ($this->page) {
            $query[$this->pageParamName] = $this->page;
        }

        $result = $client->get(null, $query);

        $payloadData = $client->getLastResponseData();

        if (isset($payloadData[$this->pageSizeParamName])) {
            $this->limit = (int) $payloadData[$this->pageSizeParamName];
        }

        if (isset($payloadData[$this->pageParamName])) {
            $this->page = (int) $payloadData[$this->pageParamName];
        }

        $this->updateOffset();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatorAdapter(ModelInterface $model)
    {
        $paginator = new RestPaginatorAdapter($model, $this);
        $paginator->setTotalItemsParamName($this->totalItemsParamName);
        return $paginator;
    }


}
