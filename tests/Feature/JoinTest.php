<?php

use Aberdeener\Koss\Queries\Joins\Join;
use Aberdeener\Koss\Queries\Joins\InnerJoin;
use Aberdeener\Koss\Exceptions\JoinException;
use Aberdeener\Koss\Queries\Joins\LeftOuterJoin;
use Aberdeener\Koss\Queries\Joins\RightOuterJoin;

/**
 * @uses Aberdeener\Koss\Koss
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\Query
 * @uses Aberdeener\Koss\Queries\Traits\DynamicWhereCalls
 *
 * @covers Aberdeener\Koss\Queries\Joins\Join
 * @covers Aberdeener\Koss\Exceptions\JoinException
 * @covers Aberdeener\Koss\Queries\Joins\InnerJoin
 * @covers Aberdeener\Koss\Queries\Joins\LeftOuterJoin
 * @covers Aberdeener\Koss\Queries\Joins\RightOuterJoin
 * @covers Aberdeener\Koss\Queries\SelectQuery
 */
class JoinTest extends KossTestCase
{
    public function testCannotMakeJoinSubclassWithInvalidKeyword()
    {
        $this->expectException(JoinException::class);

        new LeftInnerJoin($this->koss->getAll('users'));
    }

    public function testInnerJoin()
    {
        $this->assertEquals(
            'SELECT * FROM `users` INNER JOIN `users_groups` ON `users_groups`.`users_id` = `users`.`users_id`',
            $this->koss->getAll('users')->innerJoin(function (InnerJoin $join) {
                $join->table('users_groups')->on('users_id');
            })->build()
        );
    }

    public function testInnerJoinCannotProceedBeforeTableSet()
    {
        $this->expectException(JoinException::class);

        $this->koss->getAll('users')->innerJoin(function (InnerJoin $join) {
            $join->on('user_id');
        });
    }

    public function testInnerJoinUsingThrough()
    {
        $this->assertEquals(
            'SELECT * FROM `users` INNER JOIN `users_groups` ON `users_groups`.`user_id` = `users`.`id` INNER JOIN `groups` ON `groups`.`id` = `users_groups`.`group_id`',
            $this->koss->getAll('users')->innerJoin(function (InnerJoin $join) {
                $join->table('users_groups')->on('user_id', 'id');
                $join->table('groups')->through('users_groups')->on('id', 'group_id');
            })->build()
        );
    }

    public function testLeftOuterJoin()
    {
        $this->assertEquals(
            'SELECT * FROM `users` LEFT OUTER JOIN `users_groups` ON `users_groups`.`users_id` = `users`.`users_id`',
            $this->koss->getAll('users')->leftOuterJoin(function (LeftOuterJoin $join) {
                $join->table('users_groups')->on('users_id');
            })->build()
        );
    }

    public function testRightOuterJoin()
    {
        $this->assertEquals(
            'SELECT * FROM `users` RIGHT OUTER JOIN `users_groups` ON `users_groups`.`users_id` = `users`.`users_id`',
            $this->koss->getAll('users')->rightOuterJoin(function (RightOuterJoin $join) {
                $join->table('users_groups')->on('users_id');
            })->build()
        );
    }
}

class LeftInnerJoin extends Join
{
    public function __construct($queryInstance)
    {
        parent::__construct('LEFT INNNER', $queryInstance);
    }
}
