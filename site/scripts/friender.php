<?php

include_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if (\Users\UserAgent::SessionContinue() !== true){
    header("Location: ../../profile.php?res=nsi");
    exit;
}

$user = new \Users\User($_SESSION["uid"]);

if (isset($_POST["profile-friend-add-btn"])){
    if (empty($_POST["profile-friend-nickname-add-input"])){
        header("Location: ../../profile.php?page=fadd&res=unins");
        exit;
    }
    if (!\Users\UserAgent::GetUserId($_POST["profile-friend-nickname-add-input"])){
        header("Location: ../../profile.php?page=fadd&res=uine");
        exit;
    }

    if (!$user->FriendList()->addFriend(\Users\UserAgent::GetUserId($_POST["profile-friend-nickname-add-input"]))){
        header("Location: ../../profile.php?page=fadd&res=cnaf");
        exit;
    } else {
        $ntf = new \Users\UserNotificator(\Users\UserAgent::GetUserId($_POST["profile-friend-nickname-add-input"]));
        $ntf->createNotify(2, $user->getId());
        header("Location: ../../profile.php?page=friends&res=uhbatfl");
        exit;
    }
}

header("Location: ../../profile.php");
exit;