<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Factories;

use ONGR\ElasticsearchDSL\BuilderInterface;

abstract class AbstractFactory
{
    protected static $clauses = [];

    protected static $namespace = '';

    protected static $suffix = '';

    public static function has($name): bool
    {
        return isset(static::$clauses[ucfirst($name)]);
    }

    public static function make($name, array $arguments = []): BuilderInterface
    {
        $name = ucfirst($name);

        if (!isset(static::$clauses[$name])) {
            throw new \RuntimeException('query clause does not exist');
        }

        $namespace = sprintf('%s\\%s', static::$namespace, ucfirst(static::$clauses[$name]));

        $class = sprintf('%s\\%s%s', rtrim($namespace, '\\'), $name, static::$suffix);

        return new $class(...$arguments);
    }
}
