<?php

namespace Engine\Bases\Abstracts;

use Engine\Bases\Interfaces\EntityAgentInterface;
use Engine\Bases\Abstracts\AbstractModel;
use Engine\Database;
use Exceptions\Exemplars\UserExistsError;

abstract class AbstractEntityAgent implements EntityAgentInterface
{
    protected static string $entityClass;
    
    public static function getEntity(int $id): AbstractModel {
        return new (self::$entityClass)($id);
    }
    
    public static function isEntityExist(int $entityId): bool {
        return (self::$entityClass)::isExist($entityId);
    }
    
    public static function getIDWithParameter(string $column, string $value): int|false {
        return Database::getBuilder()
                       ->select("id")
            ->from((self::$entityClass)::$entitiesTable)
                       ->where($column, "=", $value)
            ->runDirectly();
    }
    
    public static function getEntityParameter(int $id, string $parameterName): string {
        return Database::getBuilder()
                       ->select($parameterName)
                       ->from((self::$entityClass)::$entitiesTable)
                       ->where("id", "=", $id)
                       ->runDirectly()[0][$parameterName];
    }
    
    public static function changeEntity(int $id, string $parameterName, mixed $parameterValue): bool {
        Database::getBuilder()
            ->update((self::$entityClass)::$entitiesTable, [ $parameterName => $parameterValue ])
            ->where("id", "=", $id)
            ->runDirectly();
        
        return Database::getLastAffectedCount() > 0;
    }
}