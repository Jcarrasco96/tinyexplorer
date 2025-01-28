<?php

namespace app\models;

use app\database\Database;

/**
 * @property string $theme
 * @property string $rootPath
 * @property string $language
 * @property string $use_curl
 */
class System
{

    public array $configs = [
        'theme' => 'light',
        'root_path' => 'C:',
        'language' => 'en',
        'use_curl' => 'y',
    ];

    public function __construct()
    {
        $db = new Database();

        $this->configs = $db->uniqueQuery("SELECT * FROM `system` WHERE id = 1");

        if (empty($this->configs)) {
            $db->query(sprintf("INSERT INTO `system` (id, theme, root_path, language, use_curl) VALUES (%u, '%s', '%s', '%s', '%s');", 1, "light", "C:", 'en', 'y'));
            $this->configs = $db->uniqueQuery("SELECT * FROM `system` WHERE id = 1");
        }

        unset($this->configs['id']);
    }

    public function updateConfig(string $key, string|int $value): bool
    {
        $db = new Database();

        if (!in_array($key, ['theme', 'root_path', 'language', 'use_curl'])) {
            return false;
        }

        if ($key === 'theme' && !is_string($value) && !in_array($value, ['light', 'dark'])) {
            return false;
        }

        $sqlUpdate = sprintf("UPDATE `system` SET $key = '%s' WHERE id = 1", $db->sqlEscape($value));

        $db->query($sqlUpdate);

        $this->configs = $db->uniqueQuery("SELECT * FROM `system` WHERE id = 1");
        unset($this->configs['id']);

        return true;
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

}