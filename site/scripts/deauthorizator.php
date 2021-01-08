<?php

include "../../engine/main.php";
\Engine\Engine::LoadEngine();

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

\Users\UserAgent::SessionDestroy();
header("Location: ../../profile.php");
exit;
