<?php

namespace Aberdeener\Koss\Util;

use Aberdeener\Koss\Exceptions\StatementException;

final class Util
{
    /**
     * Create string of joint JOIN clauses for use in final query.
     *
     * @param array $joins Raw JOIN statements
     *
     * @return string Joint statements.
     */
    public static function assembleJoinClause(array $joins): string
    {
        return implode(' ', $joins);
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

            $return .= self::escapeStrings($clause['column']) . ' ' . $clause['operator'] . ' ' . self::escapeStrings($clause['matches'], "'") . ' ';
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

        return array_map(static fn ($string) => self::wrapString($key, $string), $strings);
    }

    /**
     * Surround $string by $key if it is not an asterisk character.
     *
     * @param string $key Character to wrap $string in.
     * @param string $string String to wrap.
     *
     * @return string Wrapped $string.
     */
    private static function wrapString(string $key, string $string): string
    {
        if ($string === '*') {
            return $string;
        }

        return $key . $string . $key;
    }
}
