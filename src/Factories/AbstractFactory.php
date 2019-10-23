<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Factories;

use ONGR\ElasticsearchDSL\BuilderInterface;
use Runner\EsqBuilder\Contracts\FactoryInterface;

abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected static $clauses = [];

    /**
     * @var string
     */
    protected static $namespace = '';

    /**
     * @var string
     */
    protected static $suffix = '';

    /**
     * {@inheritdoc}
     */
    public static function has($name): bool
    {
        return isset(static::$clauses[ucfirst($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public static function make($name, array $arguments = []): BuilderInterface
    {
        $name = ucfirst($name);

        if (!isset(static::$clauses[$name])) {
            throw new \RuntimeException(sprintf('%s does not exist', $name));
        }

        $namespace = sprintf('%s\\%s', static::$namespace, ucfirst(static::$clauses[$name]));

        $class = sprintf('%s\\%s%s', rtrim($namespace, '\\'), $name, static::$suffix);

        return new $class(...$arguments);
    }
}
