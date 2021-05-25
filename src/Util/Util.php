<?php

namespace Aberdeener\Koss\Util;

use Aberdeener\Koss\Queries\Query;
use Aberdeener\Koss\Exceptions\StatementException;

class Util
{
    /**
     * Run a Koss function only when the specified $expression is true.
     *
     * @param Query $instance Current instance of Select/Update query to pass in background to callable function.
     * @param callable|bool $expression Expression to run, must return bool
     * @param callable $callback Ran if $expression is true
     * @param callable $fallback (optional) Ran if $expression is false
     */
    public static function when(Query $instance, callable | bool $expression, callable $callback, ?callable $fallback = null): void
    {
        $expression_bool = is_callable($expression) ? $expression() : $expression;

        if ($expression_bool) {
            $callback($instance);
            return;
        } else {
            if ($fallback != null) {
                $fallback($instance);
                return;
            }
        }
    }

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

            $return .= '`' . $clause['column'] . '` ' . $clause['operator'] . ' \'' . $clause['matches'] . '\' ';
        }

        return trim($return);
    }

    public static function assembleHavingClause(array $havings): string
    {
        $first = true;
        $return = '';

        foreach ($havings as $having) {
            if (!in_array($having['glue'], ['AND', 'OR'])) {
                throw new StatementException('Invalid HAVING clause glue. Glue: ' . $having['glue'] . '.');
            }

            if ($first) {
                $return .= 'HAVING ';
                $first = false;
            } else {
                $return .= $having['glue'] . ' ';
            }

            $return .= $having['having'] . ' ';
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
            return $key . $strings . $key;
        }

        $escaped = [];

        foreach ($strings as $string) {
            $escaped[] = $key . $string . $key;
        }

        return $escaped;
    }
}
