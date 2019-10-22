<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder;

use ONGR\ElasticsearchDSL\Serializer\Normalizer\CustomReferencedNormalizer;
use ONGR\ElasticsearchDSL\Serializer\OrderedSerializer;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;

class Serializer
{
    /**
     * @var OrderedSerializer
     */
    protected static $serializer;

    public static function normalize($arr)
    {
        if (is_null(static::$serializer)) {
            static::$serializer = new OrderedSerializer([
                new CustomReferencedNormalizer(),
                new CustomNormalizer(),
            ]);
        }

        return static::$serializer->normalize($arr);
    }
}
