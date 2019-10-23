<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder;

use ONGR\ElasticsearchDSL\Highlight\Highlight;
use ONGR\ElasticsearchDSL\SearchEndpoint\HighlightEndpoint;
use ONGR\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface;
use Runner\EsqBuilder\Contracts\BuilderInterface;

/**
 * Class HighlightBuilder.
 *
 * @method HighlightBuilder addParameter($name, $value)
 * @method HighlightBuilder setParameters(array $parameters)
 * @method HighlightBuilder setTags(array $preTags, array $postTags)
 */
class HighlightBuilder implements BuilderInterface
{
    /**
     * @var HighlightEndpoint
     */
    protected $endpoint;

    /**
     * @var Highlight
     */
    protected $highlight;

    public function __construct()
    {
        $this->highlight = new Highlight();
        $this->endpoint = new HighlightEndpoint();
        $this->endpoint->add($this->highlight);
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function highlightQuery(callable $callback)
    {
        call_user_func($callback, $query = new QueryBuilder());

        $this->highlight->addParameter(
            'highlight_query',
            $query->getSearchEndpoint()->getBool()->toArray()
        );

        return $this;
    }

    /**
     * @param string        $name
     * @param callable|null $callback
     *
     * @return $this
     */
    public function addField($name, callable $callback = null)
    {
        $params = [];
        if ($callback) {
            call_user_func($callback, $builder = new static());
            $params = $builder->toArray();
        }

        $this->highlight->addField($name, $params);

        return $this;
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
        if (!$this->highlight) {
            return [];
        }

        return $this->highlight->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return '';
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this->highlight, $name)) {
            throw new \BadMethodCallException(
                sprintf('Call to undefined method %s::%s()', static::class, $name)
            );
        }

        call_user_func_array([$this->highlight, $name], $arguments);

        return $this;
    }
}
