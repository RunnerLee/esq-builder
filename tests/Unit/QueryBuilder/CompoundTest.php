<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Tests\Unit\QueryBuilder;

use PHPUnit\Framework\TestCase;
use Runner\EsqBuilder\QueryBuilder;

class CompoundTest extends TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new QueryBuilder();
    }

    public function testBoosting()
    {
        $this->query->boosting(
            0.2,
            function (QueryBuilder $builder) {
                $builder->term('foo', 'bar1');
            },
            function (QueryBuilder $builder) {
                $builder->term('foo', 'bar2');
            }
        );

        $expected = [
            'boosting' => [
                'positive'       => ['term' => ['foo' => 'bar1']],
                'negative'       => ['term' => ['foo' => 'bar2']],
                'negative_boost' => 0.2,
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testConstantScore()
    {
        $this->query->constantScore(['boost' => 1.2], function (QueryBuilder $builder) {
            $builder->term('foo', 'bar');
        });

        $expected = [
            'constant_score' => [
                'filter' => [
                    'term' => ['foo' => 'bar'],
                ],
                'boost' => 1.2,
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testDisMax()
    {
        $this->query->disMax(['boost' => 1.2], [
            function (QueryBuilder $builder) {
                $builder->term('foo', 'bar1');
            },
            function (QueryBuilder $builder) {
                $builder->term('foo', 'bar2');
            },
        ]);

        $expected = [
            'dis_max' => [
                'queries' => [
                    ['term' => ['foo' => 'bar1']],
                    ['term' => ['foo' => 'bar2']],
                ],
                'boost' => 1.2,
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }
}
