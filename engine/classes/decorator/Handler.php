<?php

namespace Decorator;

const SB_TABLE = "tt_staticcomponents";
const SB_NAVIGATOR = "tt_navbar";
const SB_RIGHTSIDE = 3;
const SB_LEFTSIDE = 2;

class Handler
{
    public static function includeDependencies(string $classPath) {
        $namespace = explode("\\", $classPath);
        $namespace[count($namespace)-2] = lcfirst($namespace[count($namespace)-2]);
        $filePath = "engine/classes/decorator/{$namespace[count($namespace)-2]}/{$namespace[count($namespace)-1]}";
        include_once $filePath . ".php";
    }
}