<?php

/**
 * 
 * Koss - Write MySQL queries faster than ever before in PHP
 * Inspired by Laravel Eloquent
 * 
 * @author Tadhg Boyle
 * @since October 2020
 */
class KossSelectQuery implements IKossQuery
{

    protected PDO $_pdo;

    protected PDOStatement $_query;

    protected string
        $_query_select = '',
        $_query_from = '',
        $_query_group_by = '',
        $_query_order_by = '',
        $_query_limit = '',
        $_query_built = '';

    protected array $_where = array();

    public function __construct(PDO $pdo, string $query_select)
    {
        $this->_pdo = $pdo;
        $this->_query_select = $query_select;
    }

    public static function get(PDO $pdo, string $table, array $columns): KossSelectQuery
    {
        $columns = implode(', ', $columns);
        return new self($pdo, "SELECT $columns FROM `$table`");
    }

    public function where(string $column, string $operator, string $matches = null): KossSelectQuery
    {
        if ($matches == null) {
            $matches = $operator;
            $operator = '=';
        }

        $this->_where[] = [
            'column' => $column,
            'operator' => $operator,
            'matches' => $matches
        ];

        return $this;
    }

    public function like(string $column, string $like): KossSelectQuery
    {
        return $this->where($column, 'LIKE', "%$like%");
    }

    public function groupBy(string $column): KossSelectQuery
    {
        $this->_query_group_by = "GROUP BY `$column`";
        return $this;
    }

    public function orderBy(string $column, string $order): KossSelectQuery
    {
        $this->_query_order_by = "ORDER BY `$column` $order";
        return $this;
    }

    public function limit(int $limit): KossSelectQuery
    {
        $this->_query_limit = "LIMIT $limit";
        return $this;
    }

    public function when($expression, callable $callback, callable $fallback = null): KossSelectQuery
    {
        Koss::when($expression, $callback, $fallback);
        return $this;
    }

    public function build(): string
    {
        $this->_query_built = $this->_query_select . ' ' . $this->_query_from . ' ' . Koss::assembleWhereClause($this->_where) . ' ' . $this->_query_group_by . ' ' . $this->_query_order_by . ' ' . $this->_query_limit;
        return $this->_query_built;
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
            } else die(print_r($this->_pdo->errorInfo()));
        }
        return null;
    }

    public function reset(): void
    {
        $this->_where = array();
        $this->_query_select = $this->_query_from = $this->_query_group_by = $this->_query_order_by = $this->_query_limit = $this->_query_built = '';
    }

    public function __toString(): string
    {
        return $this->build();
    }
}