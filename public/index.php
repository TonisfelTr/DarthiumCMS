<?php

use Builder\Controllers\BuildManager;
use Engine\Engine;
use Engine\ErrorManager;
use Engine\RouteAgent;
use Exceptions\TavernException;

if (!file_exists("./../engine/classes/engine/Engine.php")) {
    echo "Cannot find Engine class. Call to server administrator.";
    exit(0);
}

require_once "./../engine/classes/engine/Engine.php";
Engine::LoadEngine();

