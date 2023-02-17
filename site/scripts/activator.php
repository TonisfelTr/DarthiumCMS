<?php

use Engine\Engine;
use Engine\LanguageManager;
use Guards\SocietyGuard;
use Users\Models\User;
use Users\UserAgent;
use Users\Services\FlashSession;

require_once "../../engine/engine.php";
Engine::LoadEngine();

if (SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

if (isset($_REQUEST["profile-activation-code-send-btn"])) {
    $session = UserAgent::SessionContinue();
    if ($session === true) {
        $user = new User(UserAgent::getCurrentSession()->getContent()["uid"]);

        if (!empty($_REQUEST["activate"])) {
            $activateCode = $_REQUEST["activate"];
        } elseif (!empty($_REQUEST["profile-activation-code-input"])) {
            $activateCode = $_REQUEST["profile-activation-code-input"];
        }

        if (!empty($_REQUEST["uid"])) {
            $uid = $_REQUEST["uid"];
        } else {
            $uid = $user->getSession()->getContent()["uid"];
        }

        if (empty($activateCode)) {
            FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.activation_code_does_not_sended"), FlashSession::MA_ERRORS);
            header("Location: ../../profile.php?activate");
            exit;
        }

        if (UserAgent::ActivateAccount($uid, $activateCode)) {
            FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.account_has_been_activated_successfully"), FlashSession::MA_INFOS);
            header("Location: ../../profile.php?uid=" . $uid);
            exit;
        } else {
            FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.invalid_activation_code"), FlashSession::MA_ERRORS);
            header("Location: ../../profile.php?activate");
            exit;
        }
    }
}


if (isset($_REQUEST["profile-activation-cancel-btn"])){
    \Users\UserAgent::SessionDestroy();
    header("Location: ../../profile.php");
    exit;
}