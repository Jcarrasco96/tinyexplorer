<?php

namespace TE\core;

abstract class BaseModel
{

    abstract protected static function tableName(): string;

    public static function findById(int $id): array
    {
        return App::$database->findById("SELECT * FROM `" . static::tableName() . "` WHERE id = :id", $id);
    }

    abstract public static function create(array $data): bool;

    abstract public static function update($id, $data): bool;

    public static function findAll(): array
    {
        return App::$database->findAll("SELECT * FROM " . static::tableName());
    }

    public static function delete($id): bool
    {
        return App::$database->delete("DELETE FROM " . static::tableName() . " WHERE id = :id", $id);
    }

}