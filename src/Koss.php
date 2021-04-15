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
    
    /**
     * Create new Koss instance.
     *
     * @param string $host Hostname for MySQL server to use.
     * @param int $port Numerical port number for MySQL server.
     * @param string $database Name of database to use.
     * @param string $username Account username to login to  on MySQL server.
     * @param string $password Account password.
     */
    public function __construct(string $host, int $port, string $database, string $username, string $password)
    {
        require_once('IKossQuery.php');
        require_once('KossSelectQuery.php');
        require_once('KossUpdateQuery.php');

        try {

            $this->_pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get all columns in $table.
     * 
     * @param string $table Name of table to select all columns from.
     * @return KossSelectQuery New instance of SelectQuery class
     */
    public function getAll(string $table): KossSelectQuery
    {
        return $this->getSome($table, ['*']);
    }

    /**
     * Get specified $columns in a $table.
     * 
     * @param string $table Name of table to select from.
     * @param array|string $columns Name of single column, or array of column names to select.
     * @return KossSelectQuery New instance of SelectQuery class
     */
    public function getSome(string $table, array|string $columns): KossSelectQuery
    {
        if (!is_array($columns)) {
            $columns = (array) $columns;
        }

        $this->_query_instance = KossSelectQuery::get($this->_pdo, $table, $columns);

        return $this->_query_instance;
    }

    /**
     * Insert new row into a table.
     * Sets this instance's $_query_instance to new UpdateQuery class.
     * Forwards to KossUpdateQuery to handle.
     * 
     * @param string $table Table to update.
     * @param array $row Column name/Value pairs to insert into table.
     * @return KossUpdateQuery New instance of UpdateQuery class.
     */
    public function insert(string $table, array $row): KossUpdateQuery
    {
        $this->_query_instance = KossUpdateQuery::insert($this->_pdo, $table, $row);

        return $this->_query_instance;
    }

    /**
     * Update an existing row.
     * 
     * @param string $table Table to update.
     * @param array $values Values to update into table.
     * @return KossUpdateQuery New instance of UpdateQuery class.
     */
    public function update(string $table, array $values): KossUpdateQuery
    {
        $this->_query_instance = KossUpdateQuery::update($this->_pdo, $table, $values);
        
        return $this->_query_instance;
    }

    /**
     * Allow running raw queries. Detects which sub class to initialize and execute.
     * 
     * @param string $query Raw SQL query to run.
     * @return array|int Array of select values, or int of number of rows changed - depending on statement type.
     */
    public function execute(string $query): array
    {
        $token = explode(' ', $query)[0];

        switch ($token) {
            case "SELECT":
                return (new KossSelectQuery($this->_pdo, [], $query))->execute();
                break;

            case "INSERT":
            case "UPDATE":
                return (new KossUpdateQuery($this->_pdo, $query))->execute();
                break;

            default:
                throw new PDOException("Unsupported start of MySQL query string. Token: $token.");
                break;
        }
    }

    /**
     * Run a Koss function only when the specified $expression is true.
     * 
     * @param IKossQuery $instance Instance of Select/Update query class to pass in background to callable function.
     * @param callable|bool $expression Expression to run, must return bool
     * @param callable $callback Ran if $expression is true
     * @param callable $fallback (optional) Ran if $expression is false
     */
    public static function when(IKossQuery $instance, callable|bool $expression, callable $callback, callable $fallback = null): void
    {
        if ((is_callable($expression) && $expression()) || $expression) {
            $callback($instance);
        } else if (((is_callable($expression) && !$expression()) || !$expression) && $fallback != null) {
            $fallback($instance);
        }
    }

    public static function handleWhereOperation(string $column, string $operator, string $matches = null): array
    {
        if ($matches == null) {
            $matches = $operator;
            $operator = '=';
        }

        if (!in_array($operator, ['=', '<>', 'LIKE'])) {
            throw new PDOException("Unsupported WHERE clause operator. Operator: $operator.");
        }

        return [
            'column' => $column,
            'operator' => $operator,
            'matches' => $matches
        ];
    }

    /**
     * Assemble all where clauses into one string using appropriate MySQL syntax.
     * 
     * @param array $where Array of columns, operators and matches to create into string.
     * @return string Assembled clause.
     */
    public static function assembleWhereClause(array $where): string
    {
        $first = true;
        $return = '';

        foreach ($where as $clause) {
            if ($first) {
                $return .= 'WHERE ';
                $first = false;
            } else {
                $return .= 'AND ';
            }

            $return .= '`' . $clause['column'] . '` ' . $clause['operator'] . ' \'' . $clause['matches'] . '\' ';
        }

        return $return;
    }
    
    /**
     * Escape array of strings by adding $key to front and end of each string. 
     * Used for preparing column and values for use in query statements.
     *
     * @param array $strings Strings to be escaped.
     * @param string $key Key to add to front and end of each string.
     * @return array Escaped strings.
     */
    public static function escapeStrings(array $strings, string $key = '`'): array
    {
        $escaped = array();

        foreach ($strings as $string) {
            $escaped[] = $key . $string . $key;
        }

        return $escaped;
    }
}
