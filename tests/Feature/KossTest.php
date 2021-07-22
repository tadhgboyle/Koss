<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;
use Aberdeener\Koss\Exceptions\StatementException;

/**
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\Query
 * @uses Aberdeener\Koss\Queries\SelectQuery
 * @uses Aberdeener\Koss\Queries\InsertQuery
 * @uses Aberdeener\Koss\Queries\UpdateQuery
 * @uses Aberdeener\Koss\Queries\Traits\HasDuplicateKeys
 *
 * @covers Aberdeener\Koss\Koss
 * @covers Aberdeener\Koss\Exceptions\StatementException
 */
class KossTest extends TestCase
{
    public function testCanCreateKossInstance()
    {
        $koss = new Koss('localhost', 3306, 'koss', 'root', '');

        $this->assertInstanceOf(Koss::class, $koss);
    }

    public function testRawExecuteSelection()
    {
        $koss = new Koss('localhost', 3306, 'koss', 'root', '');
        $results = $koss->execute('SELECT * FROM `users`');

        $this->assertIsArray($results);
    }

    public function testRawExecuteInserting()
    {
        $koss = new Koss('localhost', 3306, 'koss', 'root', '');

        $this->assertEquals(1, $koss->execute("INSERT INTO users (username, full_name, balance) VALUES ('Aberdeener', 'Tadhg Boyle', 123.45)"));
    }

    public function testRawExecuteUpdating()
    {
        $koss = new Koss('localhost', 3306, 'koss', 'root', '');

        $this->assertIsInt($koss->execute("UPDATE users SET username = 'Aber' WHERE balance = 123.45"));
    }

    public function testExceptionOnInvalidRawQuery()
    {
        $this->expectException(StatementException::class);

        $koss = new Koss('localhost', 3306, 'koss', 'root', '');
        $koss->execute('NULL');
    }

    // TODO: PDO timeout of 5 seconds makes tests really annoying to run, alternate way?
    // public function testExceptionWithInvalidDatabaseCredentials()
    // {
    //     $this->expectException(PDOException::class);

    //     $koss = new Koss('google.com', 3306, 'koss', 'root', '');
    // }
}
