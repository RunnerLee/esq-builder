<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-10
 */

namespace Runner\EsqBuilder\InnerHit;

class ParentInnerHit extends NestedInnerHit
{
    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'parent';
    }
}
