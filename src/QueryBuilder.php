<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder;

use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\Compound\BoostingQuery;
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
use Runner\EsqBuilder\InnerHit\NestedInnerHit;
use Runner\EsqBuilder\InnerHit\ParentInnerHit;

/**
 * Class QueryBuilder.
 *
 * @method QueryBuilder commonTerms(string $field, string $query, array $parameters = [])
 * @method QueryBuilder matchPhrase(string $field, string $query, array $parameters = [])
 * @method QueryBuilder matchPhrasePrefix(string $field, string $query, array $parameters = [])
 * @method QueryBuilder match(string $field, string $query, array $parameters = [])
 * @method QueryBuilder multiMatch(array $fields, string $query, array $parameters = [])
 * @method QueryBuilder queryString(string $query, array $parameters = [])
 * @method QueryBuilder simpleQueryString(string $query, array $parameters = [])
 * @method QueryBuilder geoBoundingBox(string $field, array $values, array $parameters = [])
 * @method QueryBuilder geoDistance(string $field, string $distance, $location, array $parameters = [])
 * @method QueryBuilder geoPolygon(string $field, array $points = [], array $parameters = [])
 * @method QueryBuilder geoShape(array $parameters = [])
 * @method QueryBuilder parentId(string $parentId, string $childType, array $parameters = [])
 * @method QueryBuilder moreLikeThis(string $like, array $parameters = [])
 * @method QueryBuilder script(string $script, array $parameters = [])
 * @method QueryBuilder template(string $file = null, string $inline = null, array $params = [])
 * @method QueryBuilder exists(string $field)
 * @method QueryBuilder fuzzy(string $field, string $value, array $parameters = [])
 * @method QueryBuilder ids(array $values, array $parameters = [])
 * @method QueryBuilder prefix(string $field, string $value, array $parameters = [])
 * @method QueryBuilder range(string $field, array $parameters = [])
 * @method QueryBuilder regexp(string $field, string $regexpValue, array $parameters = [])
 * @method QueryBuilder term(string $field, $value, array $parameters = [])
 * @method QueryBuilder terms(string $field, array $terms, array $parameters = [])
 * @method QueryBuilder type(string $type)
 * @method QueryBuilder wildcard(string $field, string $value, array $parameters = [])
 * @method QueryBuilder matchAll(array $parameters = [])
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

    /**
     * @return SearchEndpointInterface
     */
    public function getSearchEndpoint(): SearchEndpointInterface
    {
        return $this->endpoint;
    }

    /**
     * @param string $boolType
     * @param string $type
     * @param array  $arguments
     *
     * @return $this
     */
    public function where($boolType, $type, array $arguments = []): self
    {
        if ('bool' === $type) {
            return $this->bool($arguments[0], $boolType);
        }

        if (method_exists($this, $type)) {
            $arguments[] = $boolType;

            return $this->$type(...$arguments);
        }

        if (2 === count($arguments) && is_array($arguments[0]) && is_callable($arguments[1])) {
            call_user_func($arguments[1], $query = QueryFactory::make($type, $arguments[0]));
            $this->endpoint->addToBool($query, $boolType);
        } else {
            $this->endpoint->addToBool(QueryFactory::make($type, $arguments), $boolType);
        }

        return $this;
    }

    /**
     * @param callable $callback
     * @param string   $boolType
     *
     * @return $this
     */
    public function bool(callable $callback, $boolType = BoolQuery::MUST): self
    {
        $query = $this->runNestedQuery($callback);

        if ($bool = $query->getBool()) {
            $this->endpoint->addToBool($bool, $boolType);
        }

        return $this;
    }

    /**
     * @param $negativeBoost
     * @param callable $positiveBuilder
     * @param callable $negativeBuilder
     * @param string $boolType
     * @return $this
     */
    public function boosting(
        $negativeBoost,
        callable $positiveBuilder,
        callable $negativeBuilder,
        $boolType = BoolQuery::MUST
    ) {
        $this->endpoint->addToBool(
            new BoostingQuery(
                $this->runNestedQuery($positiveBuilder)->getBool(),
                $this->runNestedQuery($negativeBuilder)->getBool(),
                $negativeBoost
            ),
            $boolType
        );

        return $this;
    }

    /**
     * @param array    $parameters
     * @param callable $callback
     * @param string   $boolType
     *
     * @return $this
     */
    public function constantScore(array $parameters, callable $callback, $boolType = BoolQuery::MUST): self
    {
        $this->endpoint->addToBool(
            new ConstantScoreQuery($this->runNestedQuery($callback)->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    /**
     * @param array      $parameters
     * @param callable[] $queries
     * @param string     $boolType
     *
     * @return $this
     */
    public function disMax(array $parameters, array $queries, $boolType = BoolQuery::MUST): self
    {
        $query = new DisMaxQuery($parameters);

        foreach ($queries as $callback) {
            $query->addQuery($this->runNestedQuery($callback)->getBool());
        }

        $this->endpoint->addToBool($query, $boolType);

        return $this;
    }

    /**
     * @param array    $parameters
     * @param callable $callback
     * @param string   $boolType
     *
     * @return $this
     */
//    public function functionScore(array $parameters, callable $callback, $boolType = BoolQuery::MUST): self
//    {
//        $query = $this->runNestedQuery($callback);
//
//        $this->endpoint->addToBool(
//            new FunctionScoreQuery($query->getBool(), $parameters),
//            $boolType
//        );
//
//        return $this;
//    }

    /**
     * @param string   $type
     * @param array    $parameters
     * @param callable $callback
     * @param string   $boolType
     *
     * @return $this
     */
    public function hasChild($type, array $parameters, callable $callback, $boolType = BoolQuery::MUST)
    {
        $query = $this->runNestedQuery($callback);

        $this->endpoint->addToBool(
            new HasChildQuery($type, $query->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    /**
     * @param string   $type
     * @param array    $parameters
     * @param callable $callback
     * @param string   $boolType
     *
     * @return $this
     */
    public function hasParent($type, array $parameters, callable $callback, $boolType = BoolQuery::MUST)
    {
        $query = $this->runNestedQuery($callback);

        $this->endpoint->addToBool(
            new HasParentQuery($type, $query->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    /**
     * @param string   $path
     * @param array    $parameters
     * @param callable $callback
     * @param string   $boolType
     *
     * @return $this
     */
    public function nested($path, array $parameters, callable $callback, $boolType = BoolQuery::MUST)
    {
        $query = $this->runNestedQuery($callback);

        $this->endpoint->addToBool(
            new NestedQuery($path, $query->getBool(), $parameters),
            $boolType
        );

        return $this;
    }

    /**
     * @param string   $name
     * @param string   $path
     * @param callable $callback
     * @param string   $boolType
     *
     * @return $this
     */
    public function nestedInnerHit($name, $path, callable $callback, $boolType = BoolQuery::MUST)
    {
        call_user_func($callback, $builder = new SearchBuilder());

        $this->endpoint->addToBool(new NestedInnerHit($name, $path, $builder), $boolType);

        return $this;
    }

    /**
     * @param string   $name
     * @param string   $path
     * @param callable $callback
     * @param string   $boolType
     *
     * @return $this
     */
    public function parentInnerHit($name, $path, callable $callback, $boolType = BoolQuery::MUST)
    {
        call_user_func($callback, $builder = new SearchBuilder());

        $this->endpoint->addToBool(new ParentInnerHit($name, $path, $builder), $boolType);

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

        if (0 === preg_match('/^(?<type>must|must_not|should|filter)?(?<clause>[A-Za-z]+)$/', $name, $match)) {
            return [];
        }

        $clause = lcfirst($match['clause']);

        if (!QueryFactory::has($clause) && !method_exists($this, $clause)) {
            return [];
        }

        return [
            $clause, $match['type'] ?: BoolQuery::MUST,
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
     * @return BoolQuery
     */
    public function getBool()
    {
        return $this->endpoint->getBool();
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
