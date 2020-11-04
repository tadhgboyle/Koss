<?php

/**
 * 
 * Koss - Write MySQL queries faster than ever before in PHP
 * Inspired by Laravel Eloquent
 * 
 * @author Tadhg Boyle
 * @since October 2020
 */
interface IKossQuery
{

    /**
     * Create new instance of a KossQuery by injecting the beginning statement
     */
    public function __construct(PDO $pdo, string $query);

    /**
     * Execute Koss function under certain conditions
     */
    public function when($expression, callable $callback, callable $fallback = null): IKossQuery;

    /**
     * Execute repsective query and store result
     */
    public function execute();

    /**
     * Assemble queries into MySQL statement
     */
    public function build(): string;

    /**
     * Reset query strings
     */
    public function reset(): void;

    /**
     * Debugging only: Output the built string of all queries so far
     */
    public function toString(): string;

    /**
     * Debugging only: Output the built string of all queries so far
     */
    public function __toString(): string;
}
