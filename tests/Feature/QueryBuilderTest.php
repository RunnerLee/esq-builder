<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Runner\EsqBuilder\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new QueryBuilder();
    }

    public function testQueryClauseWithBoolType()
    {
        $this->query
            ->term('foo', 'bar1')
            ->mustTerm('foo', 'bar2')
            ->shouldTerm('foo', 'bar3')
            ->shouldHasChild(
                'test_type',
                ['score_mode' => 'min'],
                function (QueryBuilder $builder) {
                    $builder->term('foo', 'bar');
                }
            )
            ->mustNotTerm('foo', 'bar4')
            ->filterTerm('foo', 'bar5')
            ->filterBool(function (QueryBuilder $builder) {
                $builder
                    ->shouldTerm('foo', 'bar1')
                    ->shouldTerm('foo', 'bar2');
            });

        $except = [
            'bool' => [
                'must' => [
                    [
                        'term' => ['foo' => 'bar1'],
                    ],
                    [
                        'term' => ['foo' => 'bar2'],
                    ],
                ],
                'should' => [
                    [
                        'term' => ['foo' => 'bar3'],
                    ],
                    [
                        'has_child' => [
                            'type'       => 'test_type',
                            'score_mode' => 'min',
                            'query'      => [
                                'term' => [
                                    'foo' => 'bar',
                                ],
                            ],
                        ],
                    ],
                ],
                'must_not' => [
                    [
                        'term' => ['foo' => 'bar4'],
                    ],
                ],
                'filter' => [
                    [
                        'term' => ['foo' => 'bar5'],
                    ],
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'term' => ['foo' => 'bar1'],
                                ],
                                [
                                    'term' => ['foo' => 'bar2'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($except, $this->query->toArray());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testUnknownQueryClause()
    {
        $this->query->holy();
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testUnknownQueryClauseWithBoolType()
    {
        $this->query->shouldHoly();
    }

    /**
     * @expectedException        \UnexpectedValueException
     * @expectedExceptionMessage The bool operator holy is not supported
     */
    public function testUnknow´nBoolType()
    {
        $this->query->bool(function (QueryBuilder $builder) {
            $builder->term('foo', 'bar');
        }, 'holy');
    }

    public function testHasEmptyChildBool()
    {
        $this->query->shouldBool(function () {});

        $expect = [
            'bool' => [
                'should' => [
                    [
                        'bool' => new \stdClass(),
                    ]
                ],
            ],
        ];

        $this->assertEquals($expect, $this->query->toArray());
    }
}
