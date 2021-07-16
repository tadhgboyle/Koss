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
    protected PDO $_pdo;
    protected PDOStatement $_query;
    protected array $_result;

    protected string $_table;
    protected string $_query_select = '';
    protected string $_query_from = '';
    protected string $_query_group_by = '';
    protected string $_query_order_by = '';
    protected string $_query_limit = '';
    protected string $_query_built = '';
    protected array $_joins = [];
    protected array $_selected_columns = [];
    protected array $_casts = [];

    /**
     * Create a new instance of SelectQuery.
     *
     * @param PDO $pdo Instance of PDO to use for actual querying.
     * @param array $columns Array of column names to use in SELECT clause, used for detecting duplicates from compiled statement.
     * @param string $query_select Valid MySQL statement to use for start of SELECT clause.
     * @param string|null $query_from If provided, this will be the base for the FROM clause. Generated and provided automatically by Koss in the `getAll()` or `getSome()` functions.
     * @param string $table Name of table to use for primary SELECT statement. This is used to pass into Join objects if needed.
     */
    public function __construct(PDO $pdo, array $columns, string $query_select, ?string $query_from = null, ?string $table = null)
    {
        $this->_pdo = $pdo;
        $this->_selected_columns = $columns;
        $this->_query_select = $query_select;

        if ($query_from != null) {
            $this->_query_from = $query_from;
        }

        if ($table != null) {
            $this->_table = $table;
        }
    }

    /**
     * Add columns to SELECT clause.
     *
     * @param array $columns Names of columns to select.
     *
     * @return SelectQuery This instance of SelectQuery.
     */
    public function columns(array $columns): SelectQuery
    {
        $new_columns = [];

        foreach ($columns as $column) {
            if (!in_array($column, $this->_selected_columns)) {
                $new_columns[] = $column;
            }
        }
        
        if (!str_ends_with($this->_query_select, ',')) {
            $this->_query_select .= ', ';
        }

        $this->_query_select .= implode(', ', Util::escapeStrings($new_columns));

        return $this;
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
     * @param Closure $callback Function to call to handle the join statement creation. Must accept an `LeftOuterJoin` param.
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
     * @param Closure $callback Function to call to handle the join statement creation. Must accept an `LeftOuterJoin` param.
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
        $this->_query_group_by = "GROUP BY `$column`";

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
        $this->_query_order_by = "ORDER BY `$column` $order";

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
        $this->_query_limit = "LIMIT $limit";

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
            $this->_casts[$column] = $type;
        }

        return $this;
    }

    public function execute(): array
    {
        if (!($this->_query = $this->_pdo->prepare($this->build()))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        if (!$this->_query->execute()) {
            // @codeCoverageIgnoreStart
            die(print_r($this->_pdo->errorInfo()));
            // @codeCoverageIgnoreEnd
        }

        $this->fetch();
        $this->reset();

        return $this->_result;
    }

    /**
     * Execute query with PDO and preform casts if casts have been defined.
     */
    private function fetch(): void
    {
        try {
            $this->_result = $this->_query->fetchAll(PDO::FETCH_OBJ);
            // @codeCoverageIgnoreStart
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        // @codeCoverageIgnoreEnd

        if (count($this->_casts) < 1) {
            return;
        }

        foreach ($this->_casts as $column => $type) {
            foreach ($this->_result as $row) {
                if (!isset($row->{$column})) {
                    continue;
                }

                settype($row->{$column}, $type);
            }
        }
    }

    public function build(): string
    {
        $this->_query_built = trim(preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $this->_query_select . ' ' . $this->_query_from . ' ' . Util::assembleJoinClause($this->_joins) . ' ' . Util::assembleWhereClause($this->_where) . ' ' . $this->_query_group_by . ' ' . $this->_query_order_by . ' ' . $this->_query_limit));

        return $this->_query_built;
    }

    public function reset(): void
    {
        $this->_query_select = '';
        $this->_query_from = '';
        $this->_query_group_by = '';
        $this->_query_order_by = '';
        $this->_query_limit = '';
        $this->_query_built = '';

        $this->_where = [];
        $this->_selected_columns = [];
        $this->_casts = [];
    }
}
