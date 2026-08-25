<?php

use Builder\Controllers\BuildAgent;
use Engine\Engine;
use Engine\ErrorAgent;
use Engine\RouteAgent;
use Exceptions\TavernException;

if (!file_exists("./../engine/classes/Engine/Engine.php")) {
    echo "Cannot find Engine class. Call to server administrator.";
    exit(0);
}

require_once "./../engine/classes/Engine/Engine.php";
Engine::LoadEngine();



