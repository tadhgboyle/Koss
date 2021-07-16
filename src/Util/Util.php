<?php

namespace Aberdeener\Koss\Util;

use Aberdeener\Koss\Exceptions\StatementException;

class Util
{
    /**
     * Create an array of `column`, `operator` and `matches` for WHERE clauses.
     * Validates that $operator is valid.
     *
     * @param string $column Name of column to use in clause.
     * @param string $operator Operator to use in comparison.
     * @param string|null $matches Value to match with. If not provided, operator will be assumed as `=` and $operator will be used as match.
     * @param string|null $glue How to join this clause with other WHERE clauses. Can be `AND` or `OR`.
     *
     * @return array Validated and prepared array.
     */
    public static function handleWhereOperation(string $column, string $operator, ?string $matches = null, ?string $glue = 'AND'): array
    {
        if ($matches == null) {
            $matches = $operator;
            $operator = '=';
        }

        if (!in_array($operator, ['=', '<>', 'LIKE'])) {
            throw new StatementException("Invalid WHERE clause operator. Operator: $operator.");
        }

        if (!in_array($glue, ['AND', 'OR'])) {
            throw new StatementException("Invalid WHERE clause glue. Glue: $glue.");
        }

        return [
            'glue' => $glue,
            'column' => $column,
            'operator' => $operator,
            'matches' => $matches,
        ];
    }

    /**
     * Create string of joint JOIN clauses for use in final query.
     *
     * @param array $joins Raw JOIN statements
     *
     * @return string Joint statements.
     */
    public static function assembleJoinClause(array $joins): string
    {
        $clause = '';

        foreach ($joins as $join) {
            $clause .= $join . ' ';
        }

        return trim($clause);
    }

    /**
     * Assemble all where clauses into one string using appropriate MySQL syntax.
     *
     * @param array $where Array of columns, operators and matches to create into string.
     *
     * @return string Assembled clause.
     */
    public static function assembleWhereClause(array $where): string
    {
        $first = true;
        $return = '';

        foreach ($where as $clause) {
            if ($first) {
                $return .= 'WHERE ';
                $first = false;
            } else {
                $return .= $clause['glue'] . ' ';
            }

            $return .= "`{$clause['column']}` {$clause['operator']} '{$clause['matches']}' ";
        }

        return trim($return);
    }

    /**
     * Escape array of strings or single string by adding $key to front and end of each string.
     * Used for preparing column and values for use in query statements.
     *
     * @param array|string $strings Strings to be escaped.
     * @param string $key Key to add to front and end of each string.
     *
     * @return array|string Escaped strings.
     */
    public static function escapeStrings(array | string $strings, string $key = '`'): array | string
    {
        if (!is_array($strings)) {
            return self::wrapString($key, $strings);
        }

        return array_map(fn($string) => self::wrapString($key, $string), $strings);
    }

    /**
     * Surround $string by $key.
     * 
     * @param string $key Character to wrap $string in.
     * @param string $string String to wrap.
     * 
     * @return string Wrapped $string.
     */
    private static function wrapString(string $key, string $string): string
    {
        return $key . $string . $key;
    }
}
