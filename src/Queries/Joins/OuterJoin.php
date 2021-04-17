<?php

namespace Aberdeener\Koss\Queries\Joins;

use Aberdeener\Koss\Queries\Joins\Join;
use Aberdeener\Koss\Queries\SelectQuery;

class OuterJoin extends Join
{

    public function __construct(SelectQuery $query)
    {
        parent::__construct('OUTER', $query);
    }
}
