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

    public function __construct(PDO $pdo, string $query)
    {
        $this->_pdo = $pdo;
        $this->_query_insert = $query;
    }

    public static function insert(PDO $pdo, string $table, array $row): KossUpdateQuery
    {
        $columns = implode(', ', array_map(fn($string) => '`' . $string . '`', array_keys($row)));
        $values = implode(', ', array_map(fn($string) => '\'' . $string . '\'', array_values($row)));
        return new self($pdo, "INSERT INTO `$table` ($columns) VALUES ($values)");
    }

    public static function update(PDO $pdo, string $table, array $values): KossUpdateQuery
    {
        $values_compiled = '';
        foreach ($values as $column => $value) {
            $values_compiled .= "`$column` = '$value', ";
        }
        $values_compiled = substr($values_compiled, 0, -2);
        return new self($pdo, "UPDATE $table SET $values_compiled");
    }

    public function where(string $column, string $operator, string $matches = null): KossUpdateQuery
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