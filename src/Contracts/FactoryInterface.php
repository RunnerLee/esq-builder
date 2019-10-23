<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Contracts;

use ONGR\ElasticsearchDSL\BuilderInterface;

interface FactoryInterface
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public static function has($name): bool;

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return BuilderInterface
     */
    public static function make($name, array $arguments = []): BuilderInterface;
}
