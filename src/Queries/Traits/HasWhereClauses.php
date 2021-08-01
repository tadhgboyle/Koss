<?php

namespace Aberdeener\Koss\Queries\Traits;

use Aberdeener\Koss\Exceptions\StatementException;

trait HasWhereClauses
{
    use DynamicWhereCalls;

    /** @var string[] */
    protected array $whereClauses = [];

    /**
     * Preform an AND WHERE operation in this query.
     *
     * @param string|array $column Name of column to use. If an array is given, will attempt to extract deeper clauses from it using same order of args.
     * @param string|null $operator Operator to use for comparison.
     * @param string|null $matches Value to compare to. If not provided, $operator will be used and `=` will be assumed as operator.
     *
     * @return static This instance of Query.
     */
    final public function where(string | array $column, ?string $operator = null, ?string $matches = null): static
    {
        $this->handleWhereOperation(func_get_args());

        return $this;
    }

    /**
     * Preform an OR WHERE operation in this query.
     *
     * @param string|array $column Name of column to use. If an array is given, will attempt to extract deeper clauses from it using same order of args.
     * @param string|null $operator Operator to use for comparison.
     * @param string|null $matches Value to compare to. If not provided, $operator will be used and `=` will be assumed as operator.
     *
     * @return static This instance of Query.
     */
    final public function orWhere(string | array $column, ?string $operator = null, ?string $matches = null): static
    {
        $this->handleWhereOperation(func_get_args());

        return $this;
    }

    /**
     * Add an AND LIKE statement to this query.
     * Rereoutes to `where()` and uses `"LIKE"` as the operator.
     *
     * @param string $column Column name to search in.
     * @param string $like Value to attempt to find. Must provide `"%"` as needed.
     *
     * @return static This instance of Query.
     */
    final public function like(string $column, string $like): static
    {
        return $this->where([$column, 'LIKE', $like]);
    }

    /**
     * Add an OR LIKE statement to this query.
     * Rereoutes to `orWhere()` and uses `"LIKE"` as the operator.
     *
     * @param string $column Column name to search in.
     * @param string $like Value to attempt to find. Must provide `"%"` as needed.
     *
     * @return static This instance of Query.
     */
    final public function orLike(string $column, string $like): static
    {
        return $this->orWhere([$column, 'LIKE', $like]);
    }

    /**
     * Recursively traverse any arguments provided to assemble where clauses from them.
     * Supports arrays, nested arrays and normal string args to be passed.
     */
    private function handleWhereOperation(): void
    {
        $args = func_get_args();

        foreach ($args as $arg) {

            // If this arg is not an array, can ignore it - they should read the docs!
            if (!is_array($arg)) {
                continue;
            }

            $array = $arg;

            // If this array contains more arrays, unpack them and call this function again.
            if ($this->isMultiDimensionalArray($array)) {
                $this->handleWhereOperation(...$array);
                continue;
            }

            // If this array has all 4 required entries, save the clause and continue.
            if (count($array) == 4) {
                $this->addWhereClause(...$array);
                continue;
            }

            // If there are 3 entries in this array we need to see if the glue is provided, or the operator
            // and then make assumptions based on that.
            if (count($array) == 3) {

                // If the second element is an operator we will use that, otherwise we assume "="
                $operator = $this->isValidOperator($array[1])
                    ? $array[1]
                    : '=';

                // If the second element is an operator, we assume that the matches value is the third,
                // otherwise we assume it is the second element.
                $matches = $this->isValidOperator($array[1])
                    ? $array[2]
                    : $array[1];

                // If the second element is an operator, we must guess the $glue from
                // previously called functions.
                $glue = $this->isValidOperator($array[1])
                    ? $this->getGlueFromBacktrace()
                    : $array[2];

                $this->addWhereClause(
                    $array[0],
                    $operator,
                    $matches,
                    $glue
                );

                continue;
            }

            // If there are only 2 entries in the array, we assume they excluded the operator,
            // so save with "=" as the operator and guessed $glue.
            if (count($array) == 2) {

                // Due to the dynamic nature of this system, we cannot pass $glue so we need to guess it
                // based on previously called functions.
                $glue = $this->getGlueFromBacktrace();

                $this->addWhereClause(
                    $array[0],
                    '=',
                    $array[1],
                    $glue
                );

                continue;
            }

            throw new StatementException('Array argument provided in where function must be at least 2 in length.');
        }
    }

    /**
     * Create and store a where clause in an array from the given parameters.
     *
     * @param string $column Column name to use in clause.
     * @param string $operator Operator to use in clause to find matches.
     * @param string $matches Value to use to compare.
     * @param string $glue Whether to use `"AND"` or `"OR"` to chain this clause together with the last.
     */
    private function addWhereClause(string $column, string $operator, string $matches, string $glue): void
    {
        if (!$this->isValidOperator($operator)) {
            throw new StatementException("Invalid WHERE clause operator. Operator: {$operator}.");
        }

        if (!in_array($glue, ['AND', 'OR'])) {
            throw new StatementException("Invalid WHERE clause glue. Glue: {$glue}.");
        }

        $this->whereClauses[] = [
            'glue' => $glue,
            'column' => $column,
            'operator' => $operator,
            'matches' => $matches,
        ];
    }

    /**
     * Determine if provided array is multidimensional (contains more arrays), or not.
     *
     * @param array $array Array to check.
     *
     * @return bool Whether $array is multidim or not.
     */
    private function isMultiDimensionalArray(array $array): bool
    {
        return count($array) != count($array, COUNT_RECURSIVE);
    }

    /**
     * Determine if provided string is a valid MySQL comparision operator.
     *
     * @param string $operator String to check.
     *
     * @return bool If it is valid or not.
     */
    private function isValidOperator(string $operator): bool
    {
        return in_array($operator, ['=', '<>', '>', '<', '>=', '<=', 'LIKE']);
    }

    /**
     * Loop debug backtrace to see what where clause function was called to assume the $glue to provide to `addWhereClause()`.
     *
     * @return string `"AND"` if `where()` or `like()` was called. `"OR"` if `orWhere()` or `orLike()` was called.
     */
    private function getGlueFromBacktrace(): string
    {
        $backtrace = debug_backtrace();

        foreach ($backtrace as $frame) {
            if (in_array($frame['function'], ['handleWhereOperation', 'getGlueFromBacktrace'])) {
                continue;
            }

            $function = $frame['function'];
            break;
        }

        return match ($function) {
            'where', 'like' => 'AND',
            'orWhere', 'orLike' => 'OR',
        };
    }
}
