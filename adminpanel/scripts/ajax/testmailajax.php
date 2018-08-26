<?php

include "../../../engine/main.php";
\Engine\Engine::LoadEngine();
if (\Users\UserAgent::SessionContinue() === true){
    $user = new \Users\User($_SESSION["uid"]);
    if (!$user->UserGroup()->getPermission("change_engine_settings")){
        header("Location: ../../adminpanel.php?res=1");
        exit;
    }
    if (@$_POST["test"] == 1) {
        if (\Engine\Mailer::SendMail("Тест отсылки сообщения.", "bot.tonisfeltavern@gmail.com", "Тестирование почтового бота."))
            echo "okey";
        else
            echo "false";
    }
}