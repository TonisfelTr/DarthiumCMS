<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../index.php?page=errors/nonauth"); exit;}

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if ($user->getId() == @$_POST["uid"]){
    header("Location: ../../profile.php?res=yuid");
    exit;
}

if (\Guards\CaptchaMen::CheckCaptcha($_POST["reputation-captcha"], $_POST["reputation-captcha-id"], 4)){
    $rUser = new \Users\User($_POST["uid"]);
    if (\Engine\Engine::GetEngineInfo("vmr") && $user->getReputation()->getPointsFromUserCount($rUser->getId()) > 0){
        header("Location: ../../profile.php?uid=" . $rUser->getId() . "&res=nсhot");
        exit;
    }

    if ($rUser->getReputation()->addReputationPoint($user->getId(), $_POST["reputation-comment"], $_POST["reputation-type"])) {
        header("Location: ../../profile.php?uid=" . $rUser->getId() . "&res=sarp");
        exit;
    } else {
        header("Location: ../../profile.php?uid=" . $rUser->getId() . "&res=narp");
        exit;
    }
} else {
    header("Location: ../../profile.php?uid=" . $_POST["uid"] . "&res=nvc");
    exit;
}