<?php

use Engine\Engine;
use Engine\ErrorManager;
use Engine\RouteAgent;
use Exceptions\TavernException;

if ($_SERVER["REMOTE_ADDR"] != '176.65.43.104') {
    echo 'Server is on maintained. Visit us later.';
    exit(0);
}

if (!file_exists("./../engine/classes/engine/Engine.php")) {
    echo "Cannot find Engine class. Call to server administrator.";
    exit(0);
}

require_once "./../engine/classes/engine/Engine.php";
Engine::LoadEngine();

RouteAgent::parseUrl();

if (ob_get_level() > 0) {
    throw new TavernException("", ErrorManager::EC_OUTPUT_BUFFERING_IS_ACTIVE_AFTER_LOADING_END);
}

