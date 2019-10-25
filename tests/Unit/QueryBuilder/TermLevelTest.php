<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Tests\Unit\QueryBuilder;

use PHPUnit\Framework\TestCase;
use Runner\EsqBuilder\QueryBuilder;

class TermLevelTest extends TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new QueryBuilder();
    }

    public function testExists()
    {
        $this->query->exists('bar');
        $this->assertEquals(['exists' => ['field' => 'bar']], $this->query->toArray());
    }

    public function testFuzzy()
    {
        $this->query->fuzzy('user', 'ki', ['boost' => 1.2]);
        $expected = [
            'fuzzy' => [
                'user' => [
                    'value' => 'ki',
                    'boost' => 1.2,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testIds()
    {
        $this->query->ids(['foo', 'bar']);
        $expected = [
            'ids' => [
                'values' => ['foo', 'bar'],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testPrefix()
    {
        $this->query->prefix('user', 'ki');
        $expected = [
            'prefix' => [
                'user' => [
                    'value' => 'ki',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testRange()
    {
        $this->query->range('age', ['gte' => 10, 'lte' => 20]);
        $expected = [
            'range' => [
                'age' => [
                    'gte' => 10,
                    'lte' => 20,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testRegexp()
    {
        $this->query->regexp('user', 's.*y');
        $expected = [
            'regexp' => [
                'user' => [
                    'value' => 's.*y',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testTerm()
    {
        $this->query->term('user', 'bob');
        $expected = [
            'term' => [
                'user' => 'bob',
            ],
        ];
        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testTerms()
    {
        $this->query->terms('user', ['bob', 'elasticsearch']);
        $expected = [
            'terms' => [
                'user' => ['bob', 'elasticsearch'],
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testType()
    {
        $this->query->type('foo');
        $expectedResult = [
            'type' => ['value' => 'foo'],
        ];

        $this->assertEquals($expectedResult, $this->query->toArray());
    }

    public function testWildcard()
    {
        $this->query->wildcard('user', 'ki*y');
        $expectedResult = [
            'wildcard' => [
                'user' => [
                    'value' => 'ki*y',
                ],
            ],
        ];

        $this->assertEquals($expectedResult, $this->query->toArray());
    }

    public function testMatchAll()
    {
        $this->query->matchAll();
        $this->assertEquals(['match_all' => new \stdClass()], $this->query->toArray());
    }

    public function testMatchAllWithParameters()
    {
        $this->query->matchAll($params = ['boost' => 5]);
        $this->assertEquals(['match_all' => $params], $this->query->toArray());
    }
}
