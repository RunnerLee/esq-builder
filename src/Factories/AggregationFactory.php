<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Factories;

class AggregationFactory extends AbstractFactory
{
    public static $clauses = [
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

        //        'Max'         => 'Matrix',
        'Avg'             => 'Metric',
        'Cardinality'     => 'Metric',
        'ExtendedStats'   => 'Metric',
        'GeoBounds'       => 'Metric',
        'GeoCentroid'     => 'Metric',
        'Max'             => 'Metric',
        'Min'             => 'Metric',
        'PercentileRanks' => 'Metric',
        'Percentiles'     => 'Metric',
        'ScriptedMetric'  => 'Metric',
        'Stats'           => 'Metric',
        'Sum'             => 'Metric',
        'TopHits'         => 'Metric',
        'ValueCount'      => 'Metric',

        'AvgBucket'           => 'Pipeline',
        'BucketScript'        => 'Pipeline',
        'BucketSelector'      => 'Pipeline',
        'BucketSort'          => 'Pipeline',
        'CumulativeSum'       => 'Pipeline',
        'Derivative'          => 'Pipeline',
        'ExtendedStatsBucket' => 'Pipeline',
        'MaxBucket'           => 'Pipeline',
        'MinBucket'           => 'Pipeline',
        'MovingAverage'       => 'Pipeline',
        'MovingFunction'      => 'Pipeline',
        'PercentilesBucket'   => 'Pipeline',
        'SerialDifferencing'  => 'Pipeline',
        'StatsBucket'         => 'Pipeline',
        'SumBucket'           => 'Pipeline',
    ];

    protected static $namespace = 'ONGR\\ElasticsearchDSL\\Aggregation';

    protected static $suffix = 'Aggregation';
}
