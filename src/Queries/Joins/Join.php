<?php

namespace Aberdeener\Koss\Queries\Joins;

use ReflectionClass;
use ReflectionProperty;
use Aberdeener\Koss\Util\Util;
use Aberdeener\Koss\Queries\SelectQuery;
use Aberdeener\Koss\Exceptions\JoinException;

class Join
{
    protected SelectQuery $_query_instance;

    protected string $_keyword;
    protected string $_table;
    protected string $_through;
    protected string $_foreign_id;
    protected string $_local_id;
    protected string $_join_built;

    protected static ReflectionClass $select_query_class;
    protected static ReflectionProperty $table_property;
    protected static ReflectionProperty $joins_property;

    public function __construct(string $keyword, SelectQuery $query_instance)
    {
        if (!in_array($keyword, ['INNER', 'OUTER', 'LEFT OUTER', 'RIGHT OUTER'])) {
            throw new JoinException("Invalid JOIN clause keyword. Keyword: $keyword");
        }

        $this->_keyword = $keyword;
        $this->_query_instance = $query_instance;

        self::$select_query_class = new ReflectionClass(SelectQuery::class);
        self::$table_property = self::$select_query_class->getProperty('_table');
        self::$joins_property = self::$select_query_class->getProperty('_joins');
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
        $this->_table = $table;

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
        $this->_through = $through;

        return $this;
    }

    /**
     * Set which columns to preform the ON operation on.
     *
     * @param string $foreign_id Name of column in $_table to use for lookup.
     * @param string|null $local_id Name of column to use in this table for lookup. If not provided, will use same value as $foreign_id.
     */
    public function on(string $foreign_id, ?string $local_id = null): void
    {
        if (!isset($this->_table)) {
            throw new JoinException('$_table must be set before running on() function.');
        }

        $this->_foreign_id = $foreign_id;
        $this->_local_id = $local_id ?? $foreign_id;

        self::$joins_property->setAccessible(true);

        $joins_array = self::$joins_property->getValue($this->_query_instance);
        $joins_array[] = $this->build();

        self::$joins_property->setValue($this->_query_instance, $joins_array);
        self::$joins_property->setAccessible(false);
    }

    /**
     * Create query for this JOIN clause.
     *
     * @return string Built query.
     */
    private function build(): string
    {
        if (!isset($this->_through)) {
            self::$table_property->setAccessible(true);

            $through = self::$table_property->getValue($this->_query_instance);

            self::$table_property->setAccessible(false);
        } else {
            $through = $this->_through;
        }

        return $this->_keyword . ' JOIN ' . Util::escapeStrings($this->_table) . ' ON ' . Util::escapeStrings($this->_table) . '.' . Util::escapeStrings($this->_foreign_id) . ' = ' . Util::escapeStrings($through) . '.' . Util::escapeStrings($this->_local_id);
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
