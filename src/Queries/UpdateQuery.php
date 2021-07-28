<?php

namespace Aberdeener\Koss\Queries;

use Aberdeener\Koss\Queries\Traits\HasDuplicateKeys;
use Aberdeener\Koss\Queries\Traits\HasWhereClauses;
use PDO;
use PDOStatement;
use Aberdeener\Koss\Util\Util;

final class UpdateQuery extends Query
{
    use HasDuplicateKeys;
    use HasWhereClauses;

    protected PDOStatement $pdoStatement;
    protected int $result;

    protected string $queryUpdate;
    protected string $queryDuplicateKey = '';
    protected string $queryBuilt = '';

    /** @var array[] */
    protected array $values = [];

    /**
     * Create new instance of UpdateQuery. Should only be used internally by Koss.
     *
     * @param PDO $pdo PDO connection to be used.
     */
    public function __construct(
        protected PDO $pdo,
        protected ?string $table = null,
        protected ?string $rawQuery = null,
    ) {}

    public function update(array $values): UpdateQuery
    {
        $this->handleFirst();
        $this->values[] = $values;

        return $this;
    }

    private function handleFirst(): bool
    {
        if ($first = !isset($this->queryUpdate)) {
            $table = Util::escapeStrings($this->table);
            $this->queryUpdate = "UPDATE {$table} SET ";
        }

        return $first;
    }

    public function execute(): int
    {
        if (!($this->pdoStatement = $this->pdo->prepare($this->build()))) {
            // @codeCoverageIgnoreStart
            return -1;
            // @codeCoverageIgnoreEnd
        }

        if (!$this->pdoStatement->execute()) {
            // @codeCoverageIgnoreStart
            die(print_r($this->pdo->errorInfo()));
            // @codeCoverageIgnoreEnd
        }

        $this->result = $this->pdoStatement->rowCount();
        $this->reset();

        return $this->result;
    }

    public function build(): string
    {
        $this->queryBuilt = $this->cleanString(
            $this->rawQuery
                ?? $this->queryUpdate . ' ' . $this->compileValues() . ' ' . $this->queryDuplicateKey . ' ' . Util::assembleWhereClause($this->whereClauses)
        );

        return $this->queryBuilt;
    }

    private function compileValues(): string
    {
        $values_compiled = '';

        foreach ($this->values as $values) {
            foreach ($values as $column => $value) {
                $values_compiled .= Util::escapeStrings($column) . ' = ' . Util::escapeStrings($value, "'") . ', ';
            }
        }

        return rtrim($values_compiled, ', ');
    }

    public function reset(): void
    {
        $this->whereClauses = [];
        $this->queryBuilt = $this->query_insert = $this->queryDuplicateKey = '';
    }
}
