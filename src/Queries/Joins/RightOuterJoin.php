<?php

namespace Aberdeener\Koss\Queries\Joins;

use Aberdeener\Koss\Queries\SelectQuery;

class RightOuterJoin extends Join
{
    public function __construct(SelectQuery $query)
    {
        parent::__construct('RIGHT OUTER', $query);
    }
}
