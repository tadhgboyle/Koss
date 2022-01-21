<?php

namespace Aberdeener\Koss\Queries\Traits;

use Closure;

trait Conditionable
{
    /**
     * Run a Koss function only when the specified $expression is true.
     *
     * @param Closure|bool $expression Function or boolean value to eval.
     * @param Closure $callback Function to run when $expression is true.
     *
     * @return static This instance of Query.
     */
    final public function when(Closure|bool $expression, Closure $callback): static
    {
        if ($this->isTrue($expression)) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Run a Koss function only when the specified $expression is false.
     *
     * @param Closure|bool $expression Function or boolean value to eval.
     * @param Closure $callback Function to run when $expression is false.
     *
     * @return static This instance of Query.
     */
    final public function unless(Closure|bool $expression, Closure $callback): static
    {
        if ($this->isFalse($expression)) {
            $callback($this);
        }

        return $this;
    }

    private function isTrue(Closure|bool $expression): bool
    {
        return is_callable($expression) ? $expression() : $expression;
    }

    private function isFalse(Closure|bool $expression): bool
    {
        return !$this->isTrue($expression);
    }
}
