<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

if (isset($_REQUEST["profile-activation-code-send-btn"])) {
    $session = \Users\UserAgent::SessionContinue();
    if ($session !== TRUE) {
        if (!empty($_REQUEST["activate"])) $activateCode = $_REQUEST["activate"];
        elseif (!empty($_REQUEST["profile-activation-code-input"])) $activateCode = $_REQUEST["profile-activation-code-input"];
        if (!empty($_REQUEST["uid"])) $uid = $_REQUEST["uid"];
        else $uid = $_SESSION["uid"];
        if (empty($activateCode)) {
            header("Location: ../../profile.php?activate&res=nac");
            exit;
        }
        if (\Users\UserAgent::ActivateAccount($uid, $activateCode) === TRUE) {
            header("Location: ../../profile.php?uid=" . $uid . "&res=saa");
            exit;
        } else {
            header("Location: ../../profile.php?activate&res=iac");
            exit;
        }
    }
}


if (isset($_REQUEST["profile-activation-cancel-btn"])){
    \Users\UserAgent::SessionDestroy();
    header("Location: ../../profile.php");
    exit;
}