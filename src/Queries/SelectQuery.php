<?php

namespace Aberdeener\Koss\Queries;

use PDO;
use PDOException;
use PDOStatement;
use Aberdeener\Koss\Util\Util;
use Aberdeener\Koss\Queries\Query;
use Aberdeener\Koss\Queries\Joins\InnerJoin;
use Aberdeener\Koss\Queries\Joins\FullOuterJoin;
use Aberdeener\Koss\Queries\Joins\LeftOuterJoin;
use Aberdeener\Koss\Queries\Joins\RightOuterJoin;

class SelectQuery implements Query
{

    protected PDO $_pdo;
    protected PDOStatement $_query;

    protected string $_table;
    protected string $_query_select = '';
    protected string $_query_from = '';
    protected string $_query_group_by = '';
    protected string $_query_order_by = '';
    protected string $_query_limit = '';
    protected string $_query_built = '';
    protected array $_where = array();
    protected array $_joins = array();
    protected array $_selected_columns = array();

    public function __construct(PDO $pdo, array $columns, string $query_select, string $query_from = null, string $table = null)
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

    public static function get(PDO $pdo, string $table, array $columns): SelectQuery
    {        
        $new_columns = implode(', ', ($columns[0] != '*') ? Util::escapeStrings($columns) : $columns);

        return new SelectQuery($pdo, $columns, "SELECT $new_columns", "FROM `$table`", $table);
    }

    public function columns(array $columns): SelectQuery
    {
        $new_columns = array();

        foreach ($columns as $column) {
            if (!in_array($column, $this->_selected_columns)) {
                $new_columns[] = $column;
            }
        }

        if (substr($this->_query_select, -1) != ',') {
            $this->_query_select .= ', ';
        }

        $this->_query_select .= implode(', ', Util::escapeStrings($new_columns));

        return $this;
    }

    public function column(string $column): SelectQuery 
    {
        return $this->columns([$column]);
    }

    public function where(string $column, string $operator, string $matches = null): SelectQuery
    {
        $append = Util::handleWhereOperation($column, $operator, $matches);

        if ($append != null) {
            $this->_where[] = $append;
        }

        return $this;
    }
    
    /**
     * Preform an INNER JOIN on this select statement.
     * Reroutes to `innerJoin()` as per default MySQL behaviour.
     *
     * @param callable $callback Function to call to handle the join statement creation. Must accept an `InnerJoin` param.
     * @return SelectQuery This instance of SelectQuery.
     */
    public function join(callable $callback): SelectQuery
    {
        return $this->innerJoin($callback);
    }

    /**
     * Preform an INNER JOIN on this select statement.
     *
     * @param callable $callback Function to call to handle the join statement creation. Must accept an `InnerJoin` param.
     * @return SelectQuery This instance of SelectQuery.
     */
    public function innerJoin(callable $callback): SelectQuery
    {
        $callback(new InnerJoin($this));

        return $this;
    }

    /**
     * Preform a LEFT OUTER JOIN on this select statement.
     *
     * @param callable $callback Function to call to handle the join statement creation. Must accept an `LeftOuterJoin` param.
     * @return SelectQuery This instance of SelectQuery.
     */
    public function leftOuterJoin(callable $callback): SelectQuery
    {
        $callback(new LeftOuterJoin($this));
        
        return $this;
    }

    /**
     * Preform a RIGHT OUTER JOIN on this select statement.
     *
     * @param callable $callback Function to call to handle the join statement creation. Must accept an `LeftOuterJoin` param.
     * @return SelectQuery This instance of SelectQuery.
     */
    public function rightOuterJoin(callable $callback): SelectQuery
    {
        $callback(new RightOuterJoin($this));

        return $this;
    }

    /**
     * Preform a FULL OUTER JOIN on this select statement.
     *
     * @param callable $callback Function to call to handle the join statement creation. Must accept an `LeftOuterJoin` param.
     * @return SelectQuery This instance of SelectQuery.
     */
    public function fullOuterJoin(callable $callback): SelectQuery
    {
        $callback(new FullOuterJoin($this));

        return $this;
    }

    /**
     * Add a LIKE statement to this query.
     * Rereoutes to `where()` and uses `"LIKE"` as the operator.
     *
     * @param string $column Column name to search in.
     * @param string $like Value to attempt to find. Must provide `"%"` as needed.
     * @return SelectQuery This instance of SelectQuery.
     */
    public function like(string $column, string $like): SelectQuery
    {
        return $this->where($column, 'LIKE', $like);
    }

    public function groupBy(string $column): SelectQuery
    {
        $this->_query_group_by = "GROUP BY `$column`";
        return $this;
    }

    public function orderBy(string $column, string $order): SelectQuery
    {
        $this->_query_order_by = "ORDER BY `$column` $order";
        return $this;
    }

    public function limit(int $limit): SelectQuery
    {
        $this->_query_limit = "LIMIT $limit";
        return $this;
    }

    public function when(callable|bool $expression, callable $callback, callable $fallback = null): SelectQuery
    {
        Util::when($this, $expression, $callback, $fallback);

        return $this;
    }

    public function execute(): array
    {
        if ($this->_query = $this->_pdo->prepare($this->build())) {
            if ($this->_query->execute()) {

                try {

                    $this->_result = $this->_query->fetchAll(PDO::FETCH_OBJ);
                    $this->reset();

                    return $this->_result;

                } catch (PDOException $e) {
                    die($e->getMessage());
                }

            } else {
                die(print_r($this->_pdo->errorInfo()));
            }
        }

        return null;
    }

    public function build(): string
    {
        $this->_query_built = $this->_query_select . ' ' . $this->_query_from . ' ' . Util::assembleJoinClause($this->_joins) . ' ' . Util::assembleWhereClause($this->_where) . ' ' . $this->_query_group_by . ' ' . $this->_query_order_by . ' ' . $this->_query_limit;
        return $this->_query_built;
    }

    public function reset(): void
    {
        $this->_where = $this->_selected_columns = array();
        $this->_query_select = $this->_query_from = $this->_query_group_by = $this->_query_order_by = $this->_query_limit = $this->_query_built = '';
    }

    public function toString(): string
    {
        return $this->build();
    }

    public function __toString(): string
    {
        return $this->build();
    }
}