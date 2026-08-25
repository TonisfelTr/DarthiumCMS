<?php

namespace Engine\Bases\Structs;

use Engine\Bases\Enum\MySQLColumnTypesEnum;

class TableColumnStruct
{
    protected string $name;
    protected MySQLColumnTypesEnum $type;
    protected int    $maxLength = 255;
    protected string $defaultValue;
    protected bool $isNullable = false;
    protected bool $unsigned   = false;
    
    public static function create(string $name): self {
        return new self($name);
    }
    
    protected function __construct(string $name) {
        $this->name = $name;
    }
    
    public function setMaxLength(int $maxLength): self {
        $this->maxLength = $maxLength;
        
        return $this;
    }
    
    public function setDefaultValue(mixed $defaultValue): self {
        $this->defaultValue = $defaultValue;
        
        return $this;
    }
    
    public function setUnsigned(bool $unsignedValue = true): self {
        $this->unsigned = $unsignedValue;
        
        return $this;
    }
    
    public function setNullable(bool $value = true): self {
        $this->isNullable = $value;
        
        return $this;
    }
}