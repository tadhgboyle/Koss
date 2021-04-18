<?php

namespace Aberdeener\Koss\Queries;

abstract class Query
{
    /**
     * Execute Koss function under certain conditions.
     */
    abstract public function when(callable | bool $expression, callable $callback, ?callable $fallback = null): Query;

    /**
     * Execute repsective query and store result.
     */
    abstract public function execute(): mixed;

    /**
     * Assemble queries into MySQL statement.
     */
    abstract public function build(): string;

    /**
     * Reset query strings.
     */
    abstract public function reset(): void;

    /**
     * Debugging only: Output the built string of all queries so far.
     */
    final public function toString(): string
    {
        return $this->build();
    }

    /**
     * Debugging only: Output the built string of all queries so far.
     */
    final public function __toString(): string
    {
        return $this->build();
    }
}
