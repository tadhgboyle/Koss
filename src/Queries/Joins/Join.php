<?php

namespace Aberdeener\Koss\Queries\Joins;

use ReflectionClass;
use ReflectionProperty;
use Aberdeener\Koss\Util\Util;
use Aberdeener\Koss\Queries\SelectQuery;
use Aberdeener\Koss\Exceptions\JoinException;

class Join
{
    protected string $table;
    protected string $through;
    protected string $foreignId;
    protected string $localId;

    protected static ReflectionClass $selectQueryClass;
    protected static ReflectionProperty $tableProperty;
    protected static ReflectionProperty $joinsProperty;

    public function __construct(
        protected string $keyword,
        protected SelectQuery $queryInstance
    ) {
        if (!in_array($keyword, ['INNER', 'OUTER', 'LEFT OUTER', 'RIGHT OUTER'])) {
            throw new JoinException("Invalid JOIN clause keyword. Keyword: $keyword");
        }

        self::$selectQueryClass = new ReflectionClass(SelectQuery::class);
        self::$tableProperty = self::$selectQueryClass->getProperty('table');
        self::$joinsProperty = self::$selectQueryClass->getProperty('joins');
    }

    /**
     * Set table to preform this JOIN clause on.
     *
     * @param string $table Name of table to use.
     *
     * @return Join This instance of join class.
     */
    public function table(string $table): Join
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Set table to use for finding matches.
     * If not set, will use table in parent SelectQuery instance.
     *
     * @param string $through Name of table to use for lookup.
     *
     * @return Join This instance of join class.
     */
    public function through(string $through): Join
    {
        $this->through = $through;

        return $this;
    }

    /**
     * Set which columns to preform the ON operation on.
     *
     * @param string $foreignId Name of column in $table to use for lookup.
     * @param string|null $localId Name of column to use in this table for lookup. If not provided, will use same value as $foreign_id.
     */
    public function on(string $foreignId, ?string $localId = null): void
    {
        if (!isset($this->table)) {
            throw new JoinException('$table must be set before running on() function.');
        }

        $this->foreignId = $foreignId;
        $this->localId = $localId ?? $foreignId;

        self::$joinsProperty->setAccessible(true);

        $joins_array = self::$joinsProperty->getValue($this->queryInstance);
        $joins_array[] = $this->build();

        self::$joinsProperty->setValue($this->queryInstance, $joins_array);
        self::$joinsProperty->setAccessible(false);
    }

    /**
     * Create query for this JOIN clause.
     *
     * @return string Built query.
     */
    private function build(): string
    {
        $through = isset($this->through)
                        ? $this->through
                        : $this->getQueryTableValue();

        return $this->keyword . ' JOIN ' . Util::escapeStrings($this->table) . ' ON ' . Util::escapeStrings($this->table) . '.' . Util::escapeStrings($this->foreignId) . ' = ' . Util::escapeStrings($through) . '.' . Util::escapeStrings($this->localId);
    }

    /**
     * Get the value of $table in the child Query class.
     * 
     * @return string The $table value.
     */
    private function getQueryTableValue(): string
    {
        self::$tableProperty->setAccessible(true);

        $table = self::$tableProperty->getValue($this->queryInstance);

        self::$tableProperty->setAccessible(false);

        return $table;
    }

    /**
     * Print query for this JOIN clause.
     * Forwards request to `build()` function.
     *
     * @codeCoverageIgnore
     *
     * @return string Built query.
     */
    public function __toString(): string
    {
        return $this->build();
    }
}
