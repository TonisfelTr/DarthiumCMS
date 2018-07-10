<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../index.php?page=errors/nonauth"); exit;}

if ($user->getId() == @$_REQUEST["uid"]){
    header("Location: ../../profile.php?res=yuid");
    exit;
}

if (\Guards\CaptchaMen::CheckCaptcha($_REQUEST["reputation-captcha"], $_REQUEST["reputation-captcha-id"], 4)){
    $rUser = new \Users\User($_REQUEST["uid"]);
    if ($rUser->getReputation()->addReputationPoint($user->getId(), $_REQUEST["reputation-comment"], $_REQUEST["reputation-type"])){
        header("Location: ../../profile.php?uid=" . $rUser->getId() . "&res=sarp");
        exit;
    } else {
        header("Location: ../../profile.php?uid=" . $rUser->getId() . "&res=narp");
        exit;
    }
} else {
    header("Location: ../../profile.php?uid=" . $_REQUEST["uid"] . "&res=nvc");
    exit;
}