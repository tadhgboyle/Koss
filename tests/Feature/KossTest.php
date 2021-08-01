<?php

use Aberdeener\Koss\Koss;
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
class KossTest extends KossTestCase
{
    public function testCanCreateKossInstance()
    {
        $koss = new Koss('localhost', 3306, 'koss', 'root', '');

        $this->assertInstanceOf(Koss::class, $koss);
    }

    public function testRawExecuteSelection()
    {
        $results = $this->koss->execute('SELECT * FROM `users`');

        $this->assertIsArray($results);
    }

    public function testRawExecuteInserting()
    {
        $this->assertEquals(1, $this->koss->execute("INSERT INTO users (username, full_name, balance) VALUES ('Aberdeener', 'Tadhg Boyle', 123.45)"));
    }

    public function testRawExecuteUpdating()
    {
        $this->assertIsInt($this->koss->execute("UPDATE users SET username = 'Aber' WHERE balance = 123.45"));
    }

    public function testExceptionOnInvalidRawQuery()
    {
        $this->expectException(StatementException::class);

        $this->koss->execute('NULL');
    }

    // TODO: PDO timeout of 5 seconds makes tests really annoying to run, alternate way?
    // public function testExceptionWithInvalidDatabaseCredentials()
    // {
    //     $this->expectException(PDOException::class);

    //     $koss = new Koss('google.com', 3306, 'koss', 'root', '');
    // }
}
