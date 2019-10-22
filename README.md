# Esq Builder

基于 [ongr/elasticsearch-dsl](https://github.com/ongr-io/ElasticsearchDSL) 做的封装。

### Usage

```php
<?php

use Runner\EsqBuilder\AggregationBuilder;
use Runner\EsqBuilder\QueryBuilder;
use Runner\EsqBuilder\SearchBuilder;


$search = new SearchBuilder();

$search->query()
    ->shouldTerm('from', 123456)
    ->shouldTerm('to', 123456);

$search->aggregations()
    ->terms('receiver', 'to')
    ->filter('midnight_notice', function (QueryBuilder $builder) {
        $builder
            ->range('created_at', [
                'gte' => strtotime('2019-10-10 23:30'),
                'lte' => strtotime('2019-10-11 12:30'),
            ])
            ->shouldTerm('from', 10086)
            ->shouldTerm('to', 10086);
    })
    ->range('aaa', 'created_at', [
        [
            'from' => 1,
            'to' => 100,
        ]
    ]);

$search->setSize(10);
$search->setFrom(10);

echo json_encode($search->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";
```
