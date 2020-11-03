<?php

/**
 * 
 * Koss - Write MySQL queries faster than ever before in PHP
 * Inspired by Laravel Eloquent
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

        require_once('IKossQuery.php');
        require_once('KossSelectQuery.php');
        require_once('KossUpdateQuery.php');

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
        return $this->getSome($table, ['*']);
    }

    /**
     * Get specified $columns in a $table
     * Initiates a KossSelectQuery instance
     */
    public function getSome(string $table, array $columns): KossSelectQuery
    {
        $this->_query_instance = KossSelectQuery::get($this->_pdo, $table, $columns);
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
    public function update(string $table, array $values): KossUpdateQuery
    {
        $this->_query_instance = KossUpdateQuery::update($this->_pdo, $table, $values);
        return $this->_query_instance;
    }

    /**
     * Allow running raw queries. Detects which sub class to initialize and execute
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
                throw new PDOException("Unsupported start of MySQL query string. Token: $token.");
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