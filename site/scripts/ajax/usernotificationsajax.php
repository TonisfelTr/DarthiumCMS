<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";;
\Engine\Engine::LoadEngine();

if (\Users\UserAgent::SessionContinue() === true){
    $user = new \Users\Models\User($_SESSION["uid"]);
    echo $user->Notifications()->getNotificationsUnreadCount();


}

