<?php

namespace Aberdeener\Koss\Queries\Traits;

use Aberdeener\Koss\Util\Util;

trait HasDuplicateKeys
{
    protected string $duplicateKey = '';

    /**
     * Key/Value array of column/value to insert if a duplicate key is found during this update query.
     *
     * @param array $values Key => Value array of row to update if duplicate key is found.
     *
     * @return static Query class which called it.
     */
    final public function onDuplicateKey(array $values): static
    {
        $compiled_values = '';

        foreach ($values as $column => $value) {
            $compiled_values .= Util::escapeStrings($column) . ' = ' . Util::escapeStrings($value, "'") . ' ';
        }

        $this->duplicateKey = " ON DUPLICATE KEY UPDATE {$compiled_values}";

        return $this;
    }
}