<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Tests\Unit\QueryBuilder;

use PHPUnit\Framework\TestCase;
use Runner\EsqBuilder\QueryBuilder;

class JoiningTest extends TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new QueryBuilder();
    }

    public function testHasChild()
    {
        $this->query->hasChild(
            'test_type',
            ['score_mode' => 'min'],
            function (QueryBuilder $builder) {
                $builder->term('foo', 'bar');
            }
        );

        $expected = [
            'has_child' => [
                'type'       => 'test_type',
                'score_mode' => 'min',
                'query'      => [
                    'term' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testHasParent()
    {
        $this->query->hasParent('test_type', ['score' => true], function (QueryBuilder $builder) {
            $builder->term('foo', 'bar');
        });

        $expected = [
            'has_parent' => [
                'parent_type' => 'test_type',
                'score'       => true,
                'query'       => [
                    'term' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    /**
     * @param $path
     * @param $parameters
     * @param $expected
     * @dataProvider getNestedQueryDataProvider
     */
    public function testNested($path, $parameters, $expected)
    {
        $this->query->nested($path, $parameters, function (QueryBuilder $builder) {
            $builder->terms('foo', 'bar');
        });

        $this->assertEquals(['nested' => $expected], $this->query->toArray());
    }

    public function testParentId()
    {
        $this->query->parentId(1, 'test-child', [
            'ignore_unmapped' => true,
        ]);

        $except = [
            'parent_id' => [
                'id'              => 1,
                'type'            => 'test-child',
                'ignore_unmapped' => true,
            ],
        ];
        $this->assertEquals($except, $this->query->toArray());
    }

    public function getNestedQueryDataProvider()
    {
        $query = [
            'terms' => [
                'foo' => 'bar',
            ],
        ];

        return [
            'query_only' => [
                'product.sub_item',
                [],
                ['path' => 'product.sub_item', 'query' => $query],
            ],
            'query_with_parameters' => [
                'product.sub_item',
                ['_cache' => true, '_name' => 'named_result'],
                [
                    'path'   => 'product.sub_item',
                    'query'  => $query,
                    '_cache' => true,
                    '_name'  => 'named_result',
                ],
            ],
        ];
    }
}
