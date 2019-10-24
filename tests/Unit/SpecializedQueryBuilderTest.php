<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Runner\EsqBuilder\QueryBuilder;

class SpecializedQueryBuilderTest extends TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new QueryBuilder();
    }

    public function testMoreLikeThis()
    {
        $this->query->moreLikeThis('this is a test', ['fields' => ['title', 'description']]);
        $expected = [
            'more_like_this' => [
                'fields' => ['title', 'description'],
                'like'   => 'this is a test',
            ],
        ];

        $this->assertEquals($expected, $this->query->toArray());
    }

    /**
     * @param $script
     * @param $parameters
     * @param $expected
     * @dataProvider getScriptDataProvider
     */
    public function testScript($script, $parameters, $expected)
    {
        $this->query->script($script, $parameters);
        $this->assertEquals(['script' => $expected], $this->query->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTemplateException()
    {
        $this->query->template()->toArray();
    }

    public function testTemplateInline()
    {
        $inline = '"term": {"field": "{{query_string}}"}';
        $params = ['query_string' => 'all about search'];
        $this->query->template(null, $inline, $params);
        $expected = [
            'template' => [
                'inline' => $inline,
                'params' => $params,
            ],
        ];
        $this->assertEquals($expected, $this->query->toArray());
    }

    public function testTemplateFile()
    {
        $file = 'my_template';
        $params = ['query_string' => 'all about search'];
        $this->query->template($file, null, $params);
        $expected = [
            'template' => [
                'file'   => $file,
                'params' => $params,
            ],
        ];
        $this->assertEquals($expected, $this->query->toArray());
    }

    public function getScriptDataProvider()
    {
        return [
            'simple_script' => [
                "doc['num1'].value > 1",
                [],
                ['script' => ['inline' => "doc['num1'].value > 1"]],
            ],
            'script_with_parameters' => [
                "doc['num1'].value > param1",
                ['params' => ['param1' => 5]],
                ['script' => ['inline' => "doc['num1'].value > param1", 'params' => ['param1' => 5]]],
            ],
        ];
    }
}
