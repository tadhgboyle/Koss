<?php

namespace Aberdeener\Koss\Queries;

interface Query
{

    /**
     * Execute Koss function under certain conditions
     */
    public function when(callable|bool $expression, callable $callback, ?callable $fallback = null): Query;

    /**
     * Execute repsective query and store result
     */
    public function execute(): mixed;

    /**
     * Assemble queries into MySQL statement
     */
    public function build(): string;

    /**
     * Reset query strings
     */
    public function reset(): void;

    /**
     * Debugging only: Output the built string of all queries so far
     */
    public function toString(): string;

    /**
     * Debugging only: Output the built string of all queries so far
     */
    public function __toString(): string;
}
