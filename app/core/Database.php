<?php

namespace app\core;

use mysqli;
use mysqli_result;

class Database
{

    private mysqli|false $connection;

    public function __construct()
    {
        $databaseConfig = App::$config['db'];

        $host = $databaseConfig['host'];
        $user = $databaseConfig['user'];
        $password = $databaseConfig['password'];
        $dbname = $databaseConfig['dbname'];
        $port = $databaseConfig['port'];
        $charset = $databaseConfig['charset'];

        $this->connection = mysqli_connect($host, $user, $password, $dbname, $port);
        $this->setChar($charset);
    }

    public function __destruct()
    {
        if ($this->connection) {
            mysqli_close($this->connection);
        }
    }

    private function setChar($charset): void
    {
        $this->query("SET NAMES " . $charset);
    }

    public function query($resource): mysqli_result|bool
    {
        return mysqli_query($this->connection, $resource);
    }

    public function uniqueQuery($resource): false|array|null
    {
        $result = $this->query($resource);
        $Return = $result->fetch_array(MYSQLI_ASSOC);
        $result->close();
        return $Return;
    }

    public function countQuery($resource)
    {
        $result = $this->query($resource);
        list($Return) = $result->fetch_array(MYSQLI_NUM);
        $result->close();
        return $Return;
    }

    public function fetchQuery($resource): array
    {
        $result = $this->query($resource);
        $return = [];
        while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
            $return[] = $data;
        }
        $result->close();
        return $return;
    }

    public function fetchArray($result)
    {
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    public function fetchNum($result)
    {
        return $result->fetch_array(MYSQLI_NUM);
    }

    public function numRows($query)
    {
        return $query->num_rows;
    }

    public function insertId(): int|string
    {
        return $this->connection->insert_id;
    }

    public function sqlEscape($string, $flag = false): string
    {
        return ($flag === false) ? mysqli_real_escape_string($this->connection, $string) : addcslashes(mysqli_real_escape_string($this->connection, $string), '%_');
    }

    public function freeResult($resource)
    {
        return $resource->close();
    }

    public function affectedRows(): int|string
    {
        return $this->connection->affected_rows;
    }

    public function fetchFields($table): array
    {
        return $this->query("SELECT * FROM " . $table . " LIMIT 1;")->fetch_fields();
    }

}