<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\AdjacencyMatrixAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\CompositeAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FiltersAggregation;
use ONGR\ElasticsearchDSL\SearchEndpoint\AggregationsEndpoint;
use ONGR\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface;
use Runner\EsqBuilder\Contracts\BuilderInterface;
use Runner\EsqBuilder\Factories\AggregationFactory;

/**
 * Class AggregationBuilder.
 *
 * @method AggregationBuilder autoDateHistogram(string $name, string $field, int $buckets = null, string $format = null)
 * @method AggregationBuilder children(string $name, string $children = null)
 * @method AggregationBuilder dateHistogram(string $name, string $field = null, string $interval = null, $format = null)
 * @method AggregationBuilder dateRange(string $name, string $field = null, string $format = null, array $ranges = [], bool $keyed = false)
 * @method AggregationBuilder diversifiedSampler(string $name, string $field = null, int $shardSize = null)
 * @method AggregationBuilder geoDistance(string $name, string $field = null, $origin = null, array $ranges = [], string $unit = null, string $distanceType = null)
 * @method AggregationBuilder geoHashGrid(string $name, string $field = null, int $precision = null, int $size = null, int $shardSize = null)
 * @method AggregationBuilder global(string $name)
 * @method AggregationBuilder histogram(string $name, string $field = null, int $interval = null, int $minDocCount = null, string $orderMode = null, string $orderDirection = 'asc', int $extendedBoundsMin = null, int $extendedBoundsMax = null, bool $keyed = null)
 * @method AggregationBuilder ipv4Range(string $name, string $field = null, array $ranges = [])
 * @method AggregationBuilder missing(string $name, string $field = null)
 * @method AggregationBuilder nested(string $name, string $path = null)
 * @method AggregationBuilder range(string $name, string $field = null, array $ranges = [], bool $keyed = false)
 * @method AggregationBuilder reverseNested(string $name, string $path = null)
 * @method AggregationBuilder sampler(string $name, string $field = null, int $shardSize = null)
 * @method AggregationBuilder significantTerms(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder significantText(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder terms(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder avg(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder cardinality(string $name)
 * @method AggregationBuilder extendedStats(string $name, string $field = null, int $sigma = null, string $script = null)
 * @method AggregationBuilder geoBounds(string $name, string $field = null, bool $wrapLongitude = true)
 * @method AggregationBuilder geoCentroid(string $name, string $field = null)
 * @method AggregationBuilder max(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder min(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder percentileRanks(string $name, string $field = null, array $values = null, string $script = null, int $compression = null)
 * @method AggregationBuilder percentiles(string $name, string $field = null, array $percents = null, string $script = null, int $compression = null)
 * @method AggregationBuilder scriptedMetric(string $name, $initScript = null, $mapScript = null, $combineScript = null, $reduceScript = null)
 * @method AggregationBuilder stats(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder sum(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder topHits(string $name, $size = null, $from = null, $sort = null)
 * @method AggregationBuilder valueCount(string $name, string $field = null, string $script = null)
 * @method AggregationBuilder avgBucket(string $name, $bucketsPath = null)
 * @method AggregationBuilder bucketScript(string $name, array $bucketsPath, string $script = null)
 * @method AggregationBuilder bucketSelector(string $name, array $bucketsPath, string $script = null)
 * @method AggregationBuilder bucketSort(string $name, string $bucketsPath = null)
 * @method AggregationBuilder cumulativeSum(string $name, $bucketsPath = null)
 * @method AggregationBuilder derivative(string $name, $bucketsPath = null)
 * @method AggregationBuilder extendedStatsBucket(string $name, $bucketsPath = null)
 * @method AggregationBuilder maxBucket(string $name, $bucketsPath = null)
 * @method AggregationBuilder minBucket(string $name, $bucketsPath = null)
 * @method AggregationBuilder movingAverage(string $name, $bucketsPath = null)
 * @method AggregationBuilder movingFunction(string $name, $bucketsPath = null)
 * @method AggregationBuilder percentilesBucket(string $name, $bucketsPath = null)
 * @method AggregationBuilder serialDifferencing(string $name, $bucketsPath = null)
 * @method AggregationBuilder statsBucket(string $name, $bucketsPath = null)
 * @method AggregationBuilder sumBucket(string $name, $bucketsPath = null)
 */
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
     * @param array  $arguments
     *
     * @return $this
     */
    public function add($type, $bucket, array $arguments = [])
    {
        $callback = null;

        if (2 === count($arguments) && is_array($arguments[0]) && is_callable($arguments[1])) {
            $callback = $arguments[1];
            $arguments = $arguments[0];
        }

        array_unshift($arguments, $bucket);

        $this->endpoint->add($agg = AggregationFactory::make($type, $arguments), $bucket);

        if ($callback) {
            call_user_func($callback, $agg);
        }

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
     * @param string     $bucket
     * @param bool       $anonymous
     * @param callable[] $callbacks
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
     * @param string     $bucket
     * @param callable[] $callbacks
     *
     * @return $this
     */
    public function adjacencyMatrix($bucket, array $callbacks = [])
    {
        $agg = new AdjacencyMatrixAggregation($bucket);

        foreach ($callbacks as $name => $callback) {
            call_user_func($callback, $query = new QueryBuilder());
            $agg->addFilter($name, $query->getSearchEndpoint()->getBool());
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

        return $this->add($name, $bucket, $arguments);
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
