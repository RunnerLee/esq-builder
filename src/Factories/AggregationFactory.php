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
        'Terms'              => 'Bucketing',
    ];

    protected static $namespace = 'ONGR\\ElasticsearchDSL\\Aggregation';

    protected static $suffix = 'Aggregation';
}
