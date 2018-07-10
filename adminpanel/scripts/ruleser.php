<?php

require_once "engine/main.php";
\Engine\Engine::LoadEngine();

$sessEffect = \Users\UserAgent::SessionContinue();
if ($sessEffect == True) {
    echo 1;
    $user = new \Users\User($_SESSION["uid"]);
    if ($_REQUEST["uid"] == $_SESSION["uid"] && $user->UserGroup()->getPermission("change_engine_settings") == 1) {
        $rulesFile = fopen("../../engine/config/rules.sfc", "w+", FILE_USE_INCLUDE_PATH);
        if ($rulesFile) {
            fwrite($rulesFile, $_REQUEST["rules_texter"]);
            fclose($rulesFile);
            $_SESSION["result"] = True;
        }
    } header("Location: ../../adminpanel.php?res=1");
}
header("Location: ../../adminpanel.php?p=rules");