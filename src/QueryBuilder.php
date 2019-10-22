<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder;

use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\Compound\ConstantScoreQuery;
use ONGR\ElasticsearchDSL\Query\Compound\DisMaxQuery;
use ONGR\ElasticsearchDSL\Query\Compound\FunctionScoreQuery;
use ONGR\ElasticsearchDSL\Query\Joining\HasChildQuery;
use ONGR\ElasticsearchDSL\Query\Joining\HasParentQuery;
use ONGR\ElasticsearchDSL\Query\Joining\NestedQuery;
use ONGR\ElasticsearchDSL\SearchEndpoint\QueryEndpoint;
use ONGR\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface;
use Runner\EsqBuilder\Contracts\BuilderInterface;
use Runner\EsqBuilder\Factories\QueryFactory;

/**
 * Class QueryBuilder.
 *
 * @method QueryBuilder term($field, $value, array $parameters = [])
 */
class QueryBuilder implements BuilderInterface
{
    protected $endpoint;

    /**
     * QueryBuilder constructor.
     */
    public function __construct()
    {
        $this->endpoint = new QueryEndpoint();
    }

    public function getSearchEndpoint(): SearchEndpointInterface
    {
        return $this->endpoint;
    }

    public function where($boolType, $type, array $arguments = []): self
    {
        if ('bool' === $type) {
            return $this->bool($arguments[0], $boolType);
        }

        if (2 === count($arguments) && is_array($arguments[0]) && is_callable($arguments[1])) {
            call_user_func($arguments[1], $query = QueryFactory::make($type, $arguments[0]));
            $this->endpoint->addToBool($query, $boolType);
        } else {
            $this->endpoint->addToBool(QueryFactory::make($type, $arguments), $boolType);
        }

        return $this;
    }

    public function bool(callable $callback, $type = BoolQuery::MUST): self
    {
        $callback($query = new static());

        $this->endpoint->addToBool($query->getSearchEndpoint()->getBool(), $type);

        return $this;
    }

    public function constantScore(array $parameters, callable $callback, $boolType = BoolQuery::MUST): self
    {
        $callback($query = new static());

        $this->endpoint->addToBool(
            new ConstantScoreQuery($query->getSearchEndpoint()->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    public function disMax(array $parameters, array $queries, $boolType = BoolQuery::MUST): self
    {
        $query = new DisMaxQuery($parameters);

        foreach ($queries as $item) {
            $query->addQuery($item);
        }

        $this->endpoint->addToBool($query, $boolType);

        return $this;
    }

    public function functionScore(array $parameters, callable $callback, $boolType = BoolQuery::MUST): self
    {
        $query = $this->runNestedQuery($callback);

        $this->endpoint->addToBool(
            new FunctionScoreQuery($query->getSearchEndpoint()->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    public function hasChild($type, array $parameters, callable $callback, $boolType = BoolQuery::MUST)
    {
        $query = $this->runNestedQuery($callback);

        $this->endpoint->addToBool(
            new HasChildQuery($type, $query->getSearchEndpoint()->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    public function hasParent($type, array $parameters, callable $callback, $boolType = BoolQuery::MUST)
    {
        $query = $this->runNestedQuery($callback);

        $this->endpoint->addToBool(
            new HasParentQuery($type, $query->getSearchEndpoint()->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    public function nested($path, array $parameters, callable $callback, $boolType = BoolQuery::MUST)
    {
        $query = $this->runNestedQuery($callback);

        $this->endpoint->addToBool(
            new NestedQuery($path, $query->getSearchEndpoint()->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (!$expression = $this->parseExpression($name)) {
            throw new \BadMethodCallException(
                sprintf('Call to undefined method %s::%s()', static::class, $name)
            );
        }

        [$clause, $boolType] = $expression;

        return $this->where($boolType, $clause, $arguments);
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    protected function runNestedQuery(callable $callback)
    {
        call_user_func($callback, $query = new static());

        return $query;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    protected function parseExpression($name)
    {
        'mustNot' === substr($name, 0, 7) && ($name = str_replace('mustNot', 'must_not', $name));

        if (
            0 === preg_match('/^(?<type>must|must_not|should|filter)?(?<clause>[A-Za-z]+)$/', $name, $match)
            || !QueryFactory::has($match['clause'])
        ) {
            return [];
        }

        return [
            lcfirst($match['clause']), $match['type'] ?: BoolQuery::MUST,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->endpoint->getBool()->toArray();
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
