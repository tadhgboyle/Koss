<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;

/**
 * @uses Aberdeener\Koss\Koss
 * @uses Aberdeener\Koss\Queries\SelectQuery
 * @uses Aberdeener\Koss\Util\Util
 * 
 * @covers Aberdeener\Koss\Queries\Query
 */
class QueryTest extends TestCase
{

    private Koss $koss;

    public function setUp(): void
    {
        $this->koss = new Koss('localhost', 3306, 'koss', 'root', '');
    }

    public function testWhereNoOperator()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` = 'Aberdeener'",

            $this->koss->getAll('users')->where('username', 'Aberdeener')->build()
        );
    }

    public function testWhereWithExplicitOperator()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` <> 'yangyang200'",

            $this->koss->getAll('users')->where('username', '<>', 'yangyang200')->build()
        );
    }

    public function testWhereMultiple()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` = 'Aberdeener' AND `full_name` <> 'Ronan Boyle'",

            $this->koss->getAll('users')->where('username', 'Aberdeener')->where('full_name', '<>', 'Ronan Boyle')->build()
        );
    }

    public function testOrWhereNoOperator()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` = 'Aberdeener'",

            $this->koss->getAll('users')->orWhere('username', 'Aberdeener')->build()
        );
    }

    public function testOrWhereWithExplicitOperator()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` <> 'yangyang200'",

            $this->koss->getAll('users')->orWhere('username', '<>', 'yangyang200')->build()
        );
    }

    public function testOrWhereMultiple()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` = 'Aberdeener' OR `full_name` = 'Tadhg Boyle'",

            $this->koss->getAll('users')->orWhere('username', 'Aberdeener')->orWhere('full_name', '=', 'Tadhg Boyle')->build()
        );
    }

    public function testLike()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` LIKE '%Aberdeener%'",

            $this->koss->getAll('users')->like('username', '%Aberdeener%')->build()
        );
    }

    public function testOrLike()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` LIKE '%Aberdeener%'",

            $this->koss->getAll('users')->orLike('username', '%Aberdeener%')->build()
        );
    }

    public function testOrLikeMultiple()
    {
        $this->assertEquals(

            "SELECT * FROM `users` WHERE `username` LIKE '%Aberdeener%' OR `full_name` LIKE '%Tadhg Boyle%'",

            $this->koss->getAll('users')->orLike('username', '%Aberdeener%')->orLike('full_name', '%Tadhg Boyle%')->build()
        );
    }
}