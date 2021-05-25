<?php

namespace Aberdeener\Koss\Queries;

use ReflectionClass;
use ReflectionProperty;

class Having
{
    protected SelectQuery $_query_instance;

    protected static ReflectionClass $select_query_class;
    protected static ReflectionProperty $havings_property;

    public function __construct(SelectQuery $query)
    {
        $this->_query_instance = $query;

        self::$select_query_class = new ReflectionClass(SelectQuery::class);
        self::$havings_property = self::$select_query_class->getProperty('_havings');
    }

    public function count(string $column): string
    {
        return "COUNT($column)";
    }

    public function sum(string $column): string
    {
        return "SUM($column)";
    }

    public function average(string $column): string
    {
        return "AVG($column)";
    }

    public function lessThan(string $column, mixed $value): void
    {
        $this->submitHaving($column . ' < ' . $value);
    }

    public function orLessThan(string $column, mixed $value): void
    {
        $this->submitHaving($column . ' < ' . $value, 'OR');
    }

    public function greaterThan(string $column, mixed $value): void
    {
        $this->submitHaving($column . ' > ' . $value);
    }

    public function orGreaterThan(string $column, mixed $value): void
    {
        $this->submitHaving($column . ' > ' . $value, 'OR');
    }

    public function equalTo(string $column, mixed $value): void
    {
        $this->submitHaving($column . ' = ' . $value);
    }

    public function orEqualTo(string $column, mixed $value): void
    {
        $this->submitHaving($column . ' = ' . $value, 'OR');
    }

    public function notEqualTo(string $column, mixed $value): void
    {
        $this->submitHaving($column . ' <> ' . $value);
    }

    public function orNotEqualTo(string $column, mixed $value): void
    {
        $this->submitHaving($column . ' <> ' . $value, 'OR');
    }

    private function submitHaving(string $having, string $glue = 'AND'): void
    {
        self::$havings_property->setAccessible(true);

        $havings = self::$havings_property->getValue($this->_query_instance);
        $havings[] = [
            'glue' => $glue,
            'having' => $having,
        ];

        self::$havings_property->setValue($this->_query_instance, $havings);
        self::$havings_property->setAccessible(false);
    }
}
