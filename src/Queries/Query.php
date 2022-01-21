<?php

namespace Aberdeener\Koss\Queries;

use Aberdeener\Koss\Queries\Traits\Conditionable;

abstract class Query
{
    use Conditionable;

    /**
     * Execute repsective query and store result.
     */
    abstract public function execute(): array|int;

    /**
     * Assemble clauses into matching MySQL statement.
     */
    abstract public function build(): string;

    /**
     * Safely remove all double spaces from a string.
     * Used for sanitizing the query string before submitting it to the MySQL server.
     *
     * @param string $string String to clean.
     *
     * @return string String with all multiple whitespaces removed.
     */
    final protected function cleanString(string $string): string
    {
        return preg_replace(
            '/^\s+|\s+$|\s+(?=\s)/',
            '',
            $string
        );
    }

    /**
     * Reset query strings and arrays.
     */
    abstract public function reset(): void;

    /**
     * Output the built string of all queries so far.
     *
     * @codeCoverageIgnore
     */
    final public function __toString(): string
    {
        return $this->build();
    }
}
