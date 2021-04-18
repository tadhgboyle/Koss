<?php

namespace Aberdeener\Koss\Queries\Joins;

use Aberdeener\Koss\Queries\SelectQuery;

class FullOuterJoin extends Join
{
    public function __construct(SelectQuery $query)
    {
        parent::__construct('FULL OUTER', $query);
    }
}
