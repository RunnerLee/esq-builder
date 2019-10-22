<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\Factories;

class QueryFactory extends AbstractFactory
{
    protected static $clauses = [
        'Common'            => 'FullText',
        'MatchPhrase'       => 'FullText',
        'MatchPhrasePrefix' => 'FullText',
        'Match'             => 'FullText',
        'MultiMatch'        => 'FullText',
        'QueryString'       => 'FullText',
        'SimpleQuery'       => 'FullText',

        'GeoBoundingBox' => 'Geo',
        'GetDistance'    => 'Geo',
        'GetPolygon'     => 'Geo',
        'GetShape'       => 'Geo',

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
