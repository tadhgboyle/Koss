<?php

namespace Aberdeener\Koss\Queries;

use Aberdeener\Koss\Util\Util;

abstract class Query
{

    protected array $_where = [];

    /**
     * Preform an AND WHERE operation in this query.
     *
     * @param string $column Name of column to use.
     * @param string $operator Operator to use for comparison.
     * @param string|null $matches Value to compare to. If not provided, $operator will be used and `=` will be assumed as operator.
     * 
     * @return SelectQuery|UpdateQuery This instance of Query.
     */
    public function where(string $column, string $operator, ?string $matches = null): SelectQuery | UpdateQuery
    {
        $append = Util::handleWhereOperation($column, $operator, $matches);

        if ($append != null) {
            $this->_where[] = $append;
        }

        return $this;
    }

    /**
     * Preform an OR WHERE operation in this query.
     *
     * @param string $column Name of column to use.
     * @param string $operator Operator to use for comparison.
     * @param string|null $matches Value to compare to. If not provided, $operator will be used and `=` will be assumed as operator.
     * 
     * @return SelectQuery|UpdateQuery This instance of Query.
     */
    public function orWhere(string $column, string $operator, ?string $matches = null): SelectQuery | UpdateQuery
    {
        $append = Util::handleWhereOperation($column, $operator, $matches, 'OR');

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
     * @return SelectQuery|UpdateQuery This instance of Query.
     */
    public function when(callable | bool $expression, callable $callback, ?callable $fallback = null): SelectQuery | UpdateQuery
    {
        Util::when($this, $expression, $callback, $fallback);

        return $this;
    }

    /**
     * Execute repsective query and store result.
     */
    abstract public function execute(): array | int;

    /**
     * Assemble clauses into matching MySQL statement.
     */
    abstract public function build(): string;

    /**
     * Reset query strings and arrays.
     */
    abstract public function reset(): void;

    /**
     * Output the built string of all queries so far.
     */
    final public function toString(): string
    {
        return $this->build();
    }

    /**
     * Output the built string of all queries so far.
     */
    final public function __toString(): string
    {
        return $this->build();
    }
}
