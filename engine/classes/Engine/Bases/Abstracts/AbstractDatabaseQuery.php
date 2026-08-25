<?php

namespace Engine\Bases\Abstracts;

use Engine\Bases\Interfaces\QueryInterface;
use Engine\Database;

abstract class AbstractDatabaseQuery implements QueryInterface
{
    protected array  $content = [];
    protected bool   $distinct = false;
    protected string $fromTable = '';
    protected string $fromTableAlias;
    protected bool $fromIsSubquery = false;
    protected array  $groupBy;
    protected string $having;
    protected array  $joins;
    protected int    $limit;
    protected int    $offset;
    protected string $orderBy;
    protected array  $select;
    protected int    $type;
    protected array  $where;
    protected string $query = '';
    
    public function getContents(): array {
        return $this->content;
    }
    
    public function __toString(): string {
        return $this->query;
    }
    
    public function runDirectly(): array|true {
        Database::runQuery($this->get(), $this->content);
        
        if (Database::isReturned()) {
            return Database::getLastResult();
        }
        elseif (Database::getLastAffectedCount() || Database::getLastInsertedID()) {
            return true;
        }
        
        return Database::getLastResult();
    }
    
    public abstract function get(): string;
}