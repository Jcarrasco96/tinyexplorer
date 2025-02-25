<?php

namespace TE\models;

use PDO;
use PDOException;
use TE\core\App;
use TE\core\BaseModel;

/**
 * @property string $theme
 * @property string $rootPath
 * @property string $language
 * @property string $use_curl
 */
class System extends BaseModel
{

    public array $configs = [
        'theme' => 'light',
        'root_path' => 'C:',
        'language' => 'en',
        'use_curl' => 'y',
    ];

    protected static function tableName(): string
    {
        return 'system';
    }

    public function __construct()
    {
        $dbConfigs = self::findById(1);

        if (empty($dbConfigs)) {
            self::create($this->configs);
        } else {
            $this->configs = $dbConfigs;
        }

        unset($this->configs['id']);
    }

    public function updateConfig(string $key, string|int $value): bool
    {
        if (!in_array($key, ['theme', 'root_path', 'language', 'use_curl'])) {
            return false;
        }

        if ($key === 'theme' && !is_string($value) && !in_array($value, ['light', 'dark'])) {
            return false;
        }

        $this->configs[$key] = $value;

        if (self::update(1, $this->configs)) {
            return true;
        }

        $this->configs = self::findById(1);

        unset($this->configs['id']);

        return false;
    }

    public function __get(string $name)
    {
        $nameFormatDatabase = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));

        return $this->configs[$nameFormatDatabase] ?? null;
    }

    public function isLightTheme(): bool
    {
        return $this->configs['theme'] === 'light';
    }

    public function isCurl(): bool
    {
        return $this->configs['use_curl'] === 'y';
    }

    public static function create(array $data): bool
    {
        try {
            $stmt = App::$database->connection->prepare("INSERT INTO `system` (id, theme, root_path, language, use_curl) VALUES (:id, :theme, :root_path, :language, :use_curl);");
            $stmt->bindValue(':id', 1, PDO::PARAM_INT);
            $stmt->bindValue(':theme', $data['theme']);
            $stmt->bindValue(':root_path', $data['root_path']);
            $stmt->bindValue(':language', $data['language']);
            $stmt->bindValue(':use_curl', $data['use_curl']);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function update($id, $data): bool
    {
        try {
            $stmt = App::$database->connection->prepare("UPDATE " . self::tableName() . " SET theme = :theme, root_path = :root_path, language = :language, use_curl = :use_curl WHERE id = :id");
            $stmt->bindValue(':theme', $data['theme']);
            $stmt->bindValue(':root_path', $data['root_path']);
            $stmt->bindValue(':language', $data['language']);
            $stmt->bindValue(':use_curl', $data['use_curl']);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

}