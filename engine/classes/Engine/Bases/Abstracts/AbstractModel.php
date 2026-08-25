<?php

namespace Engine\Bases\Abstracts;

use Engine\Database;

abstract class AbstractModel
{
    protected static string $entitiesTable;
    
    protected int $id;
    protected array $properties = [];
    
    public static function isExist(int $id): bool {
        return Database::getBuilder()
                   ->selectExists(Database::getBuilder()
                                          ->selectRaw("1")
                                          ->from(self::$entitiesTable)
                                      ->where("id", "=", $id)
                                          ->get(), "exists"
                   )
                   ->runDirectly()[0]["exists"];
    }
    
    public static function getEntitiesTable(): string {
        return self::$entitiesTable;
    }
    
    public static function create(array $parameters): bool {
        Database::getBuilder()
            ->insert(self::$entitiesTable, $parameters)
            ->runDirectly();
        
        return Database::getLastInsertedID();
    }
    
    public static function delete(int $id): bool {
        Database::getBuilder()
            ->delete(self::$entitiesTable)
            ->where("id". "=", $id)
            ->runDirectly();
        
        return !Database::getLastAffectedCount();
    }
    public function __construct(int $id) {
        $this->properties = Database::getBuilder()
            ->select("*")
                                ->from(self::$entitiesTable)
                                ->where("id", "=", $id)
                                ->runDirectly()[0];
        
        $this->id = $this->properties["id"];
    }
    
    public function getId(): int {
        return $this->id;
    }
}