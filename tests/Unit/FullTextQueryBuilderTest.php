<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Runner\EsqBuilder\QueryBuilder;

class FullTextQueryBuilderTest extends TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new QueryBuilder();
    }

    public function testCommonTerms()
    {
        $this->query->commonTerms('body', 'this is bonsai cool', ['cutoff_frequency' => 0.01]);
        $expected = [
            'common' => [
                'body' => [
                    'query'            => 'this is bonsai cool',
                    'cutoff_frequency' => 0.01,
                ],
            ],
        ];
        $this->assertSame($this->query->toArray(), $expected);
    }

    public function testMatchPhrasePrefix()
    {
        $this->query->matchPhrasePrefix('message', 'this is a test');
        $expected = [
            'match_phrase_prefix' => [
                'message' => [
                    'query' => 'this is a test',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testMatchPhraseQuery()
    {
        $this->query->matchPhrase('message', 'this is a test');
        $expected = [
            'match_phrase' => [
                'message' => [
                    'query' => 'this is a test',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testMatch()
    {
        $this->query->match('message', 'this is a test');
        $expected = [
            'match' => [
                'message' => [
                    'query' => 'this is a test',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testMultiMatch()
    {
        $this->query->multiMatch(['message', 'title'], 'this is a test');
        $expected = [
            'multi_match' => [
                'query'  => 'this is a test',
                'fields' => ['message', 'title'],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testMultiMatchWithNoFields()
    {
        $this->query->multiMatch([], 'this is a test');
        $expected = [
            'multi_match' => [
                'query' => 'this is a test',
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testQueryString()
    {
        $this->query->queryString('this AND that OR thus');
        $expected = [
            'query_string' => [
                'query' => 'this AND that OR thus',
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testSimpleQueryString()
    {
        $this->query->simpleQueryString('"fried eggs" +(eggplant | potato) -frittata');
        $expected = [
            'simple_query_string' => [
                'query' => '"fried eggs" +(eggplant | potato) -frittata',
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }
}
