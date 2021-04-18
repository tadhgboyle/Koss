<?php

namespace Aberdeener\Koss\Queries;

abstract class Query
{
    /**
     * Execute Koss function under certain conditions.
     */
    public abstract function when(callable | bool $expression, callable $callback, ?callable $fallback = null): Query;

    /**
     * Execute repsective query and store result.
     */
    public abstract function execute(): mixed;

    /**
     * Assemble queries into MySQL statement.
     */
    public abstract function build(): string;

    /**
     * Reset query strings.
     */
    public abstract function reset(): void;

    /**
     * Debugging only: Output the built string of all queries so far.
     */
    public final function toString(): string
    {
        return $this->build();
    }

    /**
     * Debugging only: Output the built string of all queries so far.
     */
    public final function __toString(): string
    {
        return $this->build();
    }
}
