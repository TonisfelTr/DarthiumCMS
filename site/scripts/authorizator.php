<?php

use Engine\Engine;
use Engine\LanguageManager;
use Users\Services\FlashSession;
use Users\UserAgent;

/**
 * Authorization script.
 * Authorization script work for four conditions:
 * 1. If the account is not active - redirect to activation page.
 * 2. If the account is banned - redirect to ban welcome page.
 * 3. If enter to the account have been successefuly redirect to profile page.
**/
include "../../engine/classes/engine/Engine.php";
Engine::LoadEngine();

/**
 * Authorization does those tests:
 * 1. If email or nickname contains invalid characters (and if it's invalid anyway) will send errors #22 or #21, respectively.
 * 2. If authorization has been failed by MySQL query reasons will send #9 error.
 * 3. If account is not active will send #26 error.
 * 4. If data is invalid (ID or PWD) SessionCreate function will send #25 error.
 */

if (empty($_REQUEST["profile-auth-uid"])){
    FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.uid_does_not_sended"), FlashSession::MA_ERRORS);
    header("Location: ../../profile.php");
    exit;
}
if (empty($_REQUEST["profile-auth-password"])){
    FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.uid_does_not_sended"), FlashSession::MA_ERRORS);
    header("Location: ../../profile.php");
    exit;
}

if (UserAgent::SessionCreate($_REQUEST["profile-auth-uid"], $_REQUEST["profile-auth-password"]) === true) {
    header("Location: ../../profile.php?uid=" . (new \Users\Services\Session(\Users\Services\FlashSession::getSessionId()))->getContent()["uid"]);
    exit;
}



