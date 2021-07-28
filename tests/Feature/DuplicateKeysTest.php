<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;

/**
 * @uses Aberdeener\Koss\Koss
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\Query
 * @uses Aberdeener\Koss\Queries\InsertQuery
 *
 * @covers Aberdeener\Koss\Queries\Traits\HasDuplicateKeys
 */
class DuplicateKeysTest extends TestCase
{
    private Koss $koss;

    public function setUp(): void
    {
        $this->koss = new Koss('localhost', 3306, 'koss', 'root', '');
    }

    public function testOnDuplicateKey()
    {
        $this->assertEquals(
            "INSERT INTO `users` (`username`, `full_name`) VALUES ('Aberdeener', 'Tadhg Boyle') ON DUPLICATE KEY UPDATE `username` = 'Aber'",
            $this->koss->insert('users', ['username' => 'Aberdeener', 'full_name' => 'Tadhg Boyle'])->onDuplicateKey(['username' => 'Aber'])->build()
        );
    }
}
