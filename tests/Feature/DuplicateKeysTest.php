<?php

/**
 * @uses Aberdeener\Koss\Koss
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\Query
 * @uses Aberdeener\Koss\Queries\InsertQuery
 *
 * @covers Aberdeener\Koss\Queries\Traits\HasDuplicateKeys
 */
class DuplicateKeysTest extends KossTestCase
{
    public function testOnDuplicateKey()
    {
        $this->assertEquals(
            "INSERT INTO `users` (`username`, `full_name`) VALUES ('Aberdeener', 'Tadhg Boyle') ON DUPLICATE KEY UPDATE `username` = 'Aber'",
            $this->koss->insert('users', ['username' => 'Aberdeener', 'full_name' => 'Tadhg Boyle'])->onDuplicateKey(['username' => 'Aber'])->build()
        );
    }
}
