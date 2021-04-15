<?php

namespace Aberdeener\Koss\Queries;

interface IKossQuery
{

    /**
     * Execute Koss function under certain conditions
     */
    public function when($expression, callable $callback, callable $fallback = null): IKossQuery;

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
