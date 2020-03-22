<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder;

use Elasticsearch\Client;
use ONGR\ElasticsearchDSL\SearchEndpoint\AggregationsEndpoint;
use ONGR\ElasticsearchDSL\SearchEndpoint\HighlightEndpoint;
use ONGR\ElasticsearchDSL\SearchEndpoint\QueryEndpoint;
use ONGR\ElasticsearchDSL\SearchEndpoint\SortEndpoint;
use Runner\EsqBuilder\Contracts\BuilderInterface;

/**
 * Class SearchBuilder.
 *
 * @method QueryBuilder       query()
 * @method AggregationBuilder aggregations()
 * @method HighlightBuilder highlight()
 * @method SortBuilder sort()
 * @method SearchBuilder setFrom($from)
 * @method SearchBuilder setSize($size)
 * @method SearchBuilder setSource($source)
 * @method SearchBuilder setStoredFields($argument)
 * @method SearchBuilder setScriptFields($argument)
 * @method SearchBuilder setDocValueFields($argument)
 * @method SearchBuilder setExplain($explain)
 * @method SearchBuilder setVersion($version)
 * @method SearchBuilder setIndicesBoost($argument)
 * @method SearchBuilder setMinScore($score)
 * @method SearchBuilder setSearchAfter($argument)
 * @method SearchBuilder setTrackTotalHits($argument)
 */
class SearchBuilder
{
    /**
     * @var BuilderInterface[]
     */
    protected $builders = [];

    /**
     * @var Client|null
     */
    protected $client;

    /**
     * @var array
     */
    protected $clauses = [
        QueryEndpoint::NAME        => QueryBuilder::class,
        AggregationsEndpoint::NAME => AggregationBuilder::class,
        HighlightEndpoint::NAME    => HighlightBuilder::class,
        SortEndpoint::NAME         => SortBuilder::class,
    ];

    /**
     * @var array
     */
    protected $parameters = [
        'from'           => null,
        'size'           => null,
        'source'         => null,
        'storedFields'   => null,
        'scriptFields'   => null,
        'docValueFields' => null,
        'explain'        => null,
        'version'        => null,
        'indicesBoost'   => null,
        'minScore'       => null,
        'searchAfter'    => null,
        'trackTotalHits' => null,
    ];

    /**
     * @param $name
     * @return BuilderInterface
     */
    public function getBuilder($name)
    {
        if (!isset($this->builders[$name])) {
            $this->builders[$name] = new $this->clauses[$name]();
        }

        return $this->builders[$name];
    }

    /**
     * @return BuilderInterface[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return array_filter($this->parameters, function ($value) {
            return null !== $value;
        });
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param string $index
     * @param string $type
     * @param array $parameters
     * @return array
     */
    public function search($index, $type = '_doc', array $parameters = [])
    {
        $parameters = array_merge($parameters, [
            'index' => $index,
            'type' => $type,
            'body' => $this->toArray(),
        ]);

        return $this->client->search($parameters);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return $this|BuilderInterface
     */
    public function __call($name, $arguments)
    {
        if ('set' === substr($name, 0, 3)) {
            if (array_key_exists($key = lcfirst(substr($name, 3)), $this->parameters)) {
                return $this->setParameter($key, $arguments[0]);
            }
        }

        if (array_key_exists($name, $this->clauses)) {
            return $this->getBuilder($name);
        }

        throw new \BadMethodCallException(
            sprintf('Call to undefined method %s::%s()', static::class, $name)
        );
    }

    public function toArray()
    {
        $output = array_filter(Serializer::normalize(array_map(
            function (BuilderInterface $builder) {
                return $builder->getSearchEndpoint();
            },
            $this->builders
        )));

        $params = [
            'from'           => 'from',
            'size'           => 'size',
            'source'         => '_source',
            'storedFields'   => 'stored_fields',
            'scriptFields'   => 'script_fields',
            'docValueFields' => 'docvalue_fields',
            'explain'        => 'explain',
            'version'        => 'version',
            'indicesBoost'   => 'indices_boost',
            'minScore'       => 'min_score',
            'searchAfter'    => 'search_after',
            'trackTotalHits' => 'track_total_hits',
        ];

        foreach ($params as $field => $param) {
            if (null !== $this->parameters[$field]) {
                $output[$param] = $this->parameters[$field];
            }
        }

        return $output;
    }
}
