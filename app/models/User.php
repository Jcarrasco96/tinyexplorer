<?php

namespace app\models;

use app\database\Database;
use app\services\Security;
use Exception;

class User
{

    /**
     * @throws Exception
     */
    public static function findUserByCredentials($username, $password): array
    {
        $db = new Database();

        $username = $db->sqlEscape($username);
        $password = $db->sqlEscape($password);

        $sql = sprintf("SELECT id, username, password, info FROM user WHERE username = '%s'", $username);

        $data = $db->uniqueQuery($sql);

        if ($data && Security::validatePassword($password, $data['password'])) {
            return $data;
        }

        return [];
    }

    /**
     * @throws Exception
     */
    public static function register($username, $password): int|string
    {
        $db = new Database();

        $username = $db->sqlEscape($username);
        $password = $db->sqlEscape($password);

        $password = Security::generatePasswordHash($password);

        $sql = sprintf("INSERT INTO user (username, password) VALUES ('%s', '%s')", $username, $password);

        $db->query($sql);

        return $db->insertId();
    }

    public static function users(): array
    {
        $db = new Database();

        $data = $db->fetchQuery("SELECT id, username, info FROM user");

        array_walk($data, function (&$item) {
            $item['info'] = json_decode($item['info'] ?? '', true);
        });

        return $data;
    }

    /**
     * @throws Exception
     */
    public static function new($username, $password): int|string
    {
        $db = new Database();

        $username = $db->sqlEscape($username);
        $password = $db->sqlEscape($password);

        $password = Security::generatePasswordHash($password);

        $jsonPermissions = json_encode([
            "cDelete" => 0,
            "cUpload" => 0,
            "cRename" => 0,
            "cCopy" => 0,
            "cCompress" => 1,
            "cDownload" => 1,
            "cShare" => 1,
            "cAdmin" => 0,
        ]);

        $sql = sprintf("INSERT INTO user (username, password, info) VALUES ('%s', '%s', '%s')", $username, $password, $jsonPermissions);

        $db->query($sql);

        return $db->insertId();
    }

    public static function delete(int $id): bool
    {
        $db = new Database();

        $id = $db->sqlEscape($id);

        $db->query(sprintf("DELETE FROM user WHERE id = %u", $id));

        return $db->affectedRows() > 0;
    }

    public static function update(int $id, string $attribute): bool
    {
        $db = new Database();

        $id = $db->sqlEscape($id);
        $attribute = 'c' . $db->sqlEscape($attribute);

        $sql = sprintf("SELECT id, info FROM user WHERE id = %u", $id);

        $user = $db->uniqueQuery($sql);

        $user['info'] = json_decode($user['info'] ?? '', true);

        $user['info'][$attribute] = empty($user['info'][$attribute]) ? 1 : 0;

        $sql = sprintf("UPDATE user SET info = '%s' WHERE id = %u", json_encode($user['info']), $id);

        $db->query($sql);

        return $db->affectedRows() > 0;
    }

    public static function findById(int $id): array
    {
        $db = new Database();

        $id = $db->sqlEscape($id);

        $data = $db->uniqueQuery("SELECT id, username, info FROM user WHERE id = '$id'");

        $data['info'] = json_decode($data['info'] ?? '', true);

        return $data;
    }

    /**
     * @throws Exception
     */
    public static function changePassword(int $id, string $password): bool
    {
        $db = new Database();

        $id = $db->sqlEscape($id);
        $password = $db->sqlEscape($password);

        $password = Security::generatePasswordHash($password);

        $sql = sprintf("UPDATE user SET password = '%s' WHERE id = %u", $password, $id);

        $db->query($sql);

        return $db->affectedRows() > 0;
    }

    /**
     * @throws Exception
     */
    public static function changeMyPassword(int $id, string $password, string $oldPassword): bool
    {
        $db = new Database();

        $id = $db->sqlEscape($id);
        $password = $db->sqlEscape($password);
        $oldPassword = $db->sqlEscape($oldPassword);

        $data = $db->uniqueQuery(sprintf("SELECT id, password FROM user WHERE id = %u", $id));

        if (!$data || !Security::validatePassword($oldPassword, $data['password'])) {
            return false;
        }

        $password = Security::generatePasswordHash($password);

        $db->query(sprintf("UPDATE user SET password = '%s' WHERE id = %u", $password, $id));

        return $db->affectedRows() > 0;
    }



}