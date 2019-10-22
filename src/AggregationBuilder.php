<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\CompositeAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FiltersAggregation;
use ONGR\ElasticsearchDSL\SearchEndpoint\AggregationsEndpoint;
use ONGR\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface;
use Runner\EsqBuilder\Contracts\BuilderInterface;
use Runner\EsqBuilder\Factories\AggregationFactory;

class AggregationBuilder implements BuilderInterface
{
    /**
     * @var AggregationsEndpoint
     */
    protected $endpoint;

    public function __construct()
    {
        $this->endpoint = new AggregationsEndpoint();
    }

    /**
     * @param string $type
     * @param string $bucket
     * @param mixed  ...$arguments
     *
     * @return $this
     */
    public function add($type, $bucket, ...$arguments)
    {
        array_unshift($arguments, $bucket);

        $this->endpoint->add(AggregationFactory::make($type, $arguments), $bucket);

        return $this;
    }

    /**
     * @param string        $bucket
     * @param callable|null $callback
     *
     * @return $this
     */
    public function composite($bucket, callable $callback = null)
    {
        $agg = new CompositeAggregation($bucket);
        if ($callback) {
            $callback($builder = new static());

            foreach ($builder->getSearchEndpoint()->getAll() as $item) {
                $agg->addSource($item);
            }
        }
        $this->endpoint->add($agg, $bucket);

        return $this;
    }

    /**
     * @param string        $bucket
     * @param callable|null $callback
     *
     * @return $this
     */
    public function filter($bucket, callable $callback = null)
    {
        $agg = new FilterAggregation($bucket);

        if ($callback) {
            call_user_func($callback, $builder = new QueryBuilder());

            $agg->setFilter($builder->getSearchEndpoint()->getBool());
        }

        $this->endpoint->add($agg, $bucket);

        return $this;
    }

    /**
     * @param string $bucket
     * @param bool   $anonymous
     * @param array  $callbacks
     *
     * @return $this
     */
    public function filters($bucket, $anonymous = false, array $callbacks = [])
    {
        $agg = new FiltersAggregation($bucket, [], $anonymous);

        foreach ($callbacks as $name => $callback) {
            call_user_func($callback, $builder = new QueryBuilder());
            $agg->addFilter($builder->getSearchEndpoint()->getBool(), $name);
        }

        $this->endpoint->add($agg, $bucket);

        return $this;
    }

    /**
     * @param string   $bucket
     * @param callable $callback
     *
     * @return $this
     */
    public function aggregation($bucket, callable $callback)
    {
        if (!$this->endpoint->has($bucket)) {
            throw new \LogicException(sprintf('Missing definition for aggregation [%s]', $bucket));
        }

        $callback($builder = new static());

        $aggregation = $this->endpoint->get($bucket);

        foreach ($builder->getSearchEndpoint()->getAll() as $item) {
            $aggregation->addAggregation($item);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (!AggregationFactory::has($name)) {
            throw new \BadMethodCallException(
                sprintf('Call to undefined method %s::%s()', static::class, $name)
            );
        }

        $bucket = array_shift($arguments);

        return $this->add($name, $bucket, ...$arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchEndpoint(): SearchEndpointInterface
    {
        return $this->endpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return Serializer::normalize($this->endpoint);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return '';
    }
}
