<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Factories;

class AggregationFactory extends AbstractFactory
{
    protected static $clauses = [
        'AdjacencyMatrix'    => 'Bucketing',
        'AutoDateHistogram'  => 'Bucketing',
        'Children'           => 'Bucketing',
        'DateHistogram'      => 'Bucketing',
        'DateRange'          => 'Bucketing',
        'DiversifiedSampler' => 'Bucketing',
        'GeoDistance'        => 'Bucketing',
        'GeoHashGrid'        => 'Bucketing',
        'Global'             => 'Bucketing',
        'Histogram'          => 'Bucketing',
        'Ipv4Range'          => 'Bucketing',
        'Missing'            => 'Bucketing',
        'Nested'             => 'Bucketing',
        'Range'              => 'Bucketing',
        'ReverseNested'      => 'Bucketing',
        'Sampler'            => 'Bucketing',
        'SignificantTerms'   => 'Bucketing',
        'SignificantText'    => 'Bucketing',
        'Terms'              => 'Bucketing',
    ];

    protected static $namespace = 'ONGR\\ElasticsearchDSL\\Aggregation';

    protected static $suffix = 'Aggregation';
}
