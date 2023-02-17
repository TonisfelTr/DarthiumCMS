<?php

use Engine\LanguageManager;
use Users\Models\User;
use Users\Notificator;
use Users\Services\FlashSession;
use Users\UserAgent;

include_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

if (\Users\UserAgent::SessionContinue() !== true){
    FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.you_are_not_authorized"), FlashSession::MA_ERRORS);
    header("Location: ../../profile.php");
    exit;
}

$user = new User(@UserAgent::getCurrentSession()->getContent()["uid"]);

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if (isset($_POST["profile-friend-add-btn"])){
    if (empty($_POST["profile-friend-nickname-add-input"])){
        //User nickname does not set.
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.nickname_had_not_been_received_for_add_to_friendlist"), FlashSession::MA_ERRORS);
    }
    if (!\Users\UserAgent::GetUserId($_POST["profile-friend-nickname-add-input"])){
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.nickname_does_not_exist"), FlashSession::MA_ERRORS);
    }

    if (!$user->FriendList()->addFriend(\Users\UserAgent::GetUserId($_POST["profile-friend-nickname-add-input"]))){
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.cannot_add_user_to_friendlist"), FlashSession::MA_ERRORS);
    } else {
        $ntf = new \Users\Notificator(\Users\UserAgent::GetUserId($_POST["profile-friend-nickname-add-input"]));
        $ntf->createNotify(2, $user->getId());
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.user_has_been_added_to_friendlist"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=friends");
        exit;
    }

    header("Location: ../../profile.php?page=fadd");
    exit;
}

header("Location: ../../profile.php");
exit;