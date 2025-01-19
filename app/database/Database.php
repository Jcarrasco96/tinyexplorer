<?php

namespace app\database;

use app\core\App;
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

    public function query(string $query): mysqli_result|bool
    {
        return mysqli_query($this->connection, $query);
    }

    public function uniqueQuery(string $query): false|array|null
    {
        $result = $this->query($query);
        $array = $result->fetch_array(MYSQLI_ASSOC);
        $this->freeResult($result);
        return $array;
    }

    public function countQuery(string $query)
    {
        $result = $this->query($query);
        list($array) = $result->fetch_array(MYSQLI_NUM);
        $this->freeResult($result);
        return $array;
    }

    public function fetchQuery(string $query): array
    {
        $result = $this->query($query);
        $return = [];
        while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
            $return[] = $data;
        }
        $this->freeResult($result);
        return $return;
    }

    public function fetchArray(mysqli_result $result): false|array|null
    {
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    public function fetchNum(mysqli_result $result): false|array|null
    {
        return $result->fetch_array(MYSQLI_NUM);
    }

    public function numRows(mysqli_result $query): int|string
    {
        return $query->num_rows;
    }

    public function insertId(): int|string
    {
        return $this->connection->insert_id;
    }

    public function sqlEscape(string $string, $flag = false): string
    {
        return ($flag === false) ? mysqli_real_escape_string($this->connection, $string) : addcslashes(mysqli_real_escape_string($this->connection, $string), '%_');
    }

    public function freeResult(mysqli_result $resource): void
    {
        $resource->close();
    }

    public function affectedRows(): int|string
    {
        return $this->connection->affected_rows;
    }

    public function fetchFields(string $table): array
    {
        return $this->query("SELECT * FROM " . $table . " LIMIT 1;")->fetch_fields();
    }

}