<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder;

use ONGR\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface;
use ONGR\ElasticsearchDSL\SearchEndpoint\SortEndpoint;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use ONGR\ElasticsearchDSL\Sort\NestedSort;
use Runner\EsqBuilder\Contracts\BuilderInterface;

class SortBuilder implements BuilderInterface
{
    protected $endpoint;

    public function __construct()
    {
        $this->endpoint = new SortEndpoint();
    }

    /**
     * @param string $field
     * @param string $order
     * @param array $parameters
     * @param NestedSort|null $nestedSort
     * @return $this
     */
    public function fieldSort($field, $order, array $parameters = [], NestedSort $nestedSort = null)
    {
        $sort = new FieldSort($field, $order, $parameters);

        if ($nestedSort) {
            $sort->setNestedFilter($nestedSort);
        }

        $this->endpoint->add($sort);

        return $this;
    }

    /**
     * @param string $path
     * @param array $parameters
     * @param callable|null $filter
     * @param NestedSort|null $nestedSort
     * @return NestedSort
     */
    public function createdNestedSort(
        $path,
        array $parameters = [],
        callable $filter = null,
        NestedSort $nestedSort = null
    ) {
        if ($filter) {
            call_user_func($filter, $builder = new QueryBuilder());
            $filter = $builder->getSearchEndpoint()->getBool();
        }

        $sort = new NestedSort($path, $filter, $parameters);

        if ($nestedSort) {
            $sort->setNestedFilter($nestedSort);
        }

        return $sort;
    }

    /**
     * @return SearchEndpointInterface
     */
    public function getSearchEndpoint(): SearchEndpointInterface
    {
        return $this->endpoint;
    }

    /**
     * Generates array which will be passed to elasticsearch-php client.
     *
     * @return array
     */
    public function toArray()
    {
        return Serializer::normalize($this->endpoint);
    }

    /**
     * Returns element type.
     *
     * @return string
     */
    public function getType()
    {
        return '';
    }
}
