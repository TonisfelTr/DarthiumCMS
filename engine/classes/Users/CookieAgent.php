<?php

namespace users;

use Engine\Engine;

class CookieAgent
{
    public static function AddCookie(string $name, $content, int $lifeTime, string $whereCookieActive = "/") {
        setcookie($name, $content, $lifeTime, $whereCookieActive, Engine::GetEngineInfo("dm"));
    }
    
    public static function RemoveCookie(string $name, string $whereCookieWasActive = "/") {
        setcookie($name, "", 0, $whereCookieWasActive, Engine::GetEngineInfo("dm"));
    }
}