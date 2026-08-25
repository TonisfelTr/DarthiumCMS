<?php

namespace Engine\Bases\Interfaces;

use Engine\Bases\Abstracts\AbstractModel;

interface EntityAgentInterface
{
    static function getEntity(int $id): AbstractModel;
    
    static function isEntityExist(int $entityId): bool;
    
    static function createNew(array $parameters): bool;
}