# Koss

Write MySQL queries in PHP faster and easier than ever before.

### Documentation

Setup:
    - `require 'Koss.php'` somewhere in your PHP script
    - Initiate a new Koss instance by passing your MySQL login and database information.
    - Parameters:
        - 1: Hostname
        - 2: Port
        - 3: Database name
        - 4: Username
        - 5: Password

Functions:
    - `getAll(string $table)`
        - Select all columns in `$table`
        - Example SQL code: `SELECT * FROM users`
    - `getSome(string $table, string ...$columns)`
        - Select specific `$columns` in `$table`
        - Example SQL code: `SELECT username, first_name, last_name FROM users`
    - `where(string $column, string $operator, string $matches)`
        - Select rows in `$table` (must be previously provided via a select statement) with values in `$column` that are `$operator` to `$match`
        - *Note: If `$operator` is not provided, `'='` will be assumed*
        - Example SQL code: `WHERE username <> 'Aberdeener'`
    - `like(string $column, string $like)`
        - Select rows in `$table` (must be previously provided via a select statement) with values in `$column` that are similar to to `%$like%`
        - *Note: Multiple `like` and `where` clauses can be passed and Koss will handle compiling the correct MySQL code*
        - Example SQL code: `WHERE first_name LIKE %Tadhg%`
    - `orderBy(string $column, string $order)`
        - Sort output by `$column` either `ASC` or `DESC`
        - *Note: if `$order` is not provided, `DESC` is assumed*
        - Example SQL code: `ORDER BY first_name DESC`
    - `limit(int $limit)`
        - Only return `$limit` rows.
        - Example SQL code: `LIMIT 3`
    - `when(callable|bool $expression, callable $callback, callable $fallback)`
        - Only execute `$callback` function when `$expression` is true. If `$fallback` is provided, it will be called when `$expression` is false.
        - *Note: `$expression` can be either a boolean value (`5 < 10`) or an anonymous function which returns a boolean value*
        - *Note: `$callback` and `$fallback` must `use ($koss)` rather than passing it as a normal parameter*
    - `execute()`
        - Execute compiled Koss MySQL code and output results
        - *Note: Without calling this function at the end of your code, nothing will output!*
    - `execute(string $query)`
        - Execute provided `$query` and output results.
        - Common usage would be raw queries where Koss does not have functionality to help.
        - *Note: Cannot be mixed with other functions*
        - 