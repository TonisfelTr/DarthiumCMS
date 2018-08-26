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
require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (isset($_REQUEST["users-find-button"]) || isset($_REQUEST["fpage"])){
    if ($user->UserGroup()->getPermission("user_see_foreign")){
        $backRequest = "Location: ../../adminpanel.php?p=users";
        if (isset($_REQUEST["fgroup"]) && $_REQUEST["fgroup"] != 0) $backRequest .= "&fgroup=".$_REQUEST["fgroup"];
        if (isset($_REQUEST["user-data-input"])) {
            if (trim($_REQUEST["user-data-input"]) != "") {
                if (!isset($_REQUEST["paramType"])){ header("Location: ../../adminpanel.php?res=1"); exit; }
                if (isset($_REQUEST["paramType"]) && $_REQUEST["paramType"] == "nickname") $backRequest .= "&fnn=" . $_REQUEST["user-data-input"];
                if (isset($_REQUEST["paramType"]) && $_REQUEST["paramType"] == "referer") $backRequest .= "&frid=" . str_replace("*", "", $_REQUEST["user-data-input"]);
                if (isset($_REQUEST["paramType"]) && $_REQUEST["paramType"] == "ip") $backRequest .= "&flip=" . $_REQUEST["user-data-input"];
                if (isset($_REQUEST["paramType"]) && $_REQUEST["paramType"] == "email") $backRequest .= "&femail=" . $_REQUEST["user-data-input"];
            }
        }
        if (isset($_REQUEST["fpage"])) $backRequest .= "&fpage=" . $_REQUEST["fpage"];
        header($backRequest);
        exit;
    } else { header("Location: ../../adminpanel.php?p=users&res=1"); exit; }
}

if (isset($_REQUEST["users-delete-button"])){
    if ($user->UserGroup()->getPermission("user_remove")) {
        if (isset($_REQUEST["duids"])){
            $deleteUIDs = explode(",", $_REQUEST["duids"]);
            for ($i = 0; $i < count($deleteUIDs); $i++){
                if ($user->getId() == $deleteUIDs[$i] || $deleteUIDs[$i] == 1){
                    header("Location: ../../adminpanel.php?p=users&res=4ncdu");
                    exit;
                }
                if (\Users\UserAgent::DeleteUser($deleteUIDs[$i]) !== True) {
                    header("Location: ../../adminpanel.php?p=users&res=4ndu");
                    exit;
                }
                if ($i+1 == count($deleteUIDs)) {
                    header("Location: ../../adminpanel.php?p=users&res=4sdu");
                    exit;
                }
            }
        } else{ header("Location: ../../adminpanel.php?p=users&res=4ndus"); exit; }
    } else { header("Location: ../../adminpanel.php?res=1"); exit; }
}

if (isset($_REQUEST["user_ban_ban"])){
    if ($user->UserGroup()->getPermission("user_ban")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=1";
        if (!isset($_REQUEST["user_ban_time"])){ header("Location: ../../adminpanel.php?p=users&reqtype=1&res=4nvbt"); exit; }
        if (isset($_REQUEST["user_ban_input"])) {
            //Checking for *
            $needSearch = (strpos($_REQUEST["user_ban_input"], "*") > -1) ? True : False;
            if ($needSearch === False) {
                $backRequest .= "&bnn=" . $_REQUEST["user_ban_input"];
                if (\Users\UserAgent::GetUserId($_REQUEST["user_ban_input"]) == 7) {
                    $backRequest .= "&res=4nbeu";
                    exit;
                }
                if (\Guards\SocietyGuard::Ban(\Users\UserAgent::GetUserId($_REQUEST["user_ban_input"]), (isset($_REQUEST["user_ban_reason"])) ? $_REQUEST["user_ban_reason"] : "none",
                        (isset($_REQUEST["user_ban_time"]) && $_REQUEST["user_ban_time"] >= 0) ? $_REQUEST["user_ban_time"] : 0, $user->getId()) === true)
                    $backRequest .= "&res=4sbu";
                elseif (\Engine\ErrorManager::GetError() == 5) $backRequest .= "&res=4nibu";
                elseif (\Engine\ErrorManager::GetError() == 7) $backRequest .= "&res=4nbeu";
                else $backRequest .= "&res=4nbu";
            } else {
                $backRequest .= "&bnns=" . $_REQUEST["user_ban_input"];
                if (\Guards\SocietyGuard::BanWithSearch($_REQUEST["user_ban_input"], (isset($_REQUEST["user_ban_reason"])) ? $_REQUEST["user_ban_reason"] : "none",
                        (isset($_REQUEST["user_ban_time"]) && $_REQUEST["user_ban_time"] >= 0) ? $_REQUEST["user_ban_time"] : 0, $user->getId()) === True)
                    $backRequest .= "&res=4sbus";
                else $backRequest .= "&res=4nbus";
            }
        }
        if (isset($_REQUEST["fbpage"])) $backRequest .= "&fbpage=" . $_REQUEST["fpage"];
        header($backRequest);
       exit;
    } else { header("Location: ../../adminpanel.php?res=1"); exit;}
}

if (isset ($_REQUEST["user_ban_unban"]) || isset($_REQUEST["ufuban"]) ) {
    if ($user->UserGroup()->getPermission("user_unban")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=1";
        if( isset ($_REQUEST["ufuban"])) {
            $unbanUsers = explode(",", $_REQUEST["ufuban"]);
            for ($i = 0; $i <= count($unbanUsers)-1; $i++){
                if (\Guards\SocietyGuard::Unban($unbanUsers[$i]) !== true){
                    if (\Engine\ErrorManager::GetError() == 5) $backRequest .= "&res=4nibu";
                    elseif (\Engine\ErrorManager::GetError() == 6) $backRequest .= "&res=4nbeu";
                    else $backRequest .= "&res=4nuu";
                    header($backRequest);
                    exit;
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

if (isset ($_REQUEST["user_ban_find"]) || isset($_REQUEST["bpage"])){
    $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=1";
    if (isset($_REQUEST["fbpage"])) $backRequest .= "&bpage=" . $_REQUEST["bpage"];
    if (!empty($_REQUEST["user_ban_input"])) $backRequest .= "&fbnn=" . $_REQUEST["user_ban_input"];
    if (!empty($_REQUEST["user_ban_reason"])) $backRequest .= "&fbr=" . $_REQUEST["user_ban_reason"];
    header($backRequest);
    exit;
}

if (isset ($_REQUEST["user_bip_ban"])){
    if ($user->UserGroup()->getPermission("user_banip")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=2";
        if (!empty($_REQUEST["user-banip-input"])){
            if(\Guards\SocietyGuard::BanIP($_REQUEST["user-banip-input"], (empty($_REQUEST["user-banip-reason"])) ? "none" : $_REQUEST["user-banip-reason"],
                (empty($_REQUEST["user-banip-time"])) ? 0 : $_REQUEST["user-banip-time"], $user->getId()) === TRUE){
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

if (isset ($_REQUEST["user_bip_unban"]) || isset($_REQUEST["ipuban"])){
    if ($user->UserGroup()->getPermission("user_unbanip")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=2";
        if (!empty($_REQUEST["ipuban"])) {
            $unipBans = explode(",", $_REQUEST["ipuban"]);
            for ($i = 0; $i <= count($unipBans)-1; $i++) {
                if (\Guards\SocietyGuard::UnbanIP($unipBans[$i]) === FALSE) {
                    $backRequest .= "&res=4niub";
                    header($backRequest);
                    exit;
                }
            }
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

if (isset ($_REQUEST["user-add-add"])){
    if ($user->UserGroup()->getPermission("user_add")){
        $backRequest = "Location: ../../adminpanel.php?p=users&reqtype=3";
        if (empty($_REQUEST["user-add-nickname"])){
            $backRequest .= "&res=4nrnn";
            header($backRequest);
            exit;
        }
        elseif (empty($_REQUEST["user-add-password"])){
            $backRequest .= "&res=4nrp";
            header($backRequest);
            exit;
        }
        elseif (empty($_REQUEST["user-add-email"])){
            $backRequest .= "&res=4nre";
            header($backRequest);
            exit;
        } else {
            if (!empty($_REQUEST["user-add-group"])){
                $group = ($_REQUEST["user-add-group"] == 0) ? \Engine\Engine::GetEngineInfo("sg") : $_REQUEST["user-add-group"];
                if (\Users\UserAgent::AddUser($_REQUEST["user-add-nickname"], $_REQUEST["user-add-password"], $_REQUEST["user-add-email"], $user->getNickname()) === TRUE) {
                    $newUser = new \Users\User(\Users\UserAgent::GetUserId($_REQUEST["user-add-nickname"]));
                    $newUser->groupChange($group);
                    $newUser->Activate();
                    $backRequest .= "&res=4sru&nunn=" . $_REQUEST["user-add-nickname"];
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
        }
    } else {
        header("Location: ../../adminpanel.php?p=users&res=1");
        exit;
    }
}

if (!empty ($_REQUEST["uide"])){
    header("Location: ../../adminpanel.php?p=users&uid=". $_REQUEST["uide"]);
    exit;
}

if (isset ($_REQUEST["user-edit-save"])){
    if ($user->UserGroup()->getPermission("change_another_profiles")){
        $backRequest = "Location: ../../adminpanel.php?p=users&uid=" . $_REQUEST["user-edit-id"];
        $eUser = new \Users\User($_REQUEST["user-edit-id"]);
        if ($eUser->getNickname() != $_REQUEST["user-edit-nickname"]) {
            $res = \Users\UserAgent::ChangeUserParams($eUser->getId(), "nickname", $_REQUEST["user-edit-nickname"]);
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
        if (!empty($_REQUEST["user-edit-password"])) {
            $res = $eUser->passChange($_REQUEST["user-edit-password"]);
            if ($res === false)
                $backRequest .= "&res=4nep";
            elseif ($res === 7)
                $backRequest .= "&res=4neu";
        }
        if ($eUser->getEmail() != $_REQUEST["user-edit-email"]) {
            $res = \Users\UserAgent::ChangeUserParams($eUser->getId(), "email", $_REQUEST["user-edit-email"]);
            if ($res === 22) $backRequest .= "&res=4neve";
            elseif ($res === 4) $backRequest .= "&res=4neee";
        }
        if ($user->UserGroup()->getPermission("change_user_group")) {
            if ($eUser->getGroupId() != $_REQUEST["user-edit-group"]) {
                $eUser->groupChange($_REQUEST["user-edit-group"]);
            }
        }
        if ($eUser->getFrom() != $_REQUEST["user-edit-from"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "city", $_REQUEST["user-edit-from"])) {
                $backRequest .= "&res=4nef";
            }
        }
        if ($eUser->getVK() != $_REQUEST["user-edit-vk"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "vk", $_REQUEST["user-edit-vk"])) {
                $backRequest .= "&res=4nev";
            }
        }
        if ($eUser->getSkype() != $_REQUEST["user-edit-skype"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "skype", $_REQUEST["user-edit-skype"])) {
                $backRequest .= "&res=4nes";
            }
        }
        if ($eUser->getSex() != $_REQUEST["user-edit-sex"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "sex", $_REQUEST["user-edit-sex"])) {
                $backRequest .= "&res=4nesx";
            }
        }
        if ($eUser->getRealName() != $_REQUEST["user-edit-realname"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "realname", $_REQUEST["user-edit-realname"])) {
                $backRequest .= "&res=4nern";
            }
        }
        if ($eUser->getBirth() != $_REQUEST["user-edit-birthday"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "birth", $_REQUEST["user-edit-birthday"])) {
                $backRequest .= "&res=4nebd";
            }
        }
        if ($eUser->getHobbies() != $_REQUEST["user-edit-hobbies"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "hobbies", $_REQUEST["user-edit-hobbies"])) {
                $backRequest .= "&res=4nehs";
            }
        }
        if ($eUser->getAbout() != $_REQUEST["user-edit-about"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "about", $_REQUEST["user-edit-about"])) {
                $backRequest .= "&res=4nea";
            }
        }
        if ($eUser->getSignature() != $_REQUEST["user-edit-signature"]) {
            if (!\Users\UserAgent::ChangeUserParams($eUser->getId(), "signature", $_REQUEST["user-edit-signature"])) {
                $backRequest .= "&res=4nesg";
            }
        }
        if (!empty($_REQUEST["user-edit-avatar"])) {
            $res = \Users\UserAgent::UploadAvatar($eUser->getId(), "user-form");
            if ($res === False) $backRequest .= "&res=4neav";
            elseif ($res == 18) $backRequest .= "&res=4neavvf";
            elseif ($res == 19) $backRequest .= "&res=4neavvs";
            elseif ($res == 20) $backRequest .= "&res=4neavvb";
            else $backRequest .= "&res=4neav";
        }
        If (strpos($backRequest, "&res") === false) $backRequest .= "&res=4seu";
        if ($user->getId() != $eUser->getId() && strpos($backRequest, "4s")) $eUser->Notifications()->createNotify(3, $user->getId());
        header($backRequest);
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=users&uid=" . $_REQUEST["user-edit-id"] . "&res=1");
        exit;
    }
}

if (isset ($_REQUEST["user-edit-activate"])){
    if ($user->UserGroup()->getPermission("change_another_profiles")){
        $eUser = new \Users\User($_REQUEST["user-edit-id"]);
        if ($eUser->Activate()){
            header("Location: ../../adminpanel.php?p=users&uid=". $eUser->getId() . "&res=2sua");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=users&uid=". $eUser->getId() . "&res=2nua");
            exit;
        }
    }
}
header("Location: ../../adminpanel.php?p=forbidden");