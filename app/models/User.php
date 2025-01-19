<?php

namespace app\models;

use app\database\Database;

class User
{

    public static function findUserByCredentials($email, $password) {

        $db = new Database();

        $email = $db->sqlEscape($email);
        $password = $db->sqlEscape($password);

        $sql = sprintf("SELECT id, email, password FROM user WHERE email = '%s'", $email);

        $data = $db->uniqueQuery($sql);

        if ($data && password_verify($password, $data["password"])) {
            return $data['id'];
        }

        return null;
    }

    public static function register($email, $password): int|string
    {

        $db = new Database();

        $email = $db->sqlEscape($email);
        $password = $db->sqlEscape($password);

        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = sprintf("INSERT INTO user (email, password) VALUES ('%s', '%s')", $email, $password);

        $db->query($sql);

        return $db->insertId();
    }

}