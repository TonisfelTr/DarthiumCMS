<?php

namespace Engine\Services;

class ModuleHandler
{
    public final static function includeDependencies(string $classPath) {
        $foldersInPath = explode("/", trim(__DIR__, "/"));

        $parentFolder = end($foldersInPath);
        $namespace = explode("\\", $classPath);

        $namespace[count($namespace)-2] = lcfirst($namespace[count($namespace)-2]);
        $filePath = HOME_ROOT . "engine/classes/$parentFolder/{$namespace[count($namespace)-2]}/{$namespace[count($namespace)-1]}";
        include_once $filePath . ".php";
    }
}