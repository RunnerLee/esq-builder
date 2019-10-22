<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Contracts;

use ONGR\ElasticsearchDSL\BuilderInterface as BaseBuilder;
use ONGR\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface;

interface BuilderInterface extends BaseBuilder
{
    /**
     * @return SearchEndpointInterface
     */
    public function getSearchEndpoint(): SearchEndpointInterface;
}
