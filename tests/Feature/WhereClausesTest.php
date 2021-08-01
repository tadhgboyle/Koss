<?php

use Aberdeener\Koss\Exceptions\StatementException;

/**
 * @uses Aberdeener\Koss\Koss
 * @uses Aberdeener\Koss\Queries\Query
 * @uses Aberdeener\Koss\Queries\SelectQuery
 * @uses Aberdeener\Koss\Util\Util
 * 
 * @covers Aberdeener\Koss\Queries\Traits\HasWhereClauses
 * @covers Aberdeener\Koss\Exceptions\StatementException
 */
class WhereClausesTest extends KossTestCase
{
    public function testCanMakeSimpleWhereClauseWithExplicitOperator()
    {
        $this->assertEquals(
            "SELECT * FROM `users` WHERE `username` <> 'yangyang200'",
            $this->koss->getAll('users')->where('username', '<>', 'yangyang200')->build()
        );
    }

    public function testCanMakeSimpleWhereClauseWithoutExplicitOperator()
    {
        $this->assertEquals(
            "SELECT * FROM `users` WHERE `username` = 'Aberdeener'",
            $this->koss->getAll('users')->where('username', 'Aberdeener')->build()
        );
    }

    public function testCanMakeNestedWhereClausesFromArray()
    {
        $this->assertEquals(
            "SELECT * FROM `users` WHERE `username` = 'Aberdeener' AND `full_name` <> 'Ronan Boyle'",
            $this->koss->getAll('users')->where([['username', 'Aberdeener'], ['full_name', '<>', 'Ronan Boyle']])->build()
        ); 
    }

    public function testUsesGlueIfProvided()
    {
        $this->assertEquals(
            "SELECT * FROM `users` WHERE `username` = 'Aberdeener' OR `full_name` = 'Tadhg Boyle'",
            $this->koss->getAll('users')->where([['username', 'Aberdeener'], ['full_name', 'Tadhg Boyle', 'OR']])->build()
        ); 
    }

    public function testCanDetermineGlueIfNotProvided()
    {
        $this->assertEquals(
            "SELECT * FROM `users` WHERE `username` = 'Aberdeener' OR `full_name` = 'Tadhg Boyle'",
            $this->koss->getAll('users')->where('username', 'Aberdeener')->orWhere('full_name', 'Tadhg Boyle')->build()
        ); 
    }

    public function testIgnoresIndividualValuesInNestedArrayWhereClause()
    {
        $this->assertEquals(
            "SELECT * FROM `users` WHERE `username` = 'Aberdeener'",
            $this->koss->getAll('users')->where([['username', 'Aberdeener'], 'ignore'])->build()
        ); 
    }

    public function testNeedsAtLeastTwoElementsInEachNestedArray()
    {
        $this->expectException(StatementException::class);

        $this->koss->getAll('users')->where([])->build();
    }
}
