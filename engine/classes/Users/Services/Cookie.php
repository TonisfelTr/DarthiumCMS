<?php

namespace Users\Services;

class Cookie
{
    private $name;
    private $value;
    
    public static function create(string $name, string $content, int $expires = 0, string $path, string $domain,
                                  bool   $secure = false, bool $httpOnly = false): bool {
        
    }
    
    public function set(string $name, string $content): bool {
    
    }
    
    public function remove(string $name, bool $isStrict = false): bool {
        if ($isStrict) {
        
        }
        
        setcookie($name, "",);
    }
}