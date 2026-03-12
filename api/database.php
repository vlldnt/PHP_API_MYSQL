<?php

class Database {
    private $host = '127.0.0.1';
    private $db_name = 'php_learn';
    private $username = 'root';
    private $password = 'awlmpzw12';
    public $conn;

    public function getConnection()
    {
        // Reset connection before each attempt
        $this->conn = null;

        try {
            // Build the DSN (Data Source Name) for PDO
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";

            // Open MySQL connection using PDO (PHP Data Object)
            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Configure PDO: throw exceptions on SQL errors and fetch rows as associative arrays
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo 'Database connection error: ' . $e->getMessage();
            return null;
        }
        return $this->conn;
    }

    public function getDbName()
    {
        return $this->db_name;
    }
}
