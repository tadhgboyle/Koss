<?php 

/**
 * 
 * Koss - Write MySQL queries faster than ever before in PHP
 * 
 * @author Tadhg Boyle
 * @since October 2020
 */
class Koss {

    protected PDO $_pdo;

    protected PDOStatement $_query;

    protected array $_where = array();

    protected 
        $_query_select = '',
        $_query_from = '',
        $_query_group_by = '',
        $_query_order_by = '',
        $_query_limit = '',
        $_query_built = '';

    public function __construct(string $host, string $port, string $database, string $username, string $password)
    {
        try {
            $this->_pdo = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database, $username, $password);
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Execute a raw MySQL query and store it in a KossResultSet
     */
    public function rawQuery(string $query): KossResultSet
    {
        $this->_query_built = $query;
        return $this->execute();
    }

    /**
     * Get all columns in $table
     */
    public function getAll(string $table): Koss 
    {
        return $this->getSome($table, '*');
    }

    /**
     * Get specified $columns in a $table
     */
    public function getSome(string $table, string ...$columns): Koss
    {
        $columns = implode(', ', $columns);
        $this->_query_select = "SELECT $columns FROM `$table`";
        return $this;
    }

    public function where(string $column, string $operator, string $matches = null): Koss
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

    public function like(string $column, string $like): Koss
    {
        return $this->where($column, 'LIKE', "%$like%");
    }

    private function assembleWhereClause(): string 
    {
        $first = true;
        $return = '';
        foreach ($this->_where as $clause) {
            if ($first) {
                $return .= 'WHERE ';
                $first = false;
            }
            else $return .= 'AND ';

            $return .= '`' . $clause['column'] . '` ' . $clause['operator'] . ' \'' . $clause['matches'] . '\' ';
        }
        return $return;
    }

    public function orderBy(string $column, string $order = 'DESC'): Koss
    {
        $this->_query_order_by = "ORDER BY `$column` $order";
        return $this;
    }

    public function limit(int $limit): Koss
    {
        $this->_query_limit = "LIMIT $limit";
        return $this;
    }

    /**
     * Run a Koss function only when the specified $expression is true
     * 
     * @param mixed $expression - Expression to run, must return bool
     * @param mixed $callback - Ran if $expression is true
     * @param mixed $fallback (optional) - Ran if $expression is false
     */
    public function when($expression, $callback, $fallback = null): Koss
    {
        if ((is_callable($expression) && $expression()) || $expression) {
            $callback();
        }

        return $this;
    }

    /**
     * Debugging only: Output the built string of all queries so far
     */
    public function __toString(): string
    {
        return $this->build();
    }

    /**
     * Select only the first row in the results
     */
    public function first()
    {
        return $this->_result[0];
    }

    /**
     * Reset current working query to be prepared for next query
     */
    private function reset() 
    {
        $this->_query_select = '';
        $this->_query_from = '';
        $this->_query_group_by = '';
        $this->_query_order_by = '';
        $this->_query_limit = '';
        $this->_query_built = '';
    }

    /**
     * Assemble all non-empty clauses into one
     */
    private function build(): string
    {
        $this->_query_built = $this->_query_select . ' ' . $this->_query_from . ' ' . $this->assembleWhereClause() . ' ' . $this->_query_group_by . ' ' . $this->_query_order_by . ' ' . $this->_query_limit;
        return $this->_query_built;
    }

    /**
     * Execute this query and store result
     */
    public function execute()
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
}