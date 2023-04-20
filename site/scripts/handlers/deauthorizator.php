<?php

use Engine\Engine;
use Guards\SocietyGuard;
use Users\UserAgent;

include "../../engine/classes/engine/Engine.php";
Engine::LoadEngine();

if (SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

$logout = UserAgent::SessionDestroy();

header("Location: ../../profile.php");
exit;
