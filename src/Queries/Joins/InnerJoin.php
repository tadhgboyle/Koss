<?php

namespace Aberdeener\Koss\Queries\Joins;

use Aberdeener\Koss\Queries\SelectQuery;

class InnerJoin extends Join
{
    public function __construct(SelectQuery $query)
    {
        parent::__construct('INNER', $query);
    }
}
