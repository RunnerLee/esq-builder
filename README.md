# Esq Builder

### Usage

```php
require __DIR__ . '/vendor/autoload.php';

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
    });

echo json_encode($search->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";
```

Output:

```json
{
    "query": {
        "bool": {
            "should": [
                {
                    "term": {
                        "from": 123456
                    }
                },
                {
                    "term": {
                        "to": 123456
                    }
                }
            ]
        }
    },
    "aggregations": {
        "receiver": {
            "terms": {
                "field": "to"
            }
        },
        "midnight_notice": {
            "filter": {
                "bool": {
                    "must": [
                        {
                            "range": {
                                "created_at": {
                                    "gte": 1570721400,
                                    "lte": 1570768200
                                }
                            }
                        }
                    ],
                    "should": [
                        {
                            "term": {
                                "from": 10086
                            }
                        },
                        {
                            "term": {
                                "to": 10086
                            }
                        }
                    ]
                }
            }
        }
    }
}
```
