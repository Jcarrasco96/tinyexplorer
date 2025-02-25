<?php

namespace TE\models;

use Exception;
use PDO;
use PDOException;
use TE\core\App;
use TE\core\BaseModel;
use TE\services\Security;

class User extends BaseModel
{

    protected static function tableName(): string
    {
        return 'user';
    }

    public static function findUserByCredentials($username, $password): array
    {
        try {
            $stmt = App::$database->connection->prepare("SELECT * FROM `" . static::tableName() . "` WHERE username = :username");
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data && Security::validatePassword($password, $data['password'])) {

                unset($data['password']);
                $data['info'] = json_decode($data['info'] ?? '', true);

                return $data;
            }
        } catch (PDOException|Exception $e) {

        }

        return [];
    }

    /**
     * @throws Exception
     */
    public static function register($username, $password): bool
    {
        return self::create(['username' => $username, 'password' => $password]);
    }

    public static function users(): array
    {
        $data = self::findAll();

        array_walk($data, function (&$item) {
            $item['info'] = json_decode($item['info'] ?? '', true);
        });

        return $data;
    }

    public static function findById(int $id): array
    {
        $data = parent::findById($id);

        if (isset($data['info'])) {
            $data['info'] = json_decode($data['info'], true);
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public static function updateMyPassword(int $id, string $password, string $oldPassword): bool
    {
        $user = self::findById($id);

        if (!$user || !Security::validatePassword($oldPassword, $user['password'])) {
            return false;
        }

        return self::updatePassword($id, $password);
    }

    public static function updateAttribute(int $id, string $attribute): bool
    {
        $attribute = 'c' . $attribute;

        $user = self::findById($id);

        $user['info'][$attribute] = empty($user['info'][$attribute]) ? 1 : 0;

        $user['info'] = json_encode($user['info']);

        return self::update($id, $user);
    }

    public static function create(array $data): bool
    {
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

        try {
            $stmt = App::$database->connection->prepare("INSERT INTO " . self::tableName() . " (username, password, info)  VALUES (:username, :password, :info);");
            $stmt->bindValue(':username', $data['username']);
            $stmt->bindValue(':password', Security::generatePasswordHash($data['password']));
            $stmt->bindValue(':info', $jsonPermissions);
            return $stmt->execute();
        } catch (PDOException|Exception $e) {
            return false;
        }
    }

    public static function update($id, $data): bool
    {
        try {
            $stmt = App::$database->connection->prepare("UPDATE " . self::tableName() . " SET username = :username, info = :info WHERE id = :id");
            $stmt->bindValue(':username', $data['username']);
            $stmt->bindValue(':info', $data['info']);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function updatePassword($id, $password): bool
    {
        try {
            $stmt = App::$database->connection->prepare("UPDATE " . self::tableName() . " SET password = :password WHERE id = :id");
            $stmt->bindValue(':password', Security::generatePasswordHash($password));
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

}