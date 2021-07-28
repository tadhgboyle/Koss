<?php

namespace Aberdeener\Koss\Queries\Joins;

use Aberdeener\Koss\Queries\SelectQuery;

final class InnerJoin extends Join
{
    public function __construct(SelectQuery $query)
    {
        parent::__construct('INNER', $query);
    }
}
