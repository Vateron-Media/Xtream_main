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

    public function close_mysql() {
        if ($this->connected) {
            $this->dbh = null; // Properly close the PDO connection
            $this->connected = false;
        }

        return true;
    }

    public function __destruct() {
        $this->close_mysql();
    }

    public function ping() {
        try {
            $this->dbh->query('SELECT 1');
        } catch (PDOException $e) {
            return false;
        }

        return true;
    }

    public function db_connect() {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exception-based error handling
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Default fetch mode
            PDO::ATTR_PERSISTENT => $this->pconnect // Persistent connection if enabled
        ];
        try {
            $this->dbh = new PDO("mysql:host=" . $this->dbhost . ";port=" . $this->dbport . ";dbname=" . $this->dbname . ";charset=utf8mb4", $this->dbuser, $this->dbpassword, $options);

            if (!$this->dbh) {
                return false;
            }
        } catch (PDOException $e) {
            die(json_encode(["error" => "MySQL connection failed", "message" => $e->getMessage()]));
        }
        $this->connected = true;

        return true;
    }

    public function debugString($stmt) {
        ob_start();
        $stmt->debugDumpParams();
        $r = ob_get_contents();
        ob_end_clean();

        return $r;
    }

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
        } catch (Exception $e) {
            $actual_query = trim(explode("\n", explode('Sent SQL:', $this->debugString($this->result))[1])[0]);

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
    public function escape($string) {
        if ($this->dbh) {
            return $this->dbh->quote($string);
        }
    }

    public function num_fields() {
        if (!($this->dbh && $this->result)) {
            return 0;
        }
        $mysqli_num_fields = $this->result->columnCount();
        return (empty($mysqli_num_fields) ? 0 : $mysqli_num_fields);
    }

    public function last_insert_id() {
        if ($this->dbh) {
            $mysql_insert_id = $this->dbh->lastInsertId();
            return (empty($mysql_insert_id) ? 0 : $mysql_insert_id);
        }
    }

    public function num_rows() {
        if (!($this->dbh && $this->result)) {
            return 0;
        }
        $mysqli_num_rows = $this->result->rowCount();
        return (empty($mysqli_num_rows) ? 0 : $mysqli_num_rows);
    }

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

    public function clean_row($row) {
        foreach ($row as $key => $value) {
            if ($value) {
                $row[$key] = self::parseCleanValue($value);
            }
        }
        return $row;
    }
}
