<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Tests\Unit;

use ONGR\ElasticsearchDSL\Query\Geo\GeoShapeQuery;
use PHPUnit\Framework\TestCase;
use Runner\EsqBuilder\QueryBuilder;

class GeoQueryBuilderTest extends TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new QueryBuilder();
    }

    /**
     * @expectedException \LogicException
     */
    public function testGeoBoundingBoxQueryException()
    {
        $this
            ->query
            ->geoBoundingBox('location', [])
            ->toArray();
    }

    /**
     * @param string $field      Field name.
     * @param array  $values     Bounding box values.
     * @param array  $parameters Optional parameters.
     * @param array  $expected   Expected result.
     *
     * @dataProvider geoBoundingBoxDataProvider
     */
    public function testGeoBoundingBox($field, $values, $parameters, $expected)
    {
        $this->query->geoBoundingBox($field, $values, $parameters);

        $this->assertEquals(['geo_bounding_box' => $expected], $this->query->toArray());
    }

    /**
     * Tests toArray() method.
     *
     * @param string $field      Field name.
     * @param string $distance   Distance.
     * @param array  $location   Location.
     * @param array  $parameters Optional parameters.
     * @param array  $expected   Expected result.
     *
     * @dataProvider getGeoDistanceDataProvider
     */
    public function testGeoDistance($field, $distance, $location, $parameters, $expected)
    {
        $this->query->geoDistance($field, $distance, $location, $parameters);
        $this->assertEquals(['geo_distance' => $expected], $this->query->toArray());
    }

    /**
     * @param $field
     * @param $points
     * @param $parameters
     * @param $expected
     * @dataProvider getGeoPolygonDataProvider
     */
    public function testGeoPolygon($field, $points, $parameters, $expected)
    {
        $this->query->geoPolygon($field, $points, $parameters);
        $this->assertEquals(['geo_polygon' => $expected], $this->query->toArray());
    }

    public function testGeoShape()
    {
        $this->query->geoShape(
            [
                ['param1' => 'value1'],
            ],
            function (GeoShapeQuery $filter) {
                $filter->addShape('location', 'envelope', [[13, 53], [14, 52]], GeoShapeQuery::INTERSECTS);
            }
        );

        $expected = [
            'geo_shape' => [
                'location' => [
                    'shape' => [
                        'type'        => 'envelope',
                        'coordinates' => [[13, 53], [14, 52]],
                    ],
                    'relation' => 'intersects',
                ],
                'param1' => 'value1',
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testGeoShapeIndexed()
    {
        $this->query->geoShape(
            [
                ['param1' => 'value1'],
            ],
            function (GeoShapeQuery $filter) {
                $filter->addPreIndexedShape('location', 'DEU', 'countries', 'shapes', 'location', GeoShapeQuery::WITHIN);
            }
        );

        $expected = [
            'geo_shape' => [
                'location' => [
                    'indexed_shape' => [
                        'id'    => 'DEU',
                        'type'  => 'countries',
                        'index' => 'shapes',
                        'path'  => 'location',
                    ],
                    'relation' => 'within',
                ],
                'param1' => 'value1',
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    /**
     * @return array
     */
    public function geoBoundingBoxDataProvider()
    {
        return [
            // Case #1 (2 values).
            [
                'location',
                [
                    ['lat' => 40.73, 'lon' => -74.1],
                    ['lat' => 40.01, 'lon' => -71.12],
                ],
                ['parameter' => 'value'],
                [
                    'location' => [
                        'top_left'     => ['lat' => 40.73, 'lon' => -74.1],
                        'bottom_right' => ['lat' => 40.01, 'lon' => -71.12],
                    ],
                    'parameter' => 'value',
                ],
            ],
            // Case #2 (2 values with keys).
            [
                'location',
                [
                    'bottom_right' => ['lat' => 40.01, 'lon' => -71.12],
                    'top_left'     => ['lat' => 40.73, 'lon' => -74.1],
                ],
                ['parameter' => 'value'],
                [
                    'location' => [
                        'top_left'     => ['lat' => 40.73, 'lon' => -74.1],
                        'bottom_right' => ['lat' => 40.01, 'lon' => -71.12],
                    ],
                    'parameter' => 'value',
                ],
            ],
            // Case #2 (4 values).
            [
                'location',
                [40.73, -74.1, 40.01, -71.12],
                ['parameter' => 'value'],
                [
                    'location' => [
                        'top'    => 40.73,
                        'left'   => -74.1,
                        'bottom' => 40.01,
                        'right'  => -71.12,
                    ],
                    'parameter' => 'value',
                ],
            ],
            // Case #3 (4 values with keys).
            [
                'location',
                [
                    // out of order
                    'right'  => -71.12,
                    'bottom' => 40.01,
                    'top'    => 40.73,
                    'left'   => -74.1,
                ],
                ['parameter' => 'value'],
                [
                    'location' => [
                        'top'    => 40.73,
                        'left'   => -74.1,
                        'bottom' => 40.01,
                        'right'  => -71.12,
                    ],
                    'parameter' => 'value',
                ],
            ],
        ];
    }

    public function getGeoDistanceDataProvider()
    {
        return [
            // Case #1.
            [
                'location',
                '200km',
                ['lat' => 40, 'lon' => -70],
                [],
                ['distance' => '200km', 'location' => ['lat' => 40, 'lon' => -70]],
            ],
            // Case #2.
            [
                'location',
                '20km',
                ['lat'       => 0, 'lon' => 0],
                ['parameter' => 'value'],
                ['distance'  => '20km', 'location' => ['lat' => 0, 'lon' => 0], 'parameter' => 'value'],
            ],
        ];
    }

    public function getGeoPolygonDataProvider()
    {
        return [
            // Case #1.
            [
                'location',
                [
                    ['lat' => 20, 'lon' => -80],
                    ['lat' => 30, 'lon' => -40],
                    ['lat' => 70, 'lon' => -90],
                ],
                [],
                [
                    'location' => [
                        'points' => [
                            ['lat' => 20, 'lon' => -80],
                            ['lat' => 30, 'lon' => -40],
                            ['lat' => 70, 'lon' => -90],
                        ],
                    ],
                ],
            ],
            // Case #2.
            [
                'location',
                [],
                ['parameter' => 'value'],
                [
                    'location'  => ['points' => []],
                    'parameter' => 'value',
                ],
            ],
            // Case #3.
            [
                'location',
                [
                    ['lat' => 20, 'lon' => -80],
                ],
                ['parameter' => 'value'],
                [
                    'location' => [
                        'points' => [['lat' => 20, 'lon' => -80]],
                    ],
                    'parameter' => 'value',
                ],
            ],
        ];
    }
}
