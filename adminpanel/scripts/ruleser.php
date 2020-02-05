<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

echo 1;

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