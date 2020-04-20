<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2020-04
 */

namespace Runner\EsqBuilder\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Runner\EsqBuilder\QueryBuilder;
use Runner\EsqBuilder\SortBuilder;

class SortBuilderTest extends TestCase
{
    /**
     * @var SortBuilder
     */
    protected $builder;

    public function setUp()
    {
        $this->builder = new SortBuilder();
    }

    public function testFieldSort()
    {
        $this
            ->builder
            ->fieldSort('id', 'desc')
            ->fieldSort('oid', 'asc', [
                'mode' => 'avg',
            ]);
        $expect = [
            [
                'id' => [
                    'order' => 'desc',
                ],
            ],
            [
                'oid' => [
                    'order' => 'asc',
                    'mode' => 'avg',
                ],
            ]
        ];

        $this->assertEquals($expect, $this->builder->toArray());
    }

    public function testSingleNestedSort()
    {
        $nested = $this->builder->createdNestedSort(
            'offer',
            [],
            function (QueryBuilder $query) {
                $query->term('offer.color', 'blue');
            }
        );
        $this->builder->fieldSort('offer.price', 'asc', ['mode' => 'avg',], $nested);

        $expect = [
            [
                'offer.price' => [
                    'mode' => 'avg',
                    'order' => 'asc',
                    'nested' => [
                        'path' => 'offer',
                        'filter' => [
                            'term' => [
                                'offer.color' => 'blue',
                            ],
                        ]
                    ],
                ],
            ]
        ];
        $this->assertEquals($expect, $this->builder->toArray());
    }
}
