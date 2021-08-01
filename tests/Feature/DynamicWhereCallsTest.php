<?php

use Aberdeener\Koss\Exceptions\DynamicWhereCallException;

/**
 * @uses Aberdeener\Koss\Koss
 * @uses Aberdeener\Koss\Queries\Query
 * @uses Aberdeener\Koss\Queries\SelectQuery
 * @uses Aberdeener\Koss\Queries\Traits\HasWhereClauses
 * @uses Aberdeener\Koss\Util\Util
 * 
 * @covers Aberdeener\Koss\Queries\Traits\DynamicWhereCalls
 * @covers Aberdeener\Koss\Exceptions\DynamicWhereCallException
 */
class DynamicWhereCallsTest extends KossTestCase
{
    public function testDynamicWhereCallsWithCorrectUsage()
    {
        $this->assertEquals(
            "SELECT * FROM `users` WHERE `username` = 'Aberdeener'",
            $this->koss->getAll('users')->whereUsername('Aberdeener')->build()
        );
    }

    public function testDynamicWhereCallsMustStartWithWhere()
    {
        $this->expectException(DynamicWhereCallException::class);

        $this->koss->getAll('users')->wherUsername('Aberdeener')->build();
    }

    public function testInvalidWhereCallArgumentsWillThrowException()
    {
        $this->expectException(DynamicWhereCallException::class);

        $this->koss->getAll('users')->whereUsername()->build();
    }

    public function testTooManyWhereCallArgumentsWillThrowException()
    {
        $this->expectException(DynamicWhereCallException::class);

        $this->koss->getAll('users')->whereUsername('Aberdeener', 'tadhg')->build();
    }
}
