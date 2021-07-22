<?php

namespace Aberdeener\Koss\Queries;

use PDO;
use Closure;
use PDOException;
use PDOStatement;
use Aberdeener\Koss\Util\Util;
use Aberdeener\Koss\Queries\Joins\InnerJoin;
use Aberdeener\Koss\Queries\Joins\LeftOuterJoin;
use Aberdeener\Koss\Queries\Joins\RightOuterJoin;

class SelectQuery extends Query
{
    protected PDOStatement $query;
    protected array $result;

    protected string $querySelect = '';
    protected string $queryFrom = '';
    protected string $queryGroupBy = '';
    protected string $queryOrderBy = '';
    protected string $queryLimit = '';
    protected string $queryBuilt = '';
    protected array $joins = [];
    protected array $selectedColumns = [];
    protected array $casts = [];

    /**
     * Create a new instance of SelectQuery.
     *
     * @param PDO $pdo Instance of PDO to use for actual querying.
     * @param string $table Name of table to use for primary SELECT statement. This is used to pass into Join objects if needed.
     */
    public function __construct(
        protected PDO $pdo,
        protected ?string $table = null,
        protected ?string $rawQuery = null,
    ) {}

    /**
     * Add columns to SELECT clause.
     *
     * @param array $columns Names of columns to select.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function columns(array $columns): SelectQuery
    {
        $first = $this->handleFirst();

        $new_columns = [];

        foreach ($columns as $column) {
            if ($column == '*') {
                // TODO
            }

            if (!in_array($column, $this->selectedColumns)) {
                $new_columns[] = $column;
                $this->selectedColumns[] = $column;
            }
        }

<<<<<<< HEAD
        if (count($new_columns) && !$first && !str_ends_with($this->querySelect, ', ')) {
            $this->querySelect .= ', ';
=======
        if (!str_ends_with($this->_query_select, ',')) {
            $this->_query_select .= ', ';
>>>>>>> d0067a1dd94e20f143a4bbf7b105659e6b5b91e9
        }

        $this->querySelect .= implode(', ', Util::escapeStrings($new_columns));

        return $this;
    }

    private function handleFirst(): bool
    {
        if ($first = empty($this->querySelect)) {
            $this->querySelect = 'SELECT ';
            $this->queryFrom = 'FROM ' . Util::escapeStrings($this->table);
        }

        return $first;
    }

    /**
     * Add a single column to the SELECT clause.
     *
     * @param string $column Name of column.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function column(string $column): SelectQuery
    {
        return $this->columns([$column]);
    }

    /**
     * Preform an INNER JOIN on this select statement.
     *
     * @param Closure $callback Function to call to handle the join statement creation. Must accept an `InnerJoin` param.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function innerJoin(Closure $callback): SelectQuery
    {
        $callback(new InnerJoin($this));

        return $this;
    }

    /**
     * Preform a LEFT OUTER JOIN on this select statement.
     *
     * @param Closure $callback Function to call to handle the join statement creation. Must accept a `LeftOuterJoin` param.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function leftOuterJoin(Closure $callback): SelectQuery
    {
        $callback(new LeftOuterJoin($this));

        return $this;
    }

    /**
     * Preform a RIGHT OUTER JOIN on this select statement.
     *
     * @param Closure $callback Function to call to handle the join statement creation. Must accept a `RightOuterJoin` param.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function rightOuterJoin(Closure $callback): SelectQuery
    {
        $callback(new RightOuterJoin($this));

        return $this;
    }

    /**
     * Add a GROUP BY statement to this query.
     *
     * @param string $column Name of column to group by.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function groupBy(string $column): SelectQuery
    {
        $column = Util::escapeStrings($column);
        $this->queryGroupBy = "GROUP BY {$column}";

        return $this;
    }

    /**
     * Add an ORDER BY statement to this query.
     *
     * @param string $column Column name to sort results by.
     * @param string|null $order Order to sort by. Can be `DESC` or `ASC`. Defaults to `DESC`.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function orderBy(string $column, string $order = 'DESC'): SelectQuery
    {
        $column = Util::escapeStrings($column);
        $this->queryOrderBy = "ORDER BY {$column} $order";

        return $this;
    }

    /**
     * Add a LIMIT statement to this query.
     *
     * @param int $limit Number of rows to limit final resultset to.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function limit(int $limit): SelectQuery
    {
        $this->queryLimit = "LIMIT {$limit}";

        return $this;
    }

    /**
     * Cast an individual column to a type.
     *
     * @param string $column Name of column to preform cast on.
     * @param string $type Type to cast column data to.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function cast(string $column, string $type): SelectQuery
    {
        $this->casts([$column => $type]);

        return $this;
    }

    /**
     * Cast multiple columns to respective types.
     *
     * @param array $casts Column => Type array of casts to preform.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function casts(array $casts): SelectQuery
    {
        foreach ($casts as $column => $type) {
            $this->casts[$column] = $type;
        }

        return $this;
    }

    public function execute(): array
    {
        if (!($this->query = $this->pdo->prepare($this->build()))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        if (!$this->query->execute()) {
            // @codeCoverageIgnoreStart
            die(print_r($this->pdo->errorInfo()));
            // @codeCoverageIgnoreEnd
        }

        $this->fetch();
        $this->reset();

        return $this->result;
    }

    /**
     * Execute query with PDO and preform casts if casts have been defined.
     */
    private function fetch(): void
    {
        try {
            $this->result = $this->query->fetchAll(PDO::FETCH_OBJ);
            // @codeCoverageIgnoreStart
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        // @codeCoverageIgnoreEnd

        if (count($this->casts) < 1) {
            return;
        }

        foreach ($this->casts as $column => $type) {
            foreach ($this->result as $row) {
                if (!isset($row->{$column})) {
                    continue;
                }

                settype($row->{$column}, $type);
            }
        }
    }

    public function build(): string
    {
        $this->queryBuilt = $this->cleanString(
            is_null($this->rawQuery)
                ? $this->querySelect . ' ' . $this->queryFrom . ' ' . Util::assembleJoinClause($this->joins) . ' ' . Util::assembleWhereClause($this->whereClauses) . ' ' . $this->queryGroupBy . ' ' . $this->queryOrderBy . ' ' . $this->queryLimit
                : $this->rawQuery
        );

        return $this->queryBuilt;
    }

    public function reset(): void
    {
        $this->querySelect = '';
        $this->queryFrom = '';
        $this->queryGroupBy = '';
        $this->queryOrderBy = '';
        $this->queryLimit = '';
        $this->queryBuilt = '';

        $this->whereClauses = [];
        $this->selectedColumns = [];
        $this->casts = [];
    }
}
