<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Factories;

class QueryFactory extends AbstractFactory
{
    public static $clauses = [
        'CommonTerms'       => 'FullText',
        'MatchPhrase'       => 'FullText',
        'MatchPhrasePrefix' => 'FullText',
        'Match'             => 'FullText',
        'MultiMatch'        => 'FullText',
        'QueryString'       => 'FullText',
        'SimpleQueryString' => 'FullText',

        'GeoBoundingBox' => 'Geo',
        'GeoDistance'    => 'Geo',
        'GeoPolygon'     => 'Geo',
        'GeoShape'       => 'Geo',

        'ParentId' => 'Joining',

        'MoreLikeThis' => 'Specialized',
        'Script'       => 'Specialized',
        'Template'     => 'Specialized',

        'Exists'   => 'TermLevel',
        'Fuzzy'    => 'TermLevel',
        'Ids'      => 'TermLevel',
        'Prefix'   => 'TermLevel',
        'Range'    => 'TermLevel',
        'Regexp'   => 'TermLevel',
        'Term'     => 'TermLevel',
        'Terms'    => 'TermLevel',
        'Type'     => 'termLevel',
        'Wildcard' => 'TermLevel',

        'MatchAll' => '',
    ];

    protected static $namespace = 'ONGR\\ElasticsearchDSL\\Query';

    protected static $suffix = 'Query';
}
