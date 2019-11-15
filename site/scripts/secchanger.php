<?php
include_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

$sessEffect = \Users\UserAgent::SessionContinue();
if ($sessEffect == True) {
    echo 1;
    $user = new \Users\User($_SESSION["uid"]);
    if ( $_REQUEST["uid"] == $_SESSION["uid"] && $user->UserGroup()->getPermission("change_profile") == 1) {
         if (!isset($_REQUEST["cp"]) || !isset($_REQUEST["pass-save"])) exit;
         if ( $_REQUEST["cp"] == 1 ){
             if ($_SESSION["passhash"] != hash("sha256", $_REQUEST["profile-sec-password"])) {
                 header("Location: ../../profile.php?err=7");
                 exit;
             } elseif ($_REQUEST["profile-sec-password"] == $_REQUEST["profile-sec-npassword"]) {
                 header("Location: ../../profile.php?err=6");
                 exit;
             } else {
                 if ($user->passChange($_REQUEST["profile-sec-npassword"], true))
                 {
                     if (\Engine\Engine::GetEngineInfo("na")){
                         $sesNew = \Users\UserAgent::SessionCreate($user->getEmail(), $_REQUEST["profile-sec-npassword"]);
                     } else { $sesNew = \Users\UserAgent::SessionCreate($user->getNickname(), $_REQUEST["profile-sec-npassword"]); }
                     if ($sesNew === True) header("Location: ../../profile.php?err=5");
                 }
                 exit;
             }
         }

    } else header("Location: ../../profile.php?err=2");

    exit;
}

header("Location: ../../profile.php?err=1");