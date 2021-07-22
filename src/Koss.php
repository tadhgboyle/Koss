<?php

namespace Aberdeener\Koss;

use PDO;
use Aberdeener\Koss\Queries\InsertQuery;
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
    protected PDO $pdo;
    protected PDOStatement $query;

    protected Query $queryInstance;

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
        $this->pdo = new PDO("mysql:host={$host};port={$port};dbname={$database}", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
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

        $this->queryInstance = (new SelectQuery($this->pdo, $table))->columns($columns);

        return $this->queryInstance;
    }

    /**
     * Insert new row into a table.
     * Sets this instance's $queryInstance to new InsertQuery class.
     * Forwards to InsertQuery to handle.
     *
     * @param string $table Table to update.
     * @param array $row Column name/Value pairs to insert into table.
     *
     * @return InsertQuery New instance of InsertQuery class.
     */
    public function insert(string $table, array $row): InsertQuery
    {
        $this->queryInstance = (new InsertQuery($this->pdo, $table))->insert(array_keys($row), array_values($row));
        //$this->queryInstance = new UpdateQuery($this->pdo, $table, "INSERT INTO `$table` ($columns) VALUES ($values)");

        return $this->queryInstance;
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
        $this->queryInstance = (new UpdateQuery($this->pdo, $table))->update($values);

        return $this->queryInstance;
    }

    /**
     * Allow running raw queries. Detects which sub class to initialize and execute.
     *
     * @param string $query Raw SQL query to run.
     *
     * @return array|int Array of select values, or int of number of rows changed - depending on statement type.
     */
    public function execute(string $query): array | int
    {
        $token = explode(' ', $query)[0];

        switch ($token) {
            case 'SELECT':
                return (new SelectQuery($this->pdo, rawQuery: $query))->execute();

            case 'INSERT':
                return (new InsertQuery($this->pdo, rawQuery: $query))->execute();

            case 'UPDATE':
                return (new UpdateQuery($this->pdo, rawQuery: $query))->execute();

            default:
                throw new StatementException("Unsupported start of MySQL query string. Token: {$token}.");
        }
    }
}
