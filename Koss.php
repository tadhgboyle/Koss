<?php

/**
 * 
 * Koss - Write MySQL queries faster than ever before in PHP
 * 
 * @author Tadhg Boyle
 * @since October 2020
 */
class Koss
{

    protected PDO $_pdo;

    protected PDOStatement $_query;

    protected array $_where = array();

    protected $_query_instance;

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
     * Get all columns in $table
     * Initiates a KossSelectQuery instance
     */
    public function getAll(string $table): KossSelectQuery
    {
        return $this->getSome($table, '*');
    }

    /**
     * Get specified $columns in a $table
     * Initiates a KossSelectQuery instance
     */
    public function getSome(string $table, string ...$columns): KossSelectQuery
    {
        $columns = implode(', ', $columns);
        $this->_query_instance = new KossSelectQuery($this->_pdo, "SELECT $columns FROM `$table`");
        return $this->_query_instance;
    }

    /**
     * Insert a new row into $table
     * Initiates a KossUpdateQuery instance
     */
    public function insert(string $table, array $row): KossUpdateQuery
    {
        $this->_query_instance = KossUpdateQuery::insert($this->_pdo, $table, $row);
        return $this->_query_instance;

    }

    /**
     * Update an existing row
     * Initiates a KossUpdateQuery instance
     */
    public function update(string $table, array $values, array $where): KossUpdateQuery
    {
        $where = Koss::assembleWhereClause($where);
        $values = Koss::assembleWhereClause($values);
        $this->_query_instance = new KossUpdateQuery($this->_pdo, "UPDATE $table SET $values WHERE $where");
        return $this->_query_instance;
    }

    /**
     * Allow running raw queries. Auto detects which sub class to initialize and execute
     */
    public function execute(string $query): array
    {
        $token = explode(' ', $query)[0];
        switch ($token) {
            case "SELECT":
                $kossSelectQuery = new KossSelectQuery($this->_pdo, $query);
                return $kossSelectQuery->execute();
                break;
            case "INSERT":
            case "UPDATE":
                $kossUpdateQuery = new KossUpdateQuery($this->_pdo, $query);
                return $kossUpdateQuery->execute();
                break;
            default:
                throw new PDOException("Invalid start of MySQL query string. Token: $token");
                break;
        }
    }

    /**
     * Run a Koss function only when the specified $expression is true
     * 
     * @param mixed $expression - Expression to run, must return bool
     * @param mixed $callback - Ran if $expression is true
     * @param mixed $fallback (optional) - Ran if $expression is false
     */
    public static function when($expression, $callback, $fallback = null): void
    {
        if ((is_callable($expression) && $expression()) || $expression) {
            $callback();
        } else if (((is_callable($expression) && !$expression()) || !$expression) && $fallback != null) {
            $fallback();
        }
    }

    /**
     * Janky workaround for when()
     */
    public function limit(int $rows): void
    {
        $this->_query_instance->limit($rows);
    }

    /**
     * Janky workaround for when()
     */
    public function orderBy(string $column, string $sort): void
    {
        $this->_query_instance->orderBy($column, $sort);
    }

    /**
     * Janky workaround for when()
     */
    public function where(string $table, string $operator, string $matches = null): void
    {
        $this->_query_instance->where($table, $operator, $matches);
    }

    /**
     * Janky workaround for when()
     */
    public function groupBy(string $column): void
    {
        $this->_query_instance->groupBy($column);
    }

    /**
     * Janky workaround for when()
     */
    public function like(string $column, string $like): void
    {
        $this->_query_instance->like($column, $like);
    }

    /**
     * Assemble all where clauses into one string using appropriate MySQL syntax
     */
    public static function assembleWhereClause(array $where): string
    {
        $first = true;
        $return = '';
        foreach ($where as $clause) {
            if ($first) {
                $return .= 'WHERE ';
                $first = false;
            } else $return .= 'AND ';

            $return .= '`' . $clause['column'] . '` ' . $clause['operator'] . ' \'' . $clause['matches'] . '\' ';
        }
        return $return;
    }
}

interface IKossQuery
{

    /**
     * Create new instance of a KossQuery by injecting the beginning statement
     */
    public function __construct(PDO $pdo, string $query);

    /**
     * Execute Koss function under certain conditions
     */
    public function when($expression, callable $callback, callable $fallback = null): IKossQuery;

    /**
     * Assemble queries into MySQL statement
     */
    public function build(): string;

    /**
     * Execute repsective query and store result
     */
    public function execute();

    /**
     * Reset query strings
     */
    public function reset(): void;

    /**
     * Debugging only: Output the built string of all queries so far
     */
    public function __toString(): string;
}

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

class KossUpdateQuery implements IKossQuery
{
    protected PDO $_pdo;

    protected PDOStatement $_query;

    protected string
        $_query_insert = '',
        $_query_duplicate_key = '',
        $_query_built = '';

    protected array $_where = array();

    public function __construct(PDO $pdo, string $query)
    {
        $this->_pdo = $pdo;
        $this->_query_insert = $query;
    }

    public static function insert(PDO $pdo, string $table, array $row): KossUpdateQuery
    {
        $quotify = function ($string) {
            return '\'' . $string . '\'';
        };
        $backtickify = function ($string) {
            return '`' . $string . '`';
        };
        $columns = implode(', ', array_map($backtickify, array_keys($row)));
        $values = implode(', ', array_map($quotify, array_values($row)));
        return new self($pdo, "INSERT INTO `$table` ($columns) VALUES ($values)");
    }

    public function onDuplicateKey(array $values): KossUpdateQuery
    {
        $compiled_values = '';
        foreach ($values as $column => $value) {
            $compiled_values .= "`$column` = '$value' ";
        }
        $this->_query_duplicate_key = "ON DUPLICATE KEY UPDATE $compiled_values";
        return $this;
    }

    public function when($expression, callable $callback, callable $fallback = null): KossUpdateQuery
    {
        Koss::when($expression, $callback, $fallback);
        return $this;
    }

    public function build(): string
    {
        $this->_query_built = $this->_query_insert . ' ' . $this->_query_duplicate_key . ' ' . Koss::assembleWhereClause($this->_where);
        return $this->_query_built;
    }

    public function execute()
    {
        if ($this->_query = $this->_pdo->prepare($this->build())) {
            if ($this->_query->execute()) {
                try {
                    $this->_result = $this->_query->rowCount();
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
        $this->_query_insert = $this->_query_duplicate_key = '';
    }

    public function __toString(): string
    {
        return $this->build();
    }
}
