<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;

/**
 * @covers
 */
class KossTest extends TestCase {

    public function testCanCreateKossInstance()
    {
        $koss = new Koss('localhost', 3306, 'koss', 'root', '');

        $this->assertInstanceOf(Koss::class, $koss);
    }

    // TODO: PDO timeout of 5 seconds makes tests really annoying to run, alternate way?
    // public function testExceptionWithInvalidDatabaseCredentials()
    // {
    //     $this->expectException(PDOException::class);

    //     $koss = new Koss('google.com', 3306, 'koss', 'root', '');
    // }

}