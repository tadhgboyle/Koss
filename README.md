![Koss Header Image](https://i.imgur.com/4FN4HlE.png)

## Roadmap
  - Move `where()`, `orWhere()`, `like()`, etc to each Query subclass and use differently named functions in Query class so their return types are stricter.
  - Tests!
  - Make chained join clauses more fluent, after `on()` add a `then($callback)` (instead of current behaviour of multiple seperate calls and usage of `through($table)`) ?
  - Allow `where()` to take arrays and nested arrays
  - Add `prefix(string $prefix)` function to set a table prefix to automatically append.
  - Depending how advanced UpdateQuery gets, make InsertQuery to help seperate the internal code

## Documentation

- Requires PHP 8.0

### Setup:
  - Autoload Koss using Composer.
  - Initiate a new Koss instance by passing your MySQL login and database information.
  - Parameters:
    - 1: Hostname
    - 2: Port
    - 3: Database name
    - 4: Username
    - 5: Password

*Note: Your order of functions does not matter. You can `limit()` and then choose to select more columns via `columns()` after.*

### Core Functions:
Functions which are available in both Selection and Update/Insert queries.
  - `execute()`
    - Execute compiled Koss MySQL code and output results
    - *Note: Without calling this function at the end of your code, nothing will output!*`
  - `when(Closure | bool $expression, Closure $callback, ?Closure $fallback = null)`
    - Only execute `$callback` function when `$expression` is true. If `$fallback` is provided, it will be called when `$expression` is false.
    - *Note: `$expression` can be either a boolean value (`5 < 10`) or an anonymous function which returns a boolean value*
  - `where(string $column, string $operator, string $matches)`
    - Select rows in `$table` (must be previously provided via a select statement) with values in `$column` that are `$operator` to `$match`
    - *Note: If `$operator` is not provided, `'='` will be assumed*
    - Example SQL code: `WHERE username <> 'Aberdeener'`
  - `like(string $column, string $like)`
    - Select rows in `$table` (must be previously provided via a select statement) with values in `$column` that are similar to to `$like`.
    - You must provide the `%` where you want them to be, Koss cannot assume anything.
    - *Note: Multiple `like` and `where` clauses can be passed and Koss will handle compiling the correct MySQL code*
    - Example SQL code: `WHERE first_name LIKE %Tadhg%`

### Selection Functions:
  - `getAll(string $table)`
    - Select all columns in `$table`
    - Example SQL code: `SELECT * FROM users`
  - `getSome(string $table, array | string $columns)`
    - Select specific `$columns` (or just one column if a string is provided) in `$table`
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
  - `columns(array $columns)`
    - Also select `$columns` as well as whatever was passed in the original `getSome()`
    - `column(string $column)` allows for selecting a single column.
    - Example SQL code: `SELECT username, first_name`
  - `cast(string $column, string $type)`
    - Cast a specific `$column`'s data to `$type` when it is retreived from the database.
  - `casts(array $casts)`
    - Cast multiple columns at the same time in SelectQuery.
    - `$casts` must be an array in the format:
        ```php
        // Column name => Type
        $casts = array(
          'id' => 'int',
          'username' => 'string',
          'money' => 'float'
        );
        ```

### Update/Insert Functions:
  - `update(string $table, array $values)`
    - Updates any rows in the `$table` to new `$values`.
    - `$values` must be an array in the format:
      ```php
      // Column name => Value
      $values = array(
        'username' => 'Aber'
      );
      ```
      - Example SQL code: `UPDATE users SET username = 'Aberdeener' WHERE ...`
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

## Examples

*All assuming you have autoloaded `Koss.php` and created a new instance of it with your database credentials.*

  - Selecting information
    ```php
    // Get the "username" and "first_name" column in the "users" table, limit to only the first 5 rows, and sort by their username descending.
    $results = $koss->getSome('users', ['username', 'first_name'])->limit(5)->orderBy('username', 'DESC')->execute();
    // MySQL Output: SELECT `username`, `first_name` FROM `users` ORDER BY `username` DESC LIMIT 5
 
    // Get all columns in the "users" table, and when they're logged in, limit to only the first 5 rows.
    // Note the usage of new variable, $query in anonymous function. This will be passed by Koss.
    $results = $koss->getAll('users')->when(fn() => isset($_SESSION['logged_in']), fn(SelectQuery $query) => $query->limit(5))->execute();
    // MySQL Output: SELECT * FROM `users` LIMIT 5

    // Get the "username" column in the "users" table, but also select the "last_name" column.
    $results = $koss->getSome('users', 'username')->columns(['last_name'])->execute();
    // MySQL Output: SELECT `username`, `last_name` FROM `users`
    ```

  - Inserting information
    ```php
    // Insert a new row into the "users" table, if there is a unique row constraint, update only the username to "Aber"
    $koss->insert('users', ['username' => 'Aberdeener', 'first_name' => 'tadhg', 'last_name' => 'boyle'])->onDuplicateKey(['username' => 'Aber'])->execute();
    // MySQL Output: INSERT INTO `users` (`username`, `first_name`, `last_name`) VALUES ('Aberdeener', 'tadhg', 'boyle') ON DUPLICATE KEY UPDATE `username` = 'Aber' 
    ```

  - Updating information
    ```php
    // Update any existing rows in the "users" table which match the following criteria, update the username to "Aber" and the first_name to "Tadhg" where their "id" is 1 and their last_name is "Boyle"
    $koss->update('users', ['username' => 'Aber', 'first_name' => 'Tadhg'])->where('id', 1)->where('last_name', '=', 'Boyle')->execute();
    // MySQL Output: UPDATE `users` SET `username` = 'Aber', `first_name` = 'Tadhg' WHERE `id` = '1' AND `last_name` = 'Boyle' 
    ```