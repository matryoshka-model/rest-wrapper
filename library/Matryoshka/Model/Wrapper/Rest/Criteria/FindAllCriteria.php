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
     * @var int
     */
    protected $page = 1;

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
    public function getTotalItemsParamName()
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
    public function offset($offset)
    {
        if (null === $this->limit) {
            throw new Exception\RuntimeException('Offset unsupported without limit. Use page() or set a limit prior to call offset()');
        }

        $this->page(ceil($this->offset / $this->limit));

        return parent::offset($offset);
    }

    /**
     * @param int $page
     * @return $this
     */
    public function page($page)
    {
        $this->page = (int) $page;
        return $this;
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

        return $client->get(null, $query);
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
