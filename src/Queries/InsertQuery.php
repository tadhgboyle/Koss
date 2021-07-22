<?php

namespace Aberdeener\Koss\Queries;

use PDO;
use Aberdeener\Koss\Util\Util;
use Aberdeener\Koss\Queries\Query;
use Aberdeener\Koss\Queries\Traits\HasDuplicateKeys;
use PDOStatement;

class InsertQuery extends Query
{
    use HasDuplicateKeys;

    protected PDOStatement $query;
    protected int $result;

    protected string $insertQuery = '';
    protected array $insertData = [];
    protected string $insertDuplicateKey = '';

    public function __construct(
        protected PDO $pdo,
        protected ?string $table = null,
        protected ?string $rawQuery = null,
    ) {}

    public function insert(array $columns, array $values): InsertQuery
    {
        $this->handleFirst();

        foreach ($columns as $column) {
            $this->insertData['columns'][] = $column;
        }

        foreach ($values as $value) {
            $this->insertData['values'][] = $value;
        }

        return $this;
    }

    private function handleFirst(): bool
    {
        if ($first = empty($this->insertQuery)) {
            $this->insertQuery = "INSERT INTO `{$this->table}`";
        }

        return $first;
    }

    public function execute(): int
    {
        if (!($this->query = $this->pdo->prepare($this->build()))) {
            // @codeCoverageIgnoreStart
            return -1;
            // @codeCoverageIgnoreEnd
        }

        if (!$this->query->execute()) {
            // @codeCoverageIgnoreStart
            die(print_r($this->pdo->errorInfo()));
            // @codeCoverageIgnoreEnd
        }

        $this->result = $this->query->rowCount();
        $this->reset();

        return $this->result;
    }

    public function build(): string
    {
        return $this->cleanString(
            isset($this->rawQuery)
                ? $this->rawQuery
                : $this->insertQuery . $this->compileValues() . $this->duplicateKey
        );
    }

    private function compileValues(): string
    {
        $columns = '';
        $values = '';

        $entryNumber = 0;

        foreach ($this->insertData['columns'] as $column) {
            $value = $this->insertData['values'][$entryNumber];

            $columns .= Util::escapeStrings($column) . ', ';
            $values .= Util::escapeStrings($value, "'") . ', ';

            $entryNumber++;
        }

        $columns = rtrim($columns, ', ');
        $values = rtrim($values, ', ');

        return " ({$columns}) VALUES ({$values})";
    }

    public function reset(): void
    {
        $this->insertQuery = '';
        $this->insertData = [];
        $this->insertDuplicateKey = '';
    }
}