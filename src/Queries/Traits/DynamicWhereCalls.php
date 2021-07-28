<?php

namespace Aberdeener\Koss\Queries\Traits;

use Aberdeener\Koss\Exceptions\DynamicWhereCallException;

trait DynamicWhereCalls
{
    // TODO: Tests
    public function __call(string $name, array $arguments): static
    {
        $column = $this->parseColumnName($name);

        if (!$column) {
            throw new DynamicWhereCallException('Invalid call made. Dynamic where calls must start with \'where\'.');
        }

        if (!isset($arguments[0])) {
            throw new DynamicWhereCallException('No string provided to match with.');
        }

        $matches = $arguments[0];

        return $this->where($column, '=', $matches);
    }

    private function parseColumnName(string $string): string
    {
        return strtolower(substr($string, strpos($string, 'where') + 5));
    }
}