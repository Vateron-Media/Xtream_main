<?php

class Database {
    public $result = null;
    public $last_query = null;
    protected $dbuser = null;
    protected $dbpassword = null;
    protected $dbname = null;
    protected $dbhost = null;
    protected $dbport = null;
    public $dbh = null;
    protected $pconnect = false;
    public $connected = false;

    /**
     * Constructor - Initializes database connection
     *
     * @param string $db_user Database username
     * @param string $db_pass Database password
     * @param string $db_name Database name
     * @param string $host Database host
     * @param int $db_port Database port number
     * @param bool $pconnect Whether to use persistent connection
     */
    public function __construct($db_user, $db_pass, $db_name, $host, $db_port = 7999, $pconnect = false) {
        $this->dbh = false;
        $this->dbuser = $db_user;
        $this->dbpassword = $db_pass;
        $this->dbname = $db_name;
        $this->dbhost = $host;
        $this->pconnect = $pconnect;
        $this->dbport = $db_port;
        $this->db_connect();
    }
    public function __destruct() {
        $this->close_mysql();
    }
    public function close_mysql() {
        if ($this->connected) {
            $this->dbh = null; // Properly close the PDO connection
            $this->connected = false;
        }
        return true;
    }

    /**
     * Checks if the database connection is alive
     *
     * @return bool True if connection is alive, false otherwise
     */
    public function ping() {
        try {
            $this->dbh->query('SELECT 1');
        } catch (PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Establishes database connection
     *
     * @return bool True on successful connection, never returns on failure (exits)
     */
    public function db_connect(): bool {
        if ($this->connected) {
            return true;
        }

        $options = $this->getDefaultPdoOptions();

        try {
            $this->dbh = new PDO(
                $this->buildDsn(),
                $this->dbuser,
                $this->dbpassword,
                $options
            );

            $this->connected = true;
            return true;
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }

    /**
     * Dumps prepared statement parameters for debugging
     *
     * @param PDOStatement $stmt The prepared statement to debug
     * @return string Debug information
     */
    public function debugString($stmt) {
        ob_start();
        $stmt->debugDumpParams();
        $r = ob_get_contents();
        ob_end_clean();

        return $r;
    }

    /**
     * Executes a prepared SQL query with optional parameter binding
     *
     * @throws PDOException When database query execution fails
     * @return bool Returns true on successful query execution, false on failure or if database handle is not initialized
     *
     */
    public function query($query, $buffered = false) {
        if (!$this->dbh) {
            return false;
        }
        $numargs = func_num_args();
        $arg_list = func_get_args();
        $next_arg_list = array();
        $i = 1;

        while ($i < $numargs) {
            if (is_null($arg_list[$i]) || strtolower($arg_list[$i]) == 'null') {
                $next_arg_list[] = null;
            } else {
                $next_arg_list[] = $arg_list[$i];
            }

            $i++;
        }

        if ($buffered === true) {
            $this->dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        }

        try {
            $this->result = $this->dbh->prepare($query);
            $this->result->execute($next_arg_list);
        } catch (PDOException $e) {
            $debug_output = $this->debugString($this->result);
            if (strpos($debug_output, 'Sent SQL:') !== false) {
                $parts = explode('Sent SQL:', $debug_output);
                if (isset($parts[1])) {
                    $actual_query = trim(explode("\n", $parts[1])[0]);
                } else {
                    $actual_query = '';
                }
            } else {
                $actual_query = '';
            }

            if (strlen($actual_query) == 0) {
                $actual_query = $query;
            }
            if (class_exists('ipTV_lib')) {
                ipTV_lib::saveLog('pdo', $e->getMessage(), $actual_query);
            }

            return false;
        }

        return true;
    }

    /**
     * Fetches all rows from the result set with optional custom indexing
     *
     * This method retrieves all rows from the current result set and provides
     * various options for structuring the returned array. It can index rows
     * by a specific column and create nested arrays based on parameters.
     *
     * @param bool   $use_id       Whether to use a column value as array key
     * @param string $column_as_id The column name to use as array key when $use_id is true
     * @param bool   $unique_row   If true, only keeps one row per $column_as_id value
     *                            If false, creates an array of rows for each $column_as_id
     * @param string $sub_row_id   Column to use as secondary index when $unique_row is false
     *
     * @return array|false
     *
     * @example
     * // Simple fetch all rows
     * $db->query("SELECT * FROM users");
     * $rows = $db->get_rows(); // Returns: [['id'=>1, 'name'=>'John'], ['id'=>2, 'name'=>'Jane']]
     *
     * // Index by ID
     * $rows = $db->get_rows(true, 'id');
     * // Returns: [1=>['id'=>1, 'name'=>'John'], 2=>['id'=>2, 'name'=>'Jane']]
     *
     * // Multiple rows per ID with sub-index
     * $rows = $db->get_rows(true, 'user_id', false, 'date');
     * // Returns: ['user_id'=>['date1'=>[...], 'date2'=>[...]]]
     *
     */
    public function get_rows($use_id = false, $column_as_id = '', $unique_row = true, $sub_row_id = '') {
        if (!($this->dbh && $this->result)) {
            return false;
        }
        $rows = array();

        if ($this->result->rowCount() > 0) {
            foreach ($this->result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if ($use_id && array_key_exists($column_as_id, $row)) {
                    if (!isset($rows[$row[$column_as_id]])) {
                        $rows[$row[$column_as_id]] = array();
                    }

                    if (!$unique_row) {
                        if (!empty($sub_row_id) && array_key_exists($sub_row_id, $row)) {
                            $rows[$row[$column_as_id]][$row[$sub_row_id]] = $this->clean_row($row);
                        } else {
                            $rows[$row[$column_as_id]][] = $this->clean_row($row);
                        }
                    } else {
                        $rows[$row[$column_as_id]] = $this->clean_row($row);
                    }
                } else {
                    $rows[] = $this->clean_row($row);
                }
            }
        }
        $this->result = null;

        return $rows;
    }
    /**
     * Fetches a single row from the result set
     *
     * Retrieves the next row from the current result set and cleans it.
     * After fetching the row, the result set is cleared.
     * If there's no active connection, result set, or no rows are found,
     * the method returns false.
     *
     * @return array|false
     *
     * @see clean_row() For data sanitization process
     *
     * @example
     * $db->query("SELECT * FROM users WHERE id = ?", false, 1);
     * $row = $db->get_row();
     * // Returns: ['id' => 1, 'name' => 'John', ...] or false if not found
     */
    public function get_row() {
        if (!($this->dbh && $this->result)) {
            return false;
        }
        $row = array();

        if ($this->result->rowCount() > 0) {
            $row = $this->result->fetch(PDO::FETCH_ASSOC);
        }
        $this->result = null;

        return $this->clean_row($row);
    }
    /**
     * Fetches the first column of the first row from the result set
     *
     * This method is useful when you need to retrieve a single value from
     * the first row of a result set (e.g., COUNT queries, MAX, MIN, etc.).
     * After fetching the value, the result set is cleared.
     *
     * @return mixed|false
     *
     * @example
     * // Count total users
     * $db->query("SELECT COUNT(*) FROM users");
     * $total = $db->get_col(); // Returns: 42
     *
     */
    public function get_col() {
        if (!($this->dbh && $this->result)) {
            return false;
        }
        $row = false;

        if ($this->result->rowCount() > 0) {
            $row = $this->result->fetch();
            $row = $row[0];
        }
        $this->result = null;

        return $row;
    }
    public function affected_rows() {
        return $this->result ? $this->result->rowCount() : 0;
    }
    public function simple_query($query) {
        try {
            $this->result = $this->dbh->query($query);
        } catch (Exception $e) {
            if (class_exists('ipTV_lib')) {
                ipTV_lib::saveLog('pdo', $e->getMessage(), $query);
            }
            return false;
        }
        return true;
    }

    /**
     * Escapes a string for safe database usage
     *
     * @param string $string The string to escape
     * @return string|null The escaped string or null if database handle is not available
     * @throws PDOException If the quote operation fails
     */
    public function escape(?string $string): ?string {
        if ($string === null) {
            return null;
        }

        if (!$this->dbh) {
            throw new RuntimeException('Database connection not initialized');
        }

        try {
            return $this->dbh->quote($string);
        } catch (PDOException $e) {
            throw new PDOException('Failed to escape string: ' . $e->getMessage(), 0, $e);
        }
    }

    public function num_fields() {
        if (!($this->dbh && $this->result)) {
            return 0;
        }
        $mysqli_num_fields = $this->result->columnCount();
        return (empty($mysqli_num_fields) ? 0 : $mysqli_num_fields);
    }
    /**
     * Gets the ID of the last inserted row
     *
     * Returns the ID of the last inserted row, or the last value from a sequence.
     * If no insert operation has been performed or if the connection is not established,
     * the method returns 0.
     *
     * @return string|int The last insert ID (as returned by the database),
     *                    or 0 if no connection exists or no rows were inserted
     *
     */
    public function last_insert_id() {
        if ($this->dbh) {
            $mysql_insert_id = $this->dbh->lastInsertId();
            return (empty($mysql_insert_id) ? 0 : $mysql_insert_id);
        }
    }
    /**
     * Gets the number of rows in the result set
     *
     * This method returns the number of rows from the last query result.
     * If there's no active connection or result set, it returns 0.
     * Note: For SELECT statements, rowCount() may not return the actual number
     * of rows for all databases. It's reliable for INSERT, UPDATE, DELETE queries.
     *
     * @return int The number of rows in the result set, or 0 if no result set exists
     *             or if the result set is empty
     */
    public function num_rows() {
        if (!($this->dbh && $this->result)) {
            return 0;
        }
        $mysqli_num_rows = $this->result->rowCount();
        return (empty($mysqli_num_rows) ? 0 : $mysqli_num_rows);
    }
    /**
     * Sanitizes and cleans input value by normalizing line endings and encoding HTML special characters
     *
     * @param string|null $value The input value to clean
     * @return string The sanitized string
     */
    public static function parseCleanValue($rValue) {
        if ($rValue != '') {
            $rValue = str_replace(array("\r\n", "\n\r", "\r"), "\n", $rValue);
            $rValue = str_replace('<', '&lt;', str_replace('>', '&gt;', $rValue));
            $rValue = str_replace('<!--', '&#60;&#33;--', $rValue);
            $rValue = str_replace('-->', '--&#62;', $rValue);
            $rValue = str_ireplace('<script', '&#60;script', $rValue);
            $rValue = preg_replace('/&amp;#([0-9]+);/s', '&#\\1;', $rValue);
            $rValue = preg_replace('/&#(\\d+?)([^\\d;])/i', '&#\\1;\\2', $rValue);

            return trim($rValue);
        }
        return '';
    }
    /**
     * Cleans all non-empty values in a database row using parseCleanValue().
     *
     * @param array $row The database row to clean
     * @return array The cleaned row with sanitized values
     */
    public function clean_row($row) {
        foreach ($row as $key => $value) {
            if ($value) {
                $row[$key] = self::parseCleanValue($value);
            }
        }
        return $row;
    }

    /**
     * Gets the default PDO connection options
     *
     * @return array Array of PDO options
     */
    private function getDefaultPdoOptions(): array {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => $this->pconnect
        ];
    }

    /**
     * Builds the DSN string for PDO connection
     *
     * @return string The formatted DSN string
     */
    private function buildDsn(): string {
        return sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
            $this->dbhost,
            $this->dbport,
            $this->dbname
        );
    }

    /**
     * Handles PDO connection errors by formatting and outputting error details
     *
     * @param PDOException $e The PDO exception that was caught
     * @throws never This method never returns as it terminates execution
     * @return never The function never returns due to exit() call
     */
    private function handleConnectionError(PDOException $e): never {
        $error = [
            'error' => 'MySQL: ' . $e->getMessage(),
            'code' => $e->getCode()
        ];

        exit(json_encode($error));
    }
}
