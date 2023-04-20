<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 05.02.2018
 * Time: 2:35
 */

use Engine\Engine;
use Engine\LanguageManager;
use Guards\SocietyGuard;
use Users\Blacklister;
use Users\Models\User;
use Users\Services\FlashSession;
use Users\UserAgent;

// Errors:
// rins - receiver is not set.
// rine - receiver is not exist.
// nt - no text
// mhnbs - message has not been sended.
// nprm - not permitted to read message.
// nprmm - not permitted to remove message or error of mysql. exception is the first part.
// nmid - no message id.
// yibl - you are in blacklist.
// Successes:
// mhbs - message has been sended.
// mhbr - message has been removed.

include_once "../../engine/engine.php";
Engine::LoadEngine();

if (SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

$session = UserAgent::SessionContinue();
if ($session !== TRUE){
    FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.you_are_not_authorized"), FlashSession::MA_ERRORS);
    header("Location: ../../profile.php");
    exit;
}

$user = new User(@UserAgent::getCurrentSession()->getContent()["uid"]);

//Send message.
if (isset($_REQUEST["send"])){
    if (empty($_POST["profile-pm-receiver-input"])) {
        //No receiver
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.no_receiver_sended"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=wm");
        exit;
    }

    if (!\Users\UserAgent::IsNicknameExists($_POST["profile-pm-receiver-input"])){
        //Receiver does not exist.
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.receiver_with_this_nickname_does_not_exist"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=wm");
        exit;
    }

    if (empty($_POST["profile-pm-text"])){
        //No message text.
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.no_message_text"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=wm");
        exit;
    }

    $bl = new Blacklister(\Users\UserAgent::GetUserId($_POST["profile-pm-receiver-input"]));

    if ($bl->isBlocked($user->getId())){
        //User is blocked.
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.receiver_is_blocked"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=wm");
        exit;
    }

    if ($user->MessageManager()->send(\Users\UserAgent::GetUserId($_POST["profile-pm-receiver-input"]),
        (!empty($_POST["profile-pm-subject-input"])) ? $_POST["profile-pm-subject-input"] : "Без темы",
         $_POST["profile-pm-text"])){
        //Message has been sended.
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.message_has_been_sended"), FlashSession::MA_INFOS);
        header("Location: ../../profile.php?page=ic");
        exit;
    } else {
        //Cannot send message...
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.cannot_send_message"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=wm");
        exit;
    }
}

//Read message.
if (!empty($_REQUEST["r"])) {
    if ($user->MessageManager()->read($_REQUEST["r"]) !== false) {
        header("Location: ../../profile.php?page=rm&mid=" . $_REQUEST["r"]);
        exit;
    } else {
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.not_permitted_to_read_message"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=pm");
        exit;
    }
}

//Quote message.
if (!empty($_REQUEST["q"])){
    $letter = $user->MessageManager()->read($_REQUEST["q"]);
    if ($letter !== false){
        if (!\Users\UserAgent::IsNicknameExists($letter["senderUID"])){
            FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.receiver_with_this_nickname_does_not_exist"), FlashSession::MA_ERRORS);
            header("Location: ../../profile.php?page=wm");
            exit;
        }
        header("Location: ../../profile.php?page=wm&sendTo=" . \Users\UserAgent::GetUserNick($letter["senderUID"]) . "&mid=" . $_REQUEST["q"]);
        exit;
    }
    else {
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.not_permitted_to_read_message"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=pm");
        exit;
    }
}

//Delete message.
if (!empty($_REQUEST["d"])){
    if ($user->MessageManager()->remove($_REQUEST["d"]) === true){
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.message_has_been_removed"), FlashSession::MA_INFOS);
        header("Location: ../../profile.php?page=pm");
        exit;
    } else {
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.not_permitted_to_remove_message"), FlashSession::MA_ERRORS);
        header("Location: ../../profile.php?page=pm");
        exit;
    }
}

if (!empty($_REQUEST["rt"])){
    $result = $user->MessageManager()->restore($_REQUEST["rt"]);
    if ($result) {
        FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.message_has_been_restored"), FlashSession::MA_INFOS);
        header("Location: ../../profile.php?page=pm");
        exit;
    }
}

header("Location: ../../profile.php?page=pm");
exit;