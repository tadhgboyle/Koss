<?php

use Aberdeener\Koss\Util\Util;
use PHPUnit\Framework\TestCase;

/**
 * @covers Aberdeener\Koss\Util\Util
 * @covers Aberdeener\Koss\Exceptions\StatementException
 */
class UtilTest extends TestCase
{
    public function testAssembleJoinClauseFunction()
    {
        $join_string = Util::assembleJoinClause([
            'INNER JOIN `nl2_users_groups` ON `nl2_users_groups`.`user_id` = `nl2_users`.`id`',
            'INNER JOIN `nl2_groups` ON `nl2_groups`.`id` = `nl2_users_groups`.`group_id`',
        ]);

        $this->assertEquals(
            'INNER JOIN `nl2_users_groups` ON `nl2_users_groups`.`user_id` = `nl2_users`.`id` INNER JOIN `nl2_groups` ON `nl2_groups`.`id` = `nl2_users_groups`.`group_id`',
            $join_string
        );
    }

    public function testAssembleWhereClauseFunction()
    {
        $clauses = [
            [
                'column' => 'username',
                'operator' => '=',
                'matches' => 'Aberdeener',
            ],
            [
                'glue' => 'AND',
                'column' => 'full_name',
                'operator' => '=',
                'matches' => 'Tadhg Boyle',
            ],
        ];

        $compiled_clause = Util::assembleWhereClause($clauses);

        $this->assertEquals(
            "WHERE `username` = 'Aberdeener' AND `full_name` = 'Tadhg Boyle'",
            $compiled_clause
        );
    }

    public function testEscapeStringsSingle()
    {
        $original_string = 'username';
        $escaped_string = Util::escapeStrings($original_string);

        $this->assertEquals('`username`', $escaped_string);
    }

    public function testEscapeStringsSingleWithExplicitKey()
    {
        $original_string = 'username';
        $escaped_string = Util::escapeStrings($original_string, '*');

        $this->assertEquals('*username*', $escaped_string);
    }

    public function testEscapeStringsMultiple()
    {
        $original_string = ['username', 'full_name'];
        $escaped_string = Util::escapeStrings($original_string);

        $this->assertEquals(['`username`', '`full_name`'], $escaped_string);
    }

    public function testEscapeStringsMultipleWithExplicitKey()
    {
        $original_string = ['username', 'full_name'];
        $escaped_string = Util::escapeStrings($original_string, '*');

        $this->assertEquals(['*username*', '*full_name*'], $escaped_string);
    }
}
