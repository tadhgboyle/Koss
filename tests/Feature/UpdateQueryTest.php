<?php

/**
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\Traits\HasDuplicateKeys
 *
 * @covers Aberdeener\Koss\Koss
 * @covers Aberdeener\Koss\Queries\Query
 * @covers Aberdeener\Koss\Queries\UpdateQuery
 */
class UpdateQueryTest extends KossTestCase
{
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
