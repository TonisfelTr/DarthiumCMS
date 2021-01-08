<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

function getBrick(){
    $e = ob_get_contents();
    ob_clean();
    return $e;
}

function str_replace_once($search, $replace, $text){
    $pos = strpos($text, $search);
    return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

if ($user->UserGroup()->getPermission("bmail_sende") ||
    $user->UserGroup()->getPermission("bmail_sends")){
    if (isset($_POST["email-send-text"])){
        $userList = \Users\UserAgent::GetAllUsers();
        foreach ($userList as $someUser){
            include "site/templates/" . \Engine\Engine::GetEngineInfo("stp")  . "/mailbody.html";
            $mail = getBrick();
            $mail = str_replace_once("{MAIL_SITENAME}", \Engine\Engine::GetEngineInfo("sn"), $mail);
            $mail = str_replace_once("{MAIL_NICKNAME_TO}", $someUser["nickname"] . ",", $main);
            $mail = str_replace_once("{MAIL_BODY_MAIN}", $_POST["email-text-message"], $mail);
            $mail = str_replace_once("{MAIL_FOOTER_INFORMATION}", \Engine\LanguageManager::GetTranslation("postman.email_panel.bot_mail_send_text"), $mail);
            \Engine\Mailer::SendMail($mail, \Users\UserAgent::GetUserParam($someUser["id"], "email"), $_POST["email-subject-input"]);
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("postman.email_panel.bot_mail_send_log") . "\"" . $_POST["email-subject-input"] . "\".");
        }
        header("Location: ../../adminpanel.php?p=emailsender&res=8ses"); exit;
    }
    if (isset($_POST["pmmail-send-text"])){
        $userList = \Users\UserAgent::GetAllUsers();
        foreach ($userList as $someUser){
            $user->MessageManager()->send($someUser["id"], $_POST["pm-subject-input"], $_POST["pmmail-text-message"]);
        }
        \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("postman.pm_panel.bot_mail_send_log") . "\"" . $_POST["pm-subject-input"] . "\".");
        header("Location: ../../adminpanel.php?p=emailsender&res=8ses"); exit;
    }
} else {
    header("Location: ../../adminpanel.php?p=forbidden"); exit;
}