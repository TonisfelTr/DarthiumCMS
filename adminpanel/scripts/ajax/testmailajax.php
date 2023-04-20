<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";;
\Engine\Engine::LoadEngine();
if (\Users\UserAgent::SessionContinue() === true){
    $user = new \Users\Models\User($_SESSION["uid"]);
    if (!$user->getUserGroup()->getPermission("change_engine_settings")){
        header("Location: ../../adminpanel.php?res=1");
        exit;
    }
    if (@$_POST["test"] == 1) {
        if (\Engine\Mailer::SendMail("Тест отсылки сообщения.", \Engine\Engine::GetEngineInfo("el"), "Тестирование почтового бота."))
            echo "okey";
        else
            echo "false";
    }
}