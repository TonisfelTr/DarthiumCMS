<?php
/* Errors code:
 * 4sdu - [user] has been [deleted] [successefuly].
 * 4sbu - [user] has been [banned] [successefuly].
 * 4nbu - [user] has been [not] [banned].
 * 4ndu - [user] has not been [deleted].
 * 4suu - [user] has been [unbanned] [successefuly].
 * 4nuu - [user] has [not] been [unbanned].
 * 4nibu - [user] [is] [not] [banned].
 * 4nbeu - [user] is [not] [exist] for [banning].
 * 4ndus - [deleted] [users] are [not] [setted].
 * 4nihs - [ip] address [had] [not] been [sended].
 * 4sib - [ip] address has been [banned] [successefuly].
 * 4nib - [ip] address has [not] been [banned].
 * 4niab - [ip] address is [already] [banned].
 * 4nibe - [ip] address has [not] been [banned] because of error.
 * 4siub - [ip] address has been [un][banning] [successefuly].
 * 4niub - [ip] address has [not] been [un][banning].
 *
 * 4nrnn - [registration] has [not] been successefully: [nickname] is empty.
 * 4nrp - [registration] has [not] been successefully: [password] is empty.
 * 4nre - [registration] has [not] been successefully: [email] is empty.
 * 4sru - [user] has been [registred] [successefully].
 * 4srue - [user] has been [registred] [successefully] but with [errors].
 * 4nru - [user] has [not] been [registred].
 * 4nvnn - [not] [valid] [nickname].
 * 4nve - [not] [valid] [email].
 * 4nnee - those [nickname] and [email] are already [exist].
 * 4nne - this [nickname] is already [exist]
 */

function concateWithArrow($first, $second){
    return $first . " -> " . $second;
}
require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (isset($_POST["users-find-button"]) || isset($_POST["fpage"])){
    if ($user->UserGroup()->getPermission("user_see_foreign")){
        $backRequest = "Location: ../../adminpanel.php?p=users";
        if (isset($_POST["fgroup"]) && $_POST["fgroup"] != 0) $backRequest .= "&fgroup=".$_POST["fgroup"];
        if (isset($_POST["user-data-input"])) {
            if (trim($_POST["user-data-input"]) != "") {
                if (!isset($_POST["paramType"])){ header("Location: ../../adminpanel.php?res=1"); exit; }
                if (isset($_POST["paramType"]) && $_POST["paramType"] == "nickname") $backRequest .= "&fnn=" . $_POST["user-data-input"];
                if (isset($_POST["paramType"]) && $_POST["paramType"] == "referer") $backRequest .= "&frid=" . str_replace("*", "", $_POST["user-data-input"]);
                if (isset($_POST["paramType"]) && $_POST["paramType"] == "ip") $backRequest .= "&flip=" . $_POST["user-data-input"];
                if (isset($_POST["paramType"]) && $_POST["paramType"] == "email") $backRequest .= "&femail=" . $_POST["user-data-input"];
            }
        }
        if (isset($_POST["fpage"])) $backRequest .= "&fpage=" . $_POST["fpage"];
        header($backRequest);
        exit;
    } else { header("Location: ../../adminpanel.php?p=users&res=1"); exit; }
}

if (isset($_POST["users-delete-button"])){
    if ($user->UserGroup()->getPermission("user_remove")) {
        if (isset($_GET["duids"])){
            $indexer = 0;
            $deleteUIDs = explode(",", $_GET["duids"]);
            for ($i = 0; $i < count($deleteUIDs); $i++){
                $userNickname = \Users\UserAgent::GetUserNick($deleteUIDs[$i]);
                if ($user->getId() == $deleteUIDs[$i] || $deleteUIDs[$i] == 1){
                    header("Location: ../../adminpanel.php?p=users&res=4ncdu");
                    exit;
                }
                if (\Users\UserAgent::DeleteUser($deleteUIDs[$i]) !== True) {
                    $indexer += 1;
                }
                if ($i+1 == count($deleteUIDs)) {
                    \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.delete_user_log") . "$userNickname.");
                    header("Location: ../../adminpanel.php?p=users&res=4sdu");
                    exit;
                }
            }
            if ($indexer == count($deleteUIDs)){
                header("Location: ../../adminpanel.php?p=users&res=4ndu");
                exit;
            }
        } else{ header("Location: ../../adminpanel.php?p=users&res=4ndus"); exit; }
    } else { header("Location: ../../adminpanel.php?res=1"); exit; }
}

if (isset($_POST["user_ban_ban"])){
    if ($user->UserGroup()->getPermission("user_ban")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=1";
        if (!isset($_POST["user_ban_time"])){ header("Location: ../../adminpanel.php?p=users&reqtype=1&res=4nvbt"); exit; }
        if (isset($_POST["user_ban_input"])) {
            //Checking for *
            $needSearch = (strpos($_POST["user_ban_input"], "*") > -1) ? True : False;
            if ($needSearch === False) {
                $backRequest .= "&bnn=" . $_POST["user_ban_input"];
                if (\Users\UserAgent::GetUserId($_POST["user_ban_input"]) == 7) {
                    $backRequest .= "&res=4nbeu";
                    exit;
                }
                if (\Guards\SocietyGuard::Ban(\Users\UserAgent::GetUserId($_POST["user_ban_input"]), (isset($_POST["user_ban_reason"])) ? $_POST["user_ban_reason"] : "none",
                        (isset($_POST["user_ban_time"]) && $_POST["user_ban_time"] >= 0) ? $_POST["user_ban_time"] : 0, $user->getId()) === true) {
                    $backRequest .= "&res=4sbu";
                    \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.ban_user_log") . $_POST["user_ban_input"] . ".");
                }
                elseif (\Engine\ErrorManager::GetError() == 5) $backRequest .= "&res=4nibu";
                elseif (\Engine\ErrorManager::GetError() == 7) $backRequest .= "&res=4nbeu";
                else $backRequest .= "&res=4nbu";
            } else {
                $backRequest .= "&bnns=" . $_POST["user_ban_input"];
                if (\Guards\SocietyGuard::BanWithSearch($_POST["user_ban_input"], (isset($_POST["user_ban_reason"])) ? $_POST["user_ban_reason"] : "none",
                        (isset($_POST["user_ban_time"]) && $_POST["user_ban_time"] >= 0) ? $_POST["user_ban_time"] : 0, $user->getId()) === True)
                    $backRequest .= "&res=4sbus";
                else $backRequest .= "&res=4nbus";
            }
        }
        if (isset($_POST["fbpage"])) $backRequest .= "&fbpage=" . $_POST["fpage"];
        header($backRequest);
       exit;
    } else { header("Location: ../../adminpanel.php?res=1"); exit;}
}

if (isset ($_POST["user_ban_unban"])) {
    if ($user->UserGroup()->getPermission("user_unban")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=1";
        if( isset ($_GET["ufuban"])) {
            $unbanUsers = explode(",", $_GET["ufuban"]);
            for ($i = 0; $i <= count($unbanUsers)-1; $i++){
                if (\Guards\SocietyGuard::Unban($unbanUsers[$i]) !== true){
                    if (\Engine\ErrorManager::GetError() == 5) $backRequest .= "&res=4nibu";
                    elseif (\Engine\ErrorManager::GetError() == 6) $backRequest .= "&res=4nbeu";
                    else $backRequest .= "&res=4nuu";
                    header($backRequest);
                    exit;
                } else {
                    $unbanAccountNickname = \Users\UserAgent::GetUserNick($unbanUsers[$i]);
                    \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.log.unban_user_log") . "$unbanAccountNickname.");
                }
            }
            $backRequest .= "&res=4suu";
            header($backRequest);
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?res=1");
        exit;
    }
}

if (isset ($_POST["user_ban_find"])){
    $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=1";
    if (isset($_POST["fbpage"])) $backRequest .= "&bpage=" . $_POST["bpage"];
    if (!empty($_POST["user_ban_input"])) $backRequest .= "&fbnn=" . $_POST["user_ban_input"];
    if (!empty($_POST["user_ban_reason"])) $backRequest .= "&fbr=" . $_POST["user_ban_reason"];
    header($backRequest);
    exit;
}

if (isset ($_POST["user_bip_ban"])){
    if ($user->UserGroup()->getPermission("user_banip")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=2";
        if (!empty($_POST["user-banip-input"])){
            if(\Guards\SocietyGuard::BanIP($_POST["user-banip-input"], (empty($_POST["user-banip-reason"])) ? "none" : $_POST["user-banip-reason"],
                (empty($_POST["user-banip-time"])) ? 0 : $_POST["user-banip-time"], $user->getId()) === TRUE){
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.banip_log") . $_POST["user-banip-input"] . ".");
                $backRequest .= "&res=4sib";
                header($backRequest);
                exit;
            } elseif (\Engine\ErrorManager::GetError() == 5) {
                $backRequest .= "&res=4niab";
                header($backRequest);
                exit;
            } elseif (\Engine\ErrorManager::GetError() == 9) {
                $backRequest .= "&res=4nibe";
                header($backRequest);
                exit;
            } else {
                $backRequest .= "&res=4nib";
                header($backRequest);
                exit;
            }
        } else {
            $backRequest .= "&res=4nihs";
            header($backRequest);
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?res=1");
        exit;
    }
}

if (isset ($_POST["user_bip_unban"])){
    if ($user->UserGroup()->getPermission("user_unbanip")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=2";
        if (!empty($_GET["ipuban"])) {
            $unipBans = explode(",", $_GET["ipuban"]);
            for ($i = 0; $i <= count($unipBans)-1; $i++) {
                if (\Guards\SocietyGuard::UnbanIP($unipBans[$i]) === FALSE) {
                    $backRequest .= "&res=4niub";
                    header($backRequest);
                    exit;
                }
            }
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.log_unbanip_log") . $unipBans[$i] . ".");
            $backRequest .= "&res=4siub";
            header($backRequest);
            exit;
        } else {
            $backRequest .= "&res=4nibe";
            header($backRequest);
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=users&res=1&reqtype=2");
        exit;
    }
}

if (isset ($_POST["user-add-add"])){
    if ($user->UserGroup()->getPermission("user_add")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=3";
        if (empty($_POST["user-add-nickname"])){
            $backRequest .= "&res=4nrnn";
            header($backRequest);
            exit;
        }
        elseif (empty($_POST["user-add-password"])){
            $backRequest .= "&res=4nrp";
            header($backRequest);
            exit;
        }
        elseif (empty($_POST["user-add-email"])){
            $backRequest .= "&res=4nre";
            header($backRequest);
            exit;
        } else {
            $group = (!isset($_POST["user-add-group"]) || !$user->UserGroup()->getPermission("change_user_group")) ?
                \Engine\Engine::GetEngineInfo("sg") : ($_POST["user-add-group"] == 0) ? \Engine\Engine::GetEngineInfo("sg") : $_POST["user-add-group"];
            if (\Users\UserAgent::AddUser($_POST["user-add-nickname"], $_POST["user-add-password"], $_POST["user-add-email"], $user->getNickname()) === TRUE) {
                $newUser = new \Users\User(\Users\UserAgent::GetUserId($_POST["user-add-nickname"]));
                $newUser->groupChange($group);
                $newUser->Activate();
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.reg_new_user_log") . $_POST["user-add-nickname"] . ".");
                $backRequest .= "&res=4sru&nunn=" . $_POST["user-add-nickname"];
                header($backRequest);
                exit;
            } elseif (\Engine\ErrorManager::GetError() == 21) {
                $backRequest .= "&res=4nvnn";
                header($backRequest);
                exit;
            } elseif (\Engine\ErrorManager::GetError() == 22) {
                $backRequest .= "&res=4nve";
                header($backRequest);
                exit;
            } elseif (\Engine\ErrorManager::GetError() == 3) {
                $backRequest .= "&res=4nnee";
                header($backRequest);
                exit;
            } elseif (\Engine\ErrorManager::GetError() == 4) {
                $backRequest .= "&res=4nne";
                header($backRequest);
                exit;
            } else {
                $backRequest .= "&res=4nru";
                header($backRequest);
                exit;
            }
        }
    } else {
        header("Location: ../../adminpanel.php?p=users&res=1");
        exit;
    }
}

if (!empty ($_GET["uide"])){
    header("Location: ../../adminpanel.php?p=users&uid=". $_GET["uide"]);
    exit;
}

if (isset ($_POST["user-edit-save"])){
    if ($user->UserGroup()->getPermission("change_another_profiles")){
        $backRequest = "Location: ../../adminpanel.php?p=users&uid=" . $_POST["user-edit-id"];
        $eUser = new \Users\User($_POST["user-edit-id"]);
        //Change nickname
        if ($eUser->getNickname() != $_POST["user-edit-nickname"]) {
            $res = \Users\UserAgent::ChangeUserParams($eUser->getId(), "nickname", $_POST["user-edit-nickname"]);
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_nickname_log") . $_POST["user-edit-nickname"] ." [".
            $eUser->getNickname() . " -> " . $_POST["user-edit-nickname"] . ".");
            if ($res === 21)
                $backRequest .= "&res=4nenvn";
            elseif ($res === 4)
                $backRequest .= "&res=4neenn";
            elseif ($res === 7)
                    $backRequest .= "&res=4neu";
            elseif ($res === 22)
                $backRequest .= "&res=4nve";
            elseif ($res === 34)
                $backRequest .= "&res=4neae";
        }
        //Change password
        if (!empty($_POST["user-edit-password"])) {
            $res = $eUser->passChange($_POST["user-edit-password"],false);
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_password_log") . $eUser->getNickname() . ".");
            if ($res === false)
                $backRequest .= "&res=4nep";
            elseif ($res === 7)
                $backRequest .= "&res=4neu";
        }
        //Change email
        if ($eUser->getEmail() != $_POST["user-edit-email"]) {
            $res = \Users\UserAgent::ChangeUserParams($eUser->getId(), "email", $_POST["user-edit-email"]);
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_email_log") . $eUser->getNickname() .
                " [" . $eUser->getEmail() . " -> " . $_POST["user-edit-email"] . ".");
            if ($res === 22) $backRequest .= "&res=4neve";
            elseif ($res === 4) $backRequest .= "&res=4neee";
        }
        //Change group
        if ($user->UserGroup()->getPermission("change_user_group")) {
            if ($eUser->getGroupId() != $_POST["user-edit-group"]) {
                $groupFromName = $eUser->UserGroup()->getName();
                $groupToName = \Users\GroupAgent::GetGroupNameById($_POST["user-edit-group"]);
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_group_log") . $eUser->getNickname() .
                " [$groupFromName -> $groupToName]" );
                $eUser->groupChange($_POST["user-edit-group"]);
            }
        }
        //Change from
        if ($eUser->getFrom() != $_POST["user-edit-from"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "city", $_POST["user-edit-from"])) {
                $backRequest .= "&res=4nef";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_from_log") . $eUser->getNickname()
                . "[" . \Engine\LanguageManager::GetTranslation("users_panel.logs.from_part"). " " .  \Engine\LanguageManager::GetTranslation("users_panel.logs.from_part") . " " . concateWithArrow($eUser->getFrom(), $_POST["user-edit-form"]) . "]");
        }
        //Change VK
        if ($eUser->getVK() != $_POST["user-edit-vk"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "vk", $_POST["user-edit-vk"])) {
                $backRequest .= "&res=4nev";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_vk_id_log") . $eUser->getNickname() .
                    "[" . concateWithArrow($eUser->getVK(), $_POST["user-edit-vk"]) . "]");
        }
        //Change skype login
        if ($eUser->getSkype() != $_POST["user-edit-skype"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "skype", $_POST["user-edit-skype"])) {
                $backRequest .= "&res=4nes";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_skype_id_log") . $eUser->getNickname()
                    ."[" . concateWithArrow($eUser->getSkype(), $_POST["user-edit-skype"]) . "]");
        }
        //Change sex
        if ($eUser->getSex() != $_POST["user-edit-sex"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "sex", $_POST["user-edit-sex"])) {
                $backRequest .= "&res=4nesx";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_sex_log") . $eUser->getNickname() .
                    "[" . concateWithArrow($eUser->getSex(), $_POST["user-edit-sex"]) . "]");
        }
        //Change real name
        if ($eUser->getRealName() != $_POST["user-edit-realname"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "realname", $_POST["user-edit-realname"])) {
                $backRequest .= "&res=4nern";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_realname_log") . $eUser->getNickname()
                    ."[" . concateWithArrow($eUser->getRealName(), $_POST["user-edit-realname"]) . "]");
        }
        //Change birthday
        if ($eUser->getBirth() != $_POST["user-edit-birthday"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "birth", $_POST["user-edit-birthday"])) {
                $backRequest .= "&res=4nebd";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_birthday_log") . $eUser->getNickname() .
                    "[" . concateWithArrow($eUser->getBirth(), $_POST["user-edit-birthday"]) . "]");
        }
        //Change hobbies
        if ($eUser->getHobbies() != $_POST["user-edit-hobbies"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "hobbies", $_POST["user-edit-hobbies"])) {
                $backRequest .= "&res=4nehs";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_hobbies_log") . $eUser->getNickname()
                    ."[" . concateWithArrow($eUser->getHobbies(), $_POST["user-edit-hobbies"]) . "]");
        }
        //Change about
        if ($eUser->getAbout() != $_POST["user-edit-about"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "about", $_POST["user-edit-about"])) {
                $backRequest .= "&res=4nea";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_about_log") . $eUser->getNickname() .
                    "[" . concateWithArrow($eUser->getAbout(), $_POST["user-edit-about"]) . "]");
        }
        //Change signature
        if ($eUser->getSignature() != $_POST["user-edit-signature"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "signature", $_POST["user-edit-signature"])) {
                $backRequest .= "&res=4nesg";
            } else
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_user_signature_log") . $eUser->getNickname()
                    ."[" . concateWithArrow($eUser->getSignature(), $_POST["user-edit-signature"]) . "]");
        }
        $adFields = \Users\UserAgent::GetAdditionalFieldsList();
        foreach ($adFields as $field){
            if ($_POST["user-edit-" . $field["id"]] != \Users\UserAgent::GetAdditionalFieldContentOfUser($eUser->getId(), $field["id"])) {
                \Users\UserAgent::SetAdditionalFieldContent($eUser->getId(), $field["id"], $_POST["user-edit-" . $field["id"]]);
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.change_part") . $field["name"] . \Engine\LanguageManager::GetTranslation("users_panel.logs.user_part") . $eUser->getNickname() . ".");
            }
        }
        if (isset($_FILES["user-edit-avatar"]) && $_FILES["user-edit-avatar"]["size"] > 0) {
            echo 1;
            $res = \Users\UserAgent::UploadAvatar($eUser->getId(), "user-edit-avatar");
            if ($res === False) $backRequest .= "&res=4neav";
            elseif ($res === 18) $backRequest .= "&res=4neavvf";
            elseif ($res === 19) $backRequest .= "&res=4neavvs";
            elseif ($res === 20) $backRequest .= "&res=4neavvb";
        }
        If (strpos($backRequest, "&res") === false) $backRequest .= "&res=4seu";
        if ($user->getId() != $eUser->getId() && strpos($backRequest, "4s")) $eUser->Notifications()->createNotify(3, $user->getId());
        header($backRequest);
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=users&uid=" . $_POST["user-edit-id"] . "&res=1");
        exit;
    }
}

if (isset ($_POST["user-edit-activate"])){
    if ($user->UserGroup()->getPermission("change_another_profiles")){
        $eUser = new \Users\User($_POST["user-edit-id"]);
        if ($eUser->Activate()){
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("users_panel.logs.activate_user_log") . $eUser->getNickname() . ".");
            header("Location: ../../adminpanel.php?p=users&uid=". $eUser->getId() . "&res=4sua");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=users&uid=". $eUser->getId() . "&res=4nua");
            exit;
        }
    }
}
header("Location: ../../adminpanel.php?p=forbidden");
exit;