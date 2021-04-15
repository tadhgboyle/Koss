<?php

/**
 * 
 * Koss - Write MySQL queries faster than ever before in PHP
 * Inspired by Laravel Eloquent
 * 
 * @author Tadhg Boyle
 * @since October 2020
 */
class KossUpdateQuery implements IKossQuery
{
    
    protected PDO $_pdo;

    protected PDOStatement $_query;

    protected string
        $_query_insert = '',
        $_query_where = '',
        $_query_duplicate_key = '',
        $_query_built = '';

    protected array $_where = array();
    
    /**
     * Create new instance of KossUpdateQuery. Should only be used internally by Koss.
     *
     * @param PDO $pdo PDO connection to be used.
     * @param string $query 
     */
    public function __construct(PDO $pdo, string $query)
    {
        $this->_pdo = $pdo;
        $this->_query_insert = $query;
    }

    /**
     * Insert new row into a table.
     * 
     * @param PDO $pdo PDO instance to be used.
     * @param string $table Table to update.
     * @param array $row Column name/Value pairs to insert into table.
     * @return KossUpdateQuery New instance of UpdateQuery class.
     */
    public static function insert(PDO $pdo, string $table, array $row): KossUpdateQuery
    {
        $columns = implode(', ', Koss::escapeStrings(array_keys($row)));
        $values = implode(', ', Koss::escapeStrings(array_values($row), '\''));

        return new KossUpdateQuery($pdo, "INSERT INTO `$table` ($columns) VALUES ($values)");
    }

    public static function update(PDO $pdo, string $table, array $values): KossUpdateQuery
    {
        $values_compiled = '';

        foreach ($values as $column => $value) {
            $values_compiled .= "`$column` = '$value', ";
        }

        $values_compiled = rtrim($values_compiled, ',');

        return new KossUpdateQuery($pdo, "UPDATE `$table` SET $values_compiled");
    }

    public function where(string $column, string $operator, string $matches = null): KossUpdateQuery
    {
        $append = Koss::handleWhereOperation($column, $operator, $matches);

        if ($append != null) {
            $this->_where[] = $append;
        }
        
        return $this;
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
        Koss::when($this, $expression, $callback, $fallback);

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
        $this->_query_built = $this->_query_insert . ' ' . $this->_query_duplicate_key . ' ' . Koss::assembleWhereClause($this->_where);
        
        return $this->_query_built;
    }
    
    public function reset(): void
    {
        $this->_where = array();
        $this->_query_built = $this->_query_insert = $this->_query_duplicate_key = '';
    }

    public function toString(): string
    {
        return $this->build();
    }

    public function __toString(): string
    {
        return $this->build();
    }
}