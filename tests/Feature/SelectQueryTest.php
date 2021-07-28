<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;
use Aberdeener\Koss\Queries\SelectQuery;

/**
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\Traits\DynamicWhereCalls
 * 
 * @covers Aberdeener\Koss\Koss
 * @covers Aberdeener\Koss\Queries\Query
 * @covers Aberdeener\Koss\Queries\SelectQuery
 */
class SelectQueryTest extends TestCase
{
    private Koss $koss;

    public function setUp(): void
    {
        $this->koss = new Koss('localhost', 3306, 'koss', 'root', '');
    }

    public function testGetAllColumns()
    {
        $this->assertEquals(
            'SELECT * FROM `users`',
            $this->koss->getAll('users')->build()
        );
    }

    public function testGetAllColumnsResults()
    {
        $results = $this->koss->getAll('users')->execute();

        $this->assertIsArray($results);
    }

    public function testGetSomeColumns()
    {
        $this->assertEquals(
            'SELECT `username`, `full_name` FROM `users`',
            $this->koss->getSome('users', ['username', 'full_name'])->build()
        );
    }

    public function testGetSingleColumn()
    {
        $this->assertEquals(
            'SELECT `username` FROM `users`',
            $this->koss->getSome('users', 'username')->build()
        );
    }

    public function testGetSingleColumnThenAnother()
    {
        $this->assertEquals(
            'SELECT `username`, `full_name` FROM `users`',
            $this->koss->getSome('users', 'username')->column('full_name')->build()
        );
    }

    public function testGetSingleColumnThenAll()
    {
        $this->assertEquals(
            'SELECT * FROM `users`',
            $this->koss->getSome('users', 'username')->column('*')->build()
        );
    }

    public function testGetSingleColumnThenSameAgain()
    {
        $this->assertEquals(
            'SELECT `username` FROM `users`',
            $this->koss->getSome('users', 'username')->column('username')->build()
        );
    }

    public function testGetSingleColumnThenMore()
    {
        $this->assertEquals(
            'SELECT `username`, `full_name`, `phone_number` FROM `users`',
            $this->koss->getSome('users', 'username')->columns(['full_name', 'phone_number'])->build()
        );
    }

    public function testOrderByWithNoOrder()
    {
        $this->assertEquals(
            'SELECT * FROM `users` ORDER BY `user_id` DESC',
            $this->koss->getAll('users')->orderBy('user_id')->build()
        );
    }

    public function testOrderByWithExplicitOrder()
    {
        $this->assertEquals(
            'SELECT * FROM `users` ORDER BY `user_id` ASC',
            $this->koss->getAll('users')->orderBy('user_id', 'ASC')->build()
        );
    }

    public function testGroupBy()
    {
        $this->assertEquals(
            'SELECT * FROM `users` GROUP BY `phone_number`',
            $this->koss->getAll('users')->groupBy('phone_number')->build()
        );
    }

    public function testLimit()
    {
        $this->assertEquals(
            'SELECT * FROM `users` LIMIT 5',
            $this->koss->getAll('users')->limit(5)->build()
        );
    }

    public function testWhenWithTrueAndNoFallback()
    {
        $this->assertEquals(
            'SELECT * FROM `users` LIMIT 5',
            $this->koss->getAll('users')->when(fn () => true, fn (SelectQuery $query) => $query->limit(5))->build()
        );
    }

    public function testWhenWithFalseAndNoFallback()
    {
        $this->assertEquals(
            'SELECT * FROM `users`',
            $this->koss->getAll('users')->when(fn () => false, fn (SelectQuery $query) => $query->limit(5))->build()
        );
    }

    public function testWhenWithFalseAndWithFallback()
    {
        $this->assertEquals(
            'SELECT * FROM `users` ORDER BY `full_name` DESC',
            $this->koss->getAll('users')->when(fn () => false, fn () => null, function (SelectQuery $query) {
                return $query->orderBy('full_name');
            })->build()
        );
    }

    public function testGetAllAndCastIndividual()
    {
        $results = $this->koss->getAll('users')->cast('balance', 'float')->execute();

        $this->assertIsFloat($results[0]->balance);
    }

    public function testGetAllAndCastMultiple()
    {
        $results = $this->koss->getAll('users')->casts(['username' => 'string', 'balance' => 'float'])->execute();

        $this->assertIsString($results[0]->username);
        $this->assertIsFloat($results[0]->balance);
    }

    public function testGetAllAndCastNonexistantColumn()
    {
        $results = $this->koss->getAll('users')->cast('address', 'string')->execute();

        $this->assertNotContains('address', $results);
    }
}
