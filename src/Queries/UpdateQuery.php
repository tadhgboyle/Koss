<?php

namespace Aberdeener\Koss\Queries;

use PDO;
use PDOStatement;
use Aberdeener\Koss\Util\Util;

class UpdateQuery extends Query
{
    protected PDO $_pdo;
    protected PDOStatement $_query;
    protected int $_result;

    protected string $_query_insert = '';
    protected string $_query_where = '';
    protected string $_query_duplicate_key = '';
    protected string $_query_built = '';

    /**
     * Create new instance of UpdateQuery. Should only be used internally by Koss.
     *
     * @param PDO $pdo PDO connection to be used.
     * @param string $query Query string to start with.
     */
    public function __construct(PDO $pdo, string $query)
    {
        $this->_pdo = $pdo;
        $this->_query_insert = $query;
    }

    /**
     * Key/Value array of column/value to insert if a duplicate key is found during this update query.
     *
     * @param array $values Key => Value array of row to update if duplicate key is found.
     *
     * @return UpdateQuery This instance of UpdateQuery.
     */
    public function onDuplicateKey(array $values): UpdateQuery
    {
        $compiled_values = '';

        foreach ($values as $column => $value) {
            $compiled_values .= "`$column` = '$value' ";
        }

        $this->_query_duplicate_key = "ON DUPLICATE KEY UPDATE $compiled_values";

        return $this;
    }

    public function execute(): int
    {
        if (!($this->_query = $this->_pdo->prepare($this->build()))) {
            // @codeCoverageIgnoreStart
            return -1;
            // @codeCoverageIgnoreEnd
        }

        if (!$this->_query->execute()) {
            // @codeCoverageIgnoreStart
            die(print_r($this->_pdo->errorInfo()));
            // @codeCoverageIgnoreEnd
        }

        $this->_result = $this->_query->rowCount();
        $this->reset();

        return $this->_result;
    }

    public function build(): string
    {
        $this->_query_built = trim(preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $this->_query_insert . ' ' . $this->_query_duplicate_key . ' ' . Util::assembleWhereClause($this->_where)));

        return $this->_query_built;
    }

    public function reset(): void
    {
        $this->_where = [];
        $this->_query_built = $this->_query_insert = $this->_query_duplicate_key = '';
    }
}
