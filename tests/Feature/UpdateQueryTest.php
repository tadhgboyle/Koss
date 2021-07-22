<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;

/**
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\Traits\HasDuplicateKeys
 * 
 * @covers Aberdeener\Koss\Koss
 * @covers Aberdeener\Koss\Queries\Query
 * @covers Aberdeener\Koss\Queries\UpdateQuery
 */
class UpdateQueryTest extends TestCase
{
    private Koss $koss;

    public function setUp(): void
    {
        $this->koss = new Koss('localhost', 3306, 'koss', 'root', '');
    }

    public function testUpdate()
    {
        $this->assertEquals(
            "UPDATE `users` SET `username` = 'thebossman' WHERE `username` <> 'Aberdeener'",
            $this->koss->update('users', ['username' => 'thebossman'])->where('username', '<>', 'Aberdeener')->build()
        );
    }

    // public function testCanUpdate()
    // {
    //     $this->markTestIncomplete();
    // }
}
