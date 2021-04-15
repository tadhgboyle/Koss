<?php

namespace Aberdeener\Koss;

use PDO;
use PDOException;
use Aberdeener\Koss\Queries\KossSelectQuery;
use Aberdeener\Koss\Queries\KossUpdateQuery;

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
}
