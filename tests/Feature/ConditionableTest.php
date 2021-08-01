<?php

use Aberdeener\Koss\Queries\SelectQuery;

/**
 * @uses Aberdeener\Koss\Koss
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\Query
 * @uses Aberdeener\Koss\Queries\SelectQuery
 * 
 * @covers Aberdeener\Koss\Queries\Traits\Conditionable
 */
class ConditionableTest extends KossTestCase
{
    public function testWhenWithTrue()
    {
        $this->assertEquals(
            'SELECT * FROM `users` LIMIT 5',
            $this->koss->getAll('users')->when(fn () => true, fn (SelectQuery $query) => $query->limit(5))->build()
        );
    }

    public function testWhenWithFalse()
    {
        $this->assertEquals(
            'SELECT * FROM `users`',
            $this->koss->getAll('users')->when(fn () => false, fn (SelectQuery $query) => $query->limit(5))->build()
        );
    }

    public function testUnlessWithFalse()
    {
        $this->assertEquals(
            'SELECT * FROM `users` LIMIT 5',
            $this->koss->getAll('users')->unless(fn() => false, fn (SelectQuery $query) => $query->limit(5))->build()
        );
    }

    public function testUnlessWithTrue()
    {
        $this->assertEquals(
            'SELECT * FROM `users`',
            $this->koss->getAll('users')->unless(fn () => true, fn (SelectQuery $query) => $query->limit(5))->build()
        );
    }
}