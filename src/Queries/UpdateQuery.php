<?php

namespace Aberdeener\Koss\Queries;

use PDO;
use PDOException;
use PDOStatement;
use Aberdeener\Koss\Util\Util;

class UpdateQuery extends Query
{
    protected PDO $_pdo;
    protected PDOStatement $_query;

    protected string $_query_insert = '';
    protected string $_query_where = '';
    protected string $_query_duplicate_key = '';
    protected string $_query_built = '';
    protected array $_where = [];

    /**
     * Create new instance of UpdateQuery. Should only be used internally by Koss.
     *
     * @param PDO $pdo PDO connection to be used.
     * @param string $query
     */
    public function __construct(PDO $pdo, string $query)
    {
        $this->_pdo = $pdo;
        $this->_query_insert = $query;
    }

    public function where(string $column, string $operator, ?string $matches = null): UpdateQuery
    {
        $append = Util::handleWhereOperation($column, $operator, $matches);

        if ($append != null) {
            $this->_where[] = $append;
        }

        return $this;
    }

    public function onDuplicateKey(array $values): UpdateQuery
    {
        $compiled_values = '';

        foreach ($values as $column => $value) {
            $compiled_values .= "`$column` = '$value' ";
        }

        $this->_query_duplicate_key = "ON DUPLICATE KEY UPDATE $compiled_values";

        return $this;
    }

    public function when(callable | bool $expression, callable $callback, ?callable $fallback = null): UpdateQuery
    {
        Util::when($this, $expression, $callback, $fallback);

        return $this;
    }

    public function execute(): int
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
            }

            die(print_r($this->_pdo->errorInfo()));
        }

        return -1;
    }

    public function build(): string
    {
        $this->_query_built = $this->_query_insert . ' ' . $this->_query_duplicate_key . ' ' . Util::assembleWhereClause($this->_where);

        return $this->_query_built;
    }

    public function reset(): void
    {
        $this->_where = [];
        $this->_query_built = $this->_query_insert = $this->_query_duplicate_key = '';
    }
}
