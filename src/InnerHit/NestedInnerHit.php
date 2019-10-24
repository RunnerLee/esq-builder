<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\InnerHit;

use ONGR\ElasticsearchDSL\NameAwareTrait;
use ONGR\ElasticsearchDSL\NamedBuilderInterface;
use Runner\EsqBuilder\SearchBuilder;

class NestedInnerHit implements NamedBuilderInterface
{
    use NameAwareTrait;

    /**
     * @var SearchBuilder
     */
    protected $search;

    /**
     * @var string
     */
    protected $path;

    /**
     * NestedInnerHit constructor.
     * @param string $name
     * @param string $path
     * @param SearchBuilder|null $search
     */
    public function __construct($name, $path, SearchBuilder $search = null)
    {
        $this->name = $name;
        $this->path = $path;

        $this->search = $search;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $out = $this->search ? $this->search->toArray() : new \stdClass();

        $out = [
            $this->getPathType() => [
                $this->path => $out,
            ],
        ];

        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'nested';
    }

    /**
     * Returns 'path' for nested and 'type' for parent inner hits.
     *
     * @return null|string
     */
    private function getPathType()
    {
        switch ($this->getType()) {
            case 'nested':
                return 'path';
            case 'parent':
                return 'type';
            default:
                return '';
        }
    }
}
