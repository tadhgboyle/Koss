<?php

use Aberdeener\Koss\Exceptions\StatementException;
use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;

/**
 * @uses Aberdeener\Koss\Util\Util
 * @uses Aberdeener\Koss\Queries\SelectQuery
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
    }

    public function testRawExecuteUpdating()
    {
        $koss = new Koss('localhost', 3306, 'koss', 'root', '');
    }

    public function testExceptionOnInvalidQuery()
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
