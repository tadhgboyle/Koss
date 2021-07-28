<?php

namespace Aberdeener\Koss\Queries\Traits;

use Aberdeener\Koss\Util\Util;

trait HasWhereClauses
{
    use DynamicWhereCalls;

    /** @var string[] */
    protected array $whereClauses = [];

    /**
     * Preform an AND WHERE operation in this query.
     *
     * @param string $column Name of column to use.
     * @param string $operator Operator to use for comparison.
     * @param string|null $matches Value to compare to. If not provided, $operator will be used and `=` will be assumed as operator.
     *
     * @return static This instance of Query.
     */
    final public function where(string $column, string $operator, ?string $matches = null): static
    {
        $append = Util::handleWhereOperation($column, $operator, $matches);

        if (!is_null($append)) {
            $this->whereClauses[] = $append;
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
     * @return static This instance of Query.
     */
    final public function orWhere(string $column, string $operator, ?string $matches = null): static
    {
        $append = Util::handleWhereOperation($column, $operator, $matches, 'OR');

        if (!is_null($append)) {
            $this->whereClauses[] = $append;
        }

        return $this;
    }

    /**
     * Add an AND LIKE statement to this query.
     * Rereoutes to `where()` and uses `"LIKE"` as the operator.
     *
     * @param string $column Column name to search in.
     * @param string $like Value to attempt to find. Must provide `"%"` as needed.
     *
     * @return static This instance of Query.
     */
    final public function like(string $column, string $like): static
    {
        return $this->where($column, 'LIKE', $like);
    }

    /**
     * Add an OR LIKE statement to this query.
     * Rereoutes to `orWhere()` and uses `"LIKE"` as the operator.
     *
     * @param string $column Column name to search in.
     * @param string $like Value to attempt to find. Must provide `"%"` as needed.
     *
     * @return static This instance of Query.
     */
    final public function orLike(string $column, string $like): static
    {
        return $this->orWhere($column, 'LIKE', $like);
    }
}