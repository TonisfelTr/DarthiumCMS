<?php

include "../../../engine/main.php";
\Engine\Engine::LoadEngine();

if (\Users\UserAgent::SessionContinue() === true){
    $user = new \Users\User($_SESSION["uid"]);
    echo $user->Notifications()->getNotificationsUnreadCount();


}

