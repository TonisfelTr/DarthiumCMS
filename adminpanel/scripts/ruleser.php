<?php

require_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\Models\User((new \Users\Services\Session(\Users\Services\FlashSession::getSessionId()))->getContent()["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if ($user->UserGroup()->getPermission("rules_edit") == 1) {
    $rulesFile = fopen("../../engine/config/rules.sfc", "w+", FILE_USE_INCLUDE_PATH);
    if ($rulesFile) {
        fwrite($rulesFile, $_REQUEST["rules_texter"]);
        fclose($rulesFile);
        $_SESSION["result"] = True;
        \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("rules_panel.change_rules_log"));
        header("Location: ../../adminpanel.php?p=rules&rules_save");
        exit;
    }
} else {
    header("Location: ../../adminpanel.php?res=1");
    exit;
}

header("Location: ../../adminpanel.php?res=1");
exit;