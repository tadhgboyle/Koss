<?php

namespace Aberdeener\Koss\Queries\Joins;

use Aberdeener\Koss\Queries\SelectQuery;

class LeftOuterJoin extends Join
{
    public function __construct(SelectQuery $query)
    {
        parent::__construct('LEFT OUTER', $query);
    }
}
