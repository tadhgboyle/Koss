<?php

namespace Aberdeener\Koss;

use PDO;
use PDOException;
use Aberdeener\Koss\Util\Util;
use Aberdeener\Koss\Queries\Query;
use Aberdeener\Koss\Queries\SelectQuery;
use Aberdeener\Koss\Queries\UpdateQuery;
use Aberdeener\Koss\Exceptions\StatementException;

/**
 * Koss - Write MySQL queries faster than ever before in PHP.
 *
 * @author Tadhg Boyle
 *
 * @since October 2020
 */
class Koss
{
    protected PDO $_pdo;
    protected PDOStatement $_query;

    protected Query $_query_instance;

    protected array $_where = [];

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
     *
     * @return SelectQuery New instance of SelectQuery class
     */
    public function getAll(string $table): SelectQuery
    {
        return $this->getSome($table, ['*']);
    }

    /**
     * Get specified $columns in a $table.
     *
     * @param string $table Name of table to select from.
     * @param array|string $columns Name of single column, or array of column names to select.
     *
     * @return SelectQuery New instance of SelectQuery class
     */
    public function getSome(string $table, array | string $columns): SelectQuery
    {
        if (!is_array($columns)) {
            $columns = (array) $columns;
        }

        $csv_columns = implode(', ', in_array('*', $columns) ? ['*'] : Util::escapeStrings($columns));

        $this->_query_instance = new SelectQuery($this->_pdo, $columns, "SELECT $csv_columns", "FROM `$table`", $table);

        return $this->_query_instance;
    }

    /**
     * Insert new row into a table.
     * Sets this instance's $_query_instance to new UpdateQuery class.
     * Forwards to UpdateQuery to handle.
     *
     * @param string $table Table to update.
     * @param array $row Column name/Value pairs to insert into table.
     *
     * @return UpdateQuery New instance of UpdateQuery class.
     */
    public function insert(string $table, array $row): UpdateQuery
    {
        $columns = implode(', ', Util::escapeStrings(array_keys($row)));
        $values = implode(', ', Util::escapeStrings(array_values($row), "'"));

        $this->_query_instance = new UpdateQuery($this->_pdo, "INSERT INTO `$table` ($columns) VALUES ($values)");

        return $this->_query_instance;
    }

    /**
     * Update an existing row.
     *
     * @param string $table Table to update.
     * @param array $values Values to update into table.
     *
     * @return UpdateQuery New instance of UpdateQuery class.
     */
    public function update(string $table, array $values): UpdateQuery
    {
        $values_compiled = '';

        foreach ($values as $column => $value) {
            $values_compiled .= "`$column` = '$value', ";
        }

        $values_compiled = rtrim($values_compiled, ',');

        $this->_query_instance = new UpdateQuery($this->_pdo, "UPDATE `$table` SET $values_compiled");

        return $this->_query_instance;
    }

    /**
     * Allow running raw queries. Detects which sub class to initialize and execute.
     *
     * @param string $query Raw SQL query to run.
     *
     * @return array|int Array of select values, or int of number of rows changed - depending on statement type.
     */
    public function execute(string $query): array
    {
        $token = explode(' ', $query)[0];

        switch ($token) {
            case 'SELECT':
                return (new SelectQuery($this->_pdo, [], $query))->execute();
                break;

            case 'INSERT':
            case 'UPDATE':
                return (new UpdateQuery($this->_pdo, $query))->execute();
                break;

            default:
                throw new StatementException("Unsupported start of MySQL query string. Token: $token.");
                break;
        }
    }
}
