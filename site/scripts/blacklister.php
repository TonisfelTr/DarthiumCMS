<?php

use Engine\Engine;
use Engine\LanguageManager;
use Guards\SocietyGuard;
use Users\Models\User;
use Users\Services\FlashSession;
use Users\UserAgent;

/** Response list:
 *  1. nsnfb - not sended nickname for blacklisting.
 *  2. une - user not exists.
 *  3. uiab - user is already blacklisted.
 *  4. uhbb - user has been blocked;
 *  5. uhnbb - user has not been blocked;
 *
 *  For deleting:
 *  6. uinb - user is not blocked;
 *  7. uhbub - user has been unblocked;
 *  8. cnuu - cannot unblock user;
 */
include_once "../../engine/engine.php";
Engine::LoadEngine();

$session = UserAgent::SessionContinue();
if ($session !== TRUE){
    FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.you_are_not_authorized"), FlashSession::MA_ERRORS);
    header("Location: ../../profile.php");
    exit;
}

$user = new User(@UserAgent::getCurrentSession()->getContent()["uid"]);

if (SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if (isset($_REQUEST["profile-blacklist-add"])){
    if (empty($_REQUEST["profile-edit-blacklist-nickname"])){
        //Not sended nickname
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.nickname_had_not_been_received_for_blocking"), FlashSession::MA_ERRORS);
    }
    if (!\Users\UserAgent::GetUserId($_REQUEST["profile-edit-blacklist-nickname"])){
        //User does not exits.
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.receiver_with_this_nickname_does_not_exist"), FlashSession::MA_ERRORS);
    }
    if ($user->Blacklister()->isBlocked(\Users\UserAgent::GetUserId($_REQUEST["profile-edit-blacklist-nickname"]))){
        //User is already blocked.
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.user_is_already_blocked"), FlashSession::MA_ERRORS);
    }
    if ($user->Blacklister()->add(\Users\UserAgent::GetUserId($_REQUEST["profile-edit-blacklist-nickname"]), @$_REQUEST["profile-edit-blacklist-comment"])){
        //User has been blocked.
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.user_has_been_blocked_successfully"), FlashSession::MA_INFOS);
    } else {
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.user_blocking_failed"), FlashSession::MA_INFOS);
    }
}

if (isset($_REQUEST["buid"])){
    if (!$user->Blacklister()->isBlocked($_REQUEST["buid"])){
        //User is not blocked;
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.user_blocking_failed"), FlashSession::MA_ERRORS);
    }
    if ($user->Blacklister()->remove($_REQUEST["buid"])){
        //User has been unblocked;
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.user_has_been_unblocked"), FlashSession::MA_INFOS);
    } else {
        //Cannot unblocked user;
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.cannot_unblock_user"), FlashSession::MA_ERRORS);
    }
}

header("Location: ../../profile.php?page=blacklist");
exit;