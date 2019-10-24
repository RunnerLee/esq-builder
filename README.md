# Esq Builder

基于 [ongr/elasticsearch-dsl](https://github.com/ongr-io/ElasticsearchDSL) 做的 Search 封装。

### Usage

最简单的使用：

```php
<?php

use Runner\EsqBuilder\SearchBuilder;
use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()->build();
$search = new SearchBuilder();

$search
    ->setFrom(10)
    ->setSize(10)
    ->setSource([
        'foo',
        'bar',
    ]);

$client->search([
    'index' => 'your_index',
    'body' => $search->toArray(),
]);
```

##### Query

```php
<?php

use Runner\EsqBuilder\QueryBuilder;

$search = new SearchBuilder();

// 默认的布尔类型为 must
$search
    ->query()
    ->term('foo', 'bar')
    ->range('age', [
        'gte' => 18,
    ]);

// 也可以指定布尔类型
$search->query()->shouldTerm('foo', 'bar');

// 嵌套布尔
$search->query()->bool(function (QueryBuilder $builder) {
    $builder->term('bar', 'foo');
});
```

##### Aggregation

```php
<?php

use Runner\EsqBuilder\AggregationBuilder;

$search = new SearchBuilder();

$search
    ->aggregation()
    ->terms('aa', 'foo')
    ->filter('bb', function (QueryBuilder $builder) {
        $builder->match('foo', 'bar');
    });

// 嵌套桶, 需要先创建桶再调用嵌套
$search
    ->aggregation()
    ->aggregation('aa', function (AggregationBuilder $builder) {
        $builder->terms('bb', 'bar');
    });
```

##### Highlight

```php
<?php

use Runner\EsqBuilder\HighlightBuilder;
use Runner\EsqBuilder\QueryBuilder;

$search
    ->highlight()
    ->addParameter('tags_schema', 'styled')
    ->highlightQuery(function (QueryBuilder $builder) {
        $builder->term('from', 123);
        $builder->term('to', 321);
    })
    ->addField('from', function (HighlightBuilder $builder) {
        $builder->addParameter('fragment_size', 150);
    });
```

##### Sort

```php
<?php

use Runner\EsqBuilder\QueryBuilder;

$search
    ->sort()
    ->fieldSort(
        'createdTime',
        'desc',
        [],
        $search->sort()->createdNestedSort('aaa', [], function (QueryBuilder $builder) {
            $builder->term('foo', 'bar');
        })
    );
```

输出

```json
{
    "sort": [
        {
            "createdTime": {
                "order": "desc",
                "nested": {
                    "path": "aaa",
                    "filter": {
                        "term": {
                            "foo": "bar"
                        }
                    }
                }
            }
        }
    ]
}
```



