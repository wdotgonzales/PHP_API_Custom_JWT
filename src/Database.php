<?php

class Database
{
    // Database Parameters
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $connection;

    public function __construct($host, $db_name, $username, $password)
    {
        $this->host = $host;
        $this->db_name = $db_name;
        $this->username = $username;
        $this->password = $password;
    }

    public function connect()
    {
        $this->connection = null;
        try {
            $this->connection = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->username,
                $this->password
            );

            // Set the PDO error mode to exception. This will throw a PDOException for any database-related errors.
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Disable emulation of prepared statements. This ensures that the native prepared statement support provided by the database driver is used.
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Do not convert numeric values to strings when fetching from the database. This ensures that numeric data types are preserved.
            $this->connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        } catch (PDOException $error) {
            echo "Connection Error" . $error->getMessage();
        }
        return $this->connection;
    }
}
