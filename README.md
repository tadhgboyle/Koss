![alt](https://i.imgur.com/4FN4HlE.png)

## Roadmap
  - Add as a Composer project for easier usage
  - Add function to select specific columns after the initial `get()` function, for use in `when()` or similar
  - Depending how advanced KossUpdateQuery gets, make KossInsertQuery to help seperate
  - Allow `where()` to take (nested) arrays

## Documentation

### Setup:
  - `require 'src/Koss.php'` somewhere in your PHP script
  - Initiate a new Koss instance by passing your MySQL login and database information.
  - Parameters:
    - 1: Hostname
    - 2: Port
    - 3: Database name
    - 4: Username
    - 5: Password

### Core Functions:
Functions which are available in both Selection and Update/Insert queries.
  - `execute()`
    - Execute compiled Koss MySQL code and output results
    - *Note: Without calling this function at the end of your code, nothing will output!*`
  - `when(callable|bool $expression, callable $callback, callable $fallback)`
    - Only execute `$callback` function when `$expression` is true. If `$fallback` is provided, it will be called when `$expression` is false.
    - *Note: `$expression` can be either a boolean value (`5 < 10`) or an anonymous function which returns a boolean value*
    - *Note: `$callback` and `$fallback` must `use ($koss)` rather than passing it as a normal parameter, or be a shorthand `fn()`*
    - *Note: Only some Koss functions are supported in `when()` statements. Functions: `limit()`, `orderBy()`, `where()`, `groupBy()`, `like()`*
  - `where(string $column, string $operator, string $matches)`
    - Select rows in `$table` (must be previously provided via a select statement) with values in `$column` that are `$operator` to `$match`
    - *Note: If `$operator` is not provided, `'='` will be assumed*
    - Example SQL code: `WHERE username <> 'Aberdeener'`
  - `like(string $column, string $like)`
    - Select rows in `$table` (must be previously provided via a select statement) with values in `$column` that are similar to to `%$like%`
    - *Note: Multiple `like` and `where` clauses can be passed and Koss will handle compiling the correct MySQL code*
    - Example SQL code: `WHERE first_name LIKE %Tadhg%`

### Selection Functions:
  - `getAll(string $table)`
    - Select all columns in `$table`
    - Example SQL code: `SELECT * FROM users`
  - `getSome(string $table, array $columns)`
    - Select specific `$columns` in `$table`
    - Example SQL code: `SELECT username, first_name, last_name FROM users`
  - `groupBy(string $column)`
    - Group together rows with same `$column` values
    - Example SQL code: `GROUP BY age`
  - `orderBy(string $column, string $order)`
    - Sort output by `$column` either `ASC` or `DESC`
    - Example SQL code: `ORDER BY first_name DESC`
  - `limit(int $limit)`
    - Only return `$limit` rows.
    - Example SQL code: `LIMIT 3`

### Update/Insert Functions:
  - `insert(string $table, array $row)`
    - Inserts a new row into `$table`.
    - `$row` must be an array in the format:
      ```php
      // Column name => Value
      $row = array(
        'username' => 'Aberdeener',
        'first_name' => 'Tadhg',
        'last_name' => 'Boyle'
      );
      ```
    - Example SQL code: `INSERT INTO users (username, first_name, last_name) VALUES ('Aberdeener', 'Tadhg', 'Boyle')`
  - `onDuplicateKey(array $values)`
    - Upon an insertion, if there is a unique column and it is overridden, run this code instead.
    - `$values` must be an array in the format:
      ```php
      // Column name => New value
      $values = array(
        'username' => 'Aber'
      );
      ```
    - Example SQL code: `ON DUPLICATE KEY UPDATE username = 'Aber'`

### Other Functions:
Functions which are not in Selection or Update/Insert queries
  - `execute(string $query)`
    - Execute provided `$query` and output results.
    - Common usage would be raw queries where Koss does not have functionality to help.
    - *Note: Cannot be mixed with other functions*

### Examples:
*All assuming you have autoloaded `Koss.php` and created a new instance of it with your database credentials.*

  - Selecting information
    ```php
    // Get the "username" and "first_name" column in the "users" table, limit to only the first 5 rows, and sort by their username descending.
    $results = $koss->getSome('users', ['username', 'first_name'])->limit(5)->orderBy('username', 'DESC')->execute();
    // MySQL Output: SELECT `username`, `first_name` FROM `users` ORDER BY `username` DESC LIMIT 5

    // Get all columns in the "users" table, and when they're logged in, limit to only the first 5 rows.
    $results = $koss->getAll('users')->when(fn() => isset($_SESSION['logged_in']), fn() => $koss->limit(5))->execute();
    // MySQL Output: SELECT * FROM `users` LIMIT 5
    ```

  - Inserting information
    ```php
    // TODO
    ```

  - Updating information
    ```php
    // TODO
    ```