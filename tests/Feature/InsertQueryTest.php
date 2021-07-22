<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;

/**
 * @uses Aberdeener\Koss\Util\Util
 *
 * @covers Aberdeener\Koss\Koss
 * @covers Aberdeener\Koss\Queries\Query
 * @covers Aberdeener\Koss\Queries\InsertQuery
 */
class InsertQueryTest extends TestCase
{
    private Koss $koss;

    public function setUp(): void
    {
        $this->koss = new Koss('localhost', 3306, 'koss', 'root', '');
    }

    public function testInsert()
    {
        $this->assertEquals(
            "INSERT INTO `users` (`username`, `full_name`) VALUES ('Aberdeener', 'Tadhg Boyle')",
            $this->koss->insert('users', ['username' => 'Aberdeener', 'full_name' => 'Tadhg Boyle'])->build()
        );
    }

    public function testCanInsert()
    {
        $this->assertSame(
            1,
            $this->koss->insert('users', ['username' => 'Aberdeener', 'full_name' => 'Tadhg Boyle'])->execute()
        );
    }
}
