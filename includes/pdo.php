<?php

class ipTV_db {
    public $result;
    protected $last_query;
    protected $dbuser;
    protected $dbpassword;
    protected $dbname;
    protected $dbhost;
    protected $dbport;
    public $dbh;
    protected $pconnect = false;
    protected $connected = false;

    public function __construct($db_user, $db_pass, $db_name, $host, $db_port = 7999, $pconnect = false) {
        $this->dbh = null;
        $this->dbuser = $db_user;
        $this->dbpassword = $db_pass;
        $this->dbname = $db_name;
        $this->dbhost = $host;
        $this->pconnect = $pconnect;
        $this->dbport = $db_port;
    }

    public function close_mysql() {
        $this->dbh = null; // Properly close the PDO connection
        $this->connected = false;
        return true;
    }

    function __destruct() {
        $this->close_mysql();
    }

    public function db_connect() {
        if ($this->connected && $this->dbh) {
            return true;
        }

        $dsn = "mysql:host={$this->dbhost};port={$this->dbport};dbname={$this->dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exception-based error handling
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Default fetch mode
            PDO::ATTR_PERSISTENT => $this->pconnect // Persistent connection if enabled
        ];

        try {
            $this->dbh = new PDO($dsn, $this->dbuser, $this->dbpassword, $options);
            $this->connected = true;
        } catch (PDOException $e) {
            $this->logError("Database connection error: " . $e->getMessage());
            die(json_encode(["error" => "MySQL connection failed"]));
        }

        return true;
    }

    public function query($query, ...$params) {
        $this->db_connect();
        $this->last_query = $query;
        try {
            $this->result = $this->dbh->prepare($query);
            $this->result->execute($params);
        } catch (PDOException $e) {
            $this->logError("Query error [{$query}]: " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function get_rows($use_id = false, $column_as_id = "", $unique_row = true, $column = "") {
        if ($this->result) {
            $rows = [];
            $data = $this->result->fetchAll();
            if ($use_id && $column_as_id) {
                foreach ($data as $row) {
                    if (!isset($rows[$row[$column_as_id]])) {
                        $rows[$row[$column_as_id]] = $unique_row ? $row : [];
                    }
                    if (!$unique_row) {
                        if (!empty($column) && isset($row[$column])) {
                            $rows[$row[$column_as_id]][$row[$column]] = $row;
                        } else {
                            $rows[$row[$column_as_id]][] = $row;
                        }
                    }
                }
            } else {
                $rows = $data;
            }
            return $rows;
        }
        return false;
    }

    public function get_row() {
        if ($this->result) {
            return $this->result->fetch();
        }
        return false;
    }

    public function get_col() {
        if ($this->result) {
            $row = $this->result->fetch(PDO::FETCH_NUM);
            return $row[0] ?? false;
        }
        return false;
    }

    public function affected_rows() {
        return $this->result ? $this->result->rowCount() : 0;
    }

    public function simple_query($query) {
        $this->db_connect();
        try {
            $this->result = $this->dbh->query($query);
        } catch (PDOException $e) {
            $this->logError("Simple query error [{$query}]: " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function escape($string) {
        $this->db_connect();
        return substr($this->dbh->quote($string), 1, -1);
    }

    public function num_fields() {
        return $this->result ? $this->result->columnCount() : 0;
    }

    public function last_insert_id() {
        return $this->dbh ? $this->dbh->lastInsertId() : 0;
    }

    public function num_rows() {
        return $this->result ? $this->result->rowCount() : 0;
    }

    private function logError($message) {
        error_log("[ipTV_db Error] " . $message);
        ipTV_lib::saveLog($message); // Assuming ipTV_lib::saveLog exists for logging
    }
}
