<?php

namespace Aberdeener\Koss\Queries;

use Aberdeener\Koss\Util\Util;

abstract class Query
{

    protected array $_where = [];

    /**
     * Preform a WHERE operation in this query.
     *
     * @param string $column Name of column to use.
     * @param string $operator Operator to use for comparison.
     * @param string|null $matches Value to compare to. If not provided, $operator will be used and `=` will be assumed as operator.
     * 
     * @return Query This instance of Query.
     */
    public function where(string $column, string $operator, ?string $matches = null): Query
    {
        $append = Util::handleWhereOperation($column, $operator, $matches);

        if ($append != null) {
            $this->_where[] = $append;
        }

        return $this;
    }

    /**
     * Execute Koss function under certain conditions.
     *
     * @param callable|bool $expression Function or boolean value to eval. 
     * @param callable $callback Function to run when $expression is true.
     * @param callable|null $fallback Function to run when $expression is false.
     * 
     * @return Query This instance of Query.
     */
    public function when(callable | bool $expression, callable $callback, ?callable $fallback = null): Query
    {
        Util::when($this, $expression, $callback, $fallback);

        return $this;
    }

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
