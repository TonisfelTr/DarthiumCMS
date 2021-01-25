<?php
include "engine/main.php";
\Engine\Engine::LoadEngine();
$session = Users\UserAgent::SessionContinue();
$user = false;

function getBrick(){
    $e = ob_get_contents();
    ob_clean();
    return $e;
}

function str_replace_once($search, $replace, $text){
    $pos = strpos($text, $search);
    return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

//print_r($_COOKIE);

###############################################
# Менеджер пользователя.
if ($session === TRUE || !empty($_GET["uid"]))
    $seeProfile = true;
else
    $seeProfile = false;
if ($session === TRUE || $session === 26){
    $user = new \Users\User($_SESSION["uid"]);
    if (!empty($_REQUEST["res"])){
        $response = $_REQUEST["res"];
    }
    $notReadCount = $user->MessageManager()->getNotReadCount();
}

if (!empty($_GET["uid"])){
    if (\Users\UserAgent::IsUserExist($_GET["uid"])) {
        $user = new \Users\User($_GET["uid"]);
    }
    else $user = false;
}

if ($session !== true) {
    $captchaID = \Guards\CaptchaMen::GenerateCaptcha();
    $captchaImgPath = \Guards\CaptchaMen::GenerateImage(\Guards\CaptchaMen::FetchCaptcha(1));
}
################################################
#Менеджер управления панелями профайла:
if ($session !== TRUE){
    if (isset($_REQUEST["signup"])) $q = "reg";
    if (isset($_REQUEST["activate"])) $q = "act";
}
if (empty($q)) $q = "";
#Обработка нужного ввода.
if (\Engine\Engine::GetEngineInfo("na"))
    $uidPlaceholder = \Engine\LanguageManager::GetTranslation("email_or_login");
else $uidPlaceholder = \Engine\LanguageManager::GetTranslation("nickname");
###################################################
#Обработка прямого обращения с активацией:
if (!empty($_REQUEST["activate"]) && !empty($_REQUEST["uid"])){
    header("Location: ./site/scripts/activator.php?activate=".$_REQUEST["activate"]."&uid=".$_REQUEST["uid"]."&profile-activation-code-send-btn");
    exit;
}

if ($user !== false && $user->getId() == $_SESSION["uid"]) {
################################################
# Менеджер личных сообщений.
    if ($session === true && $user->getId() == $_SESSION["uid"]) {
        $letter = false;
        $quoteLetter = false;
        if (!empty($_GET["page"]) && $_GET["page"] == "rm") {
            if (!empty($_GET["mid"])) {
                $letter = $user->MessageManager()->read($_GET["mid"]);
                if ($letter == false) $response = "nprm";
            } else $response = "nmid";
        }

        if (!empty($_GET["page"]) && $_GET["page"] == "wm") {
            if (!empty($_GET["mid"])) {
                $quoteLetter = $user->MessageManager()->read($_GET["mid"]);
                if ($quoteLetter == false) $response = "nprm";
            }
        }
    }

################################################
# Менеджер уведомлений.
    if ($session === true && $user->getId() == $_SESSION["uid"]) {
        $notificationsCount = $user->Notifications()->getNotifiesCount();
        $notNotifiedCount = $user->Notifications()->getNotificationsUnreadCount();
    }
#################################################
# Отображение страниц
    if (!empty($_GET["page"]))
        $page = $_GET["page"];
    else
        $page = "none";
}
ob_start();

$reputationerBlock = "";
$uploadererBlock = "";

include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/main.html";
$main = getBrick();

include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/footer.html";
$footer = getBrick();

include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/header.html";
$header = getBrick();
$header = str_replace_once("{ENGINE_META:SITE_NAME}", \Engine\Engine::GetEngineInfo("sn"), $header);

include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/customscript.js";
$customScript = getBrick();

$main = str_replace_once("{ENGINE_META:DESCRIPTION}", \Engine\Engine::GetEngineInfo("stl"), $main);
$main = str_replace_once("{ENGINE_META:KEYWORDS}", \Engine\Engine::GetEngineInfo("sh"), $main);

$errorCode = 0;
if ($seeProfile){
    if ($session !== true && !empty($user) && !\Engine\Engine::GetEngineInfo("gsp"))
        $errorCode = 1;
    elseif (isset($_GET["uid"]) && !\Users\UserAgent::IsUserExist($_GET["uid"]))
        $errorCode = 2;
    elseif ($session === true && isset($_GET["uid"]) && $_GET["uid"] != $_SESSION["uid"] && !\Users\GroupAgent::IsHavePerm(\Users\UserAgent::GetUserGroupId($_SESSION["uid"]), "user_see_foreign"))
        $errorCode = 3;
    elseif ($session === true && isset($_GET["uid"]) && $_GET["uid"] != $_SESSION["uid"] && $user->Blacklister()->isBlocked($_SESSION["uid"])
        || (!empty($user) && !$user->IsAccountPublic() && $session !== true)
        || (!empty($user) && $user->getId() != $_SESSION["uid"] && !$user->IsAccountPublic() && !$user->FriendList()->isFriend($_SESSION["uid"])))
        $errorCode = 4;
}

/***********************************Block profile page if user is exist.*********************/
if ($session === true && $user !== false && $user->getId() == $_SESSION["uid"]){
    if ($user->isBanned() || \Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
        header("Location: banned.php");
        exit;
    }
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userprofile.html";
    $profileMainPanel = getBrick();
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userscript.js";
    $profileJS = getBrick();
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userinfo.html";
    $userInfo = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/useredit.html";
    $userEdit = getBrick();
    if ($user->UserGroup()->getPermission("change_profile")) {
        $userEdit = str_replace_once("{PROFILE_PAGE:USER_EDITPANEL_BTN}", "<button type=\"button\" id=\"profile-edit-btn-custom-panel\" class=\"profile-profile-panel-btn active\" onclick=\"showSubpanel('edit', 1);\"> ".
            \Engine\LanguageManager::GetTranslation("custom_data"). "</button>", $userEdit);
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userprofileeditcustom.html";
        $profileEditCustomPanel = getBrick();
        $userEdit = str_replace_once("{PROFILE_PAGE:USER_EDITPANEL}", $profileEditCustomPanel, $userEdit);
    }
    include_once "./site/reputationer.php";
    include_once "./site/uploader.php";

    $parentDivName = "";
    $subpanelDivNumber = "";
    switch ($page) {
        case "none":
            $main = str_replace("{PROFILE_JS:SHOW_PANEL}", "", $main);
            break;
        case "edit":
        case "security":
        case "blacklist":
            $parentDivName = "edit";
            switch ($page) {
                case "edit":
                    $subpanelDivNumber = 1;
                    break;
                case "security":
                    $subpanelDivNumber = 2;
                    break;
                case "blacklist":
                    $subpanelDivNumber = 3;
                    break;
                default:
                    $subpanelDivNumber = 1;
                    break;
            }
            break;
        case "pm":
        case "ic":
        case "oc":
        case "sd":
        case "rm":
        case "bin":
        case "wm":
            $parentDivName = "pm";
            switch ($page) {
                case "pm":
                case "ic":
                    $subpanelDivNumber = 1;
                    break;
                case "oc":
                    $subpanelDivNumber = 2;
                    break;
                case "sd":
                    $subpanelDivNumber = 3;
                    break;
                case "rm":
                    $subpanelDivNumber = 6;
                    break;
                case "bin":
                    $subpanelDivNumber = 4;
                    break;
                case "wm":
                    $subpanelDivNumber = 5;
                    break;
                default:
                    $subpanelDivNumber = 1;
                    break;
            }
            break;
        case "news":
            $parentDivName = "notifications";
            $subpanelDivNumber = 1;
            break;
        default:
            $main = str_replace("{PROFILE_JS:SHOW_PANEL}", "", $main);
            break;
    }

    ///////////////////////////////////////////////////////////////////////
    /// Build additional fields mechanism.
    ///////////////////////////////////////////////////////////////////////

    $additionalFields = \Users\UserAgent::GetAdditionalFieldsList();
    $userAdFields = $user->getAdditionalFields();
    $customAF = [];
    $contactAF = [];
    $infoAF = [];
    /********************************************
     * $infoEditAF[] - array with fields to edit info additional fields.
     * And etc.
     *******************************************/
    //var_dump($additionalFields);
    //foreach($additionalFields as $fieldProp){
    for ($i = 0; $i < count($additionalFields); $i++){
        $fieldProp = $additionalFields[$i];
        $id = $fieldProp["id"];
        $fieldName = $fieldProp["name"];
        $tag = "";
        $title = "";
        $closingTag = "";
        $content = htmlentities($userAdFields[$id]["content"]);
        $isPrivate = $userAdFields[$id]["isPrivate"];
        if (strlen($content) > 0){
            if ($fieldProp["link"] != ""){
                $tag = "<a class=\"profile-profile-link\" href=\"" . str_replace("{{1}}", $content, $fieldProp["link"])  ."\"";
                $closingTag = "</a>";
            }
            if ($fieldProp["description"] != ""){
                $title = " title=\"" . $fieldProp["description"] . "\"";
            }
            if ($fieldProp["link"] == "" && $title != ""){
                $tag = "<span";
                $closingTag = "</span>";
            }
            if ($fieldProp["description"] != ""){
                $tag .= $title . "> " . $content . $closingTag;
            }
            if ($tag != "") {
                $result = $fieldName . ": " . $tag . "<br>";
            } else {
                $result = $fieldName . ": " . $content . "<br>";
            }
        } else {
            if ($fieldProp["type"] !== "3")
                $result = $fieldName . ": " . \Engine\LanguageManager::GetTranslation("not_setted"). ".<br>";
            else {
                $value = (\Users\UserAgent::GetAdditionalFieldContentOfUser($user->getId(), $id) == null) ? $fieldProp["custom"] :
                    \Users\UserAgent::GetAdditionalFieldContentOfUser($user->getId(), $id);
                $result = $fieldName . ": " . $value . "<br>";
            }
        }

        $isPrivate = ($isPrivate) ? "checked" : "";
        if ($fieldProp["type"] === "1"){
            $infoAF[] = $result;
            $infoEditAF[] = "<div class=\"profile-profile-edit-area-group\">
                                        <label class=\"profile-label\" for=\"profile-edit-" . $fieldProp["id"] . "\">$fieldName:</label>
                                        <textarea class=\"profile-about-input\" placeholder=\"" . $fieldProp["description"] . "\" id=\"profile-edit-" . $fieldProp["id"] . "\" name=\"profile-edit-" . $fieldProp["id"] . "\" maxlength=\"300\">$content</textarea>
                                    </div>";
        } elseif ($fieldProp["type"] === "2") {
            $contactAF[] = $result;
            $contactEditAF[] = "<div class=\"profile-profile-edit-group\">
                                            <label class=\"profile-label\" for=\"profile-edit-" . $fieldProp["id"] . "\">$fieldName</label>
                                            <input class=\"profile-input\" type=\"text\" id=\"profile-edit-" . $fieldProp["id"] . "\" name=\"profile-edit-" . $fieldProp["id"] . "\" value=\"$content\" placeholder=\"" . $fieldProp["description"] . "\">
                                        </div>";
            $contactSecurityAF[] = "<div class=\"profile-profile-edit-group\">
                                                <label for=\"profile-public-" . $fieldProp["id"] . "\">" . \Engine\LanguageManager::GetTranslation("show"). " $fieldName</label>
                                                <input type=\"checkbox\" id=\"profile-public-" . $fieldProp["id"] . "\" name=\"profile-public-" . $fieldProp["id"] . "\" $isPrivate>
                                            </div>";
        } elseif ($fieldProp["type"] === "3") {
            $customAF[] = $result;
        }
    }

    //Display on main profile page.
    $infoAFJoined = implode("", $infoAF);
    $customAFJoined = implode("", $customAF);
    $contactAFJoined = implode("", $contactAF);
    //Display on change custom profile page.
    @$infoAFEditJoined = implode("", $infoEditAF);
    @$contactAFEditJoined = implode("", $contactEditAF);
    //Display on security profile page.
    @$contactAFSecurityJoined = implode("", $contactSecurityAF);

    $userInfo = str_replace_once("{PROFILE_PAGE:CUSTOM_ADDITIONALS}", $customAFJoined, $userInfo);
    $userInfo = str_replace_once("{PROFILE_PAGE:CONTACT_ADDITIONALS}", $contactAFJoined, $userInfo);
    $userInfo = str_replace_once("{PROFILE_PAGE:INFO_ADDITIONALS}", $infoAFJoined, $userInfo);
    $userEdit = str_replace_once("{PROFILE_PAGE:INFO_ADDITIONALS_EDIT}", $infoAFEditJoined, $userEdit);
    $userEdit = str_replace_once("{PROFILE_PAGE:CONTACT_ADDITIONALS_EDIT}", $contactAFEditJoined, $userEdit);
    $userEdit = str_replace_once("{PROFILE_PAGE:PRIVATE_CONTACTS_EDIT}", $contactAFSecurityJoined, $userEdit);

    //End building.

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Building PMs module.
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userpm.html";
    $userPMs = getBrick();
    if ($letter !== false){
        $userPMs = str_replace_once("{PROFILE_PAGE:USER_READ_PM_BUTTON}", "<button class=\"profile-profile-panel-btn active\" id=\"profile-pm-read-btn\" type=\"button\" onclick=\"showSubpanel('pm', 6);\"><span class=\"glyphicons glyphicons-message-out\"></span> ". \Engine\LanguageManager::GetTranslation("read_message")."</button>", $userPMs);
    } else {
        $userPMs = str_replace_once("{PROFILE_PAGE:USER_READ_PM_BUTTON}", "", $userPMs);
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_INCOMING_COUNT}", $user->MessageManager()->getIncomeSize(), $userPMs);
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_OUTCOMING_COUNT}", $user->MessageManager()->getOutcomeSize(), $userPMs);
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_SENDED_COUNT}", $user->MessageManager()->getSendedSize(), $userPMs);
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_BIN_COUNT}", $user->MessageManager()->getBinSize(), $userPMs);

    if ($user->MessageManager()->getIncomeSize() == 0) {
        $table = "<tr><td class=\"profile-pm-table-empty-td\" colspan=\"6\"><span class=\"glyphicons glyphicons-info-sign\"></span> " . \Engine\LanguageManager::GetTranslation("have_no_new_messages"). "</td></tr>";
    }
    else
    {
        $table = "";
        for ($k = 0; $k < $user->MessageManager()->getIncomeSize(); $k++) {
            $icon = ($user->MessageManager()->incomes()[$k]["isRead"] == false) ? "message-full" : "message-empty";
            $sender = new \Users\User($user->MessageManager()->incomes()[$k]["senderUID"]);
            $subject = $user->MessageManager()->incomes()[$k]["subject"];
            $time = \Engine\Engine::DatetimeFormatToRead($user->MessageManager()->incomes()[$k]["receiveTime"]);
            $id = $user->MessageManager()->incomes()[$k]["id"];
            $table .= "<tr onclick=\"submitForReadLetter($id);\">
                <td class=\"profile-pm-table-icon\"><span class=\"glyphicons glyphicons-$icon\"></span></td>
                <td class=\"profile-pm-table-author\"><a class=\"profile-link\" href=\"profile.php?uid=" . $sender->getId() . ">" . $sender->getNickname() . "</a></td>
                <td class=\"profile-pm-table-subject\">$subject</td>
                <td class=\"profile-pm-table-time\">$time</td>
                <td class=\"profile-pm-table-btn-group\">
                    <button class=\"profile-table-btn\" title=\"". \Engine\LanguageManager::GetTranslation("remove_message")."\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?d=$id\"><span class=\"glyphicons glyphicons-erase\"></span></button>
                    <button class=\"profile-table-btn\" title=\"". \Engine\LanguageManager::GetTranslation("read_message")."\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?r=$id\"><span class=\"glyphicons glyphicons-message-out\"></span></button>
                    <button class=\"profile-table-btn\" title=\"". \Engine\LanguageManager::GetTranslation("answer_to_message")."\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?q=$id\"><span class=\"glyphicons glyphicons-quote\"></span></button>
                </td>
            </tr>";
        }
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_INCOMING_TABLE}", $table, $userPMs);

    if ($user->MessageManager()->getOutcomeSize() == 0) {
        $table = "<tr><td class=\"profile-pm-table-empty-td\" colspan=\"6\"><span class=\"glyphicons glyphicons-info-sign\"></span> ". \Engine\LanguageManager::GetTranslation("have_no_outcome_messages"). "</td></tr>";
    }
    else
    {
        $table = "";
        for ($k = 0; $k < $user->MessageManager()->getOutcomeSize(); $k++){
            $icon = "message-flag";
            $receiver = new \Users\User($user->MessageManager()->outcomes()[$k]["receiverUID"]);
            $subject = $user->MessageManager()->outcomes()[$k]["subject"];
            $time = \Engine\Engine::DatetimeFormatToRead($user->MessageManager()->outcomes()[$k]["receiveTime"]);
            $id = $user->MessageManager()->outcomes()[$k]["id"];
            $table .= "<tr onclick=\"submitForReadLetter($id);\">
                <td class=\"profile-pm-table-icon\"><span class=\"glyphicons glyphicons-$icon\"></span></td>
                <td class=\"profile-pm-table-author\"><a class=\"profile-link\" href=\"profile.php?uid=" . $sender->getId() . ">" . $sender->getNickname() . "</a></td>
                <td class=\"profile-pm-table-subject\">$subject</td>
                <td class=\"profile-pm-table-time\">$time</td>
                <td class=\"profile-pm-table-btn-group\">
                    <button class=\"profile-table-btn\" title=\"".\Engine\LanguageManager::GetTranslation("remove_message")."\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?d=$id\"><span class=\"glyphicons glyphicons-erase\"></span></button>
                    <button class=\"profile-table-btn\" title=\"".\Engine\LanguageManager::GetTranslation("read_message")."\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?r=$id\"><span class=\"glyphicons glyphicons-message-out\"></span></button>
                    <button class=\"profile-table-btn\" title=\"".\Engine\LanguageManager::GetTranslation("answer_to_message")."\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?q=$id\"><span class=\"glyphicons glyphicons-quote\"></span></button>
                </td>
            </tr>";
        }
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_OUTCOMING_TABLE}", $table, $userPMs);

    if ($user->MessageManager()->getSendedSize() == 0) {
        $table = "<tr><td class=\"profile-pm-table-empty-td\" colspan=\"6\"><span class=\"glyphicons glyphicons-info-sign\"></span> Вы пока не отправили ни одного сообщения.</td></tr>";
    }
    else
    {
        $table = "";
        for ($k = 0; $k < $user->MessageManager()->getSendedSize(); $k++) {
            $icon = "inbox-out";
            $receiver = new \Users\User($user->MessageManager()->sended()[$k]["receiverUID"]);
            $subject = $user->MessageManager()->sended()[$k]["subject"];
            $time = \Engine\Engine::DatetimeFormatToRead($user->MessageManager()->sended()[$k]["receiveTime"]);
            $id = $user->MessageManager()->sended()[$k]["id"];
            $table .= "<tr onclick=\"submitForReadLetter($id);\">
                           <td class=\"profile-pm-table-icon\"><span class=\"glyphicons glyphicons-$icon\"></span></td>
                           <td class=\"profile-pm-table-author\"><a class=\"profile-link\" href=\"profile.php?uid=" . $receiver->getId() . "\">" . $receiver->getNickname() . "</a></td>
                           <td class=\"profile-pm-table-subject\">$subject</td>
                           <td class=\"profile-pm-table-time\">$time</td>
                           <td class=\"profile-pm-table-btn-group\">
                               <button class=\"profile-table-btn\" name=\"pm-read\" type=\"submit\" title=\"".\Engine\LanguageManager::GetTranslation("read_message")."\" formaction=\"/site/scripts/pmanager.php?r=$id\"><span class=\"glyphicons glyphicons-message-out\"></span></button>
                               <button class=\"profile-table-btn\" type=\"submit\" title=\"".\Engine\LanguageManager::GetTranslation("remove_message")."\" formaction=\"/site/scripts/pmanager.php?d=$id\"><span class=\"glyphicons glyphicons-erase\"></span></button>
                           </td>
                       </tr>";
        }
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_SENDED_TABLE}", $table, $userPMs);

    if ($user->MessageManager()->getBinSize() == 0) {
        $table = "<tr><td class=\"profile-pm-table-empty-td\" colspan=\"6\"><span class=\"glyphicons glyphicons-info-sign\"></span> ".\Engine\LanguageManager::GetTranslation("empty_bin")."</td></tr>";
    }
    else
    {
        $table = "";
        for ($k = 0; $k < $user->MessageManager()->getBinSize(); $k++) {
            $icon = "bin";
            $sender = new \Users\User($user->MessageManager()->bin()[$k]["senderUID"]);
            $receiver = new \Users\User($user->MessageManager()->bin()[$k]["receiverUID"]);
            $subject = $user->MessageManager()->bin()[$k]["subject"];
            $time = \Engine\Engine::DatetimeFormatToRead($user->MessageManager()->bin()[$k]["receiveTime"]);
            $id = $user->MessageManager()->bin()[$k]["id"];
            $senderId = $sender->getId();
            $receiverId = $receiver->getId();
            $table .= "<tr onclick=\"submitForReadLetter($id);\">
                           <td class=\"profile-pm-table-icon\"><span class=\"glyphicons glyphicons-$icon\"></span></td>
                           <td class=\"profile-pm-table-author\"><a class=\"profile-link\" href=\"profile.php?uid=$senderId\">" . $sender->getNickname() . "</a></td>
                           <td class=\"profile-pm-table-author\"><a class=\"profile-link\" href=\"profile.php?uid=" . $receiverId . "\">" . $receiver->getNickname() . "</a></td>
                           <td class=\"profile-pm-table-subject\">$subject</td>
                           <td class=\"profile-pm-table-time\">$time</td>
                           <td class=\"profile-pm-table-btn-group\">
                               <button class=\"profile-table-btn\" name=\"pm-read\" type=\"submit\" title=\"".\Engine\LanguageManager::GetTranslation("read_message")."\" formaction=\"/site/scripts/pmanager.php?r=$id\"><span class=\"glyphicons glyphicons-message-out\"></span></button>
                               <button class=\"profile-table-btn\" type=\"submit\" title=\"".\Engine\LanguageManager::GetTranslation("restore_message")."\" formaction=\"/site/scripts/pmanager.php?rt=$id\"><span class=\"glyphicons glyphicons-inbox-in\"></span></button>
                           </td>
                       </tr>";
        }
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_BIN_TABLE}", $table, $userPMs);

    $userPMs = str_replace("{PROFILE_PAGE:USER_PM_SENDING_RECEIVER}", (!empty($_GET["sendTo"]) && $page == "wm") ? $_GET["sendTo"] : "", $userPMs);
    $userPMs = str_replace("{PROFILE_PAGE:USER_PM_SENDING_SUBJECT}", ($page == "wm" && !empty($_GET["mid"])) ? "RE: " . $user->MessageManager()->read($_GET["mid"])["subject"] : "", $userPMs);
    $userPMs = str_replace("{PROFILE_PAGE:USER_PM_SENDING_TEXT}", ($page == "wm" && !empty($_GET["mid"])) ? $user->MessageManager()->read($_GET["mid"])["text"] : "", $userPMs);

    if (is_array($letter)) {
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userpm_read.html";
        $userPMReader = getBrick();
        $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_READER}", (!$letter) ? "" : $userPMReader, $userPMs);

        $userPMs = str_replace("{PROFILE_PAGE:USER_PM_FROM}", $letter["senderUID"], $userPMs);
        $userPMs = str_replace("{PROFILE_PAGE:USER_SENDER_NICKNAME}", \Users\UserAgent::GetUserNick($letter["senderUID"]), $userPMs);
        $userPMs = str_replace("{PROFILE_PAGE:USER_PM_TO}", $letter["receiverUID"], $userPMs);
        $userPMs = str_replace("{PROFILE_PAGE:USER_RECIEVER_NICKNAME}", \Users\UserAgent::GetUserNick($letter["receiverUID"]), $userPMs);
        $userPMs = str_replace("{PROFILE_PAGE:USER_PM_SUBJECT}", $letter["subject"], $userPMs);
        $userPMs = str_replace("{PROFILE_PAGE:USER_PM_RECIEVETIME}", \Engine\Engine::DatetimeFormatToRead($letter["receiveTime"]), $userPMs);
        $userPMs = str_replace("{PROFILE_PAGE:USER_PM_TEXT}", \Engine\Engine::CompileBBCode($letter["text"]), $userPMs);
    }
    else
        $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_READER}","", $userPMs);
    //End building.

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Notifications building.
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/usernotifs.html";
    $userNotifics = getBrick();

    if ($user->Notifications()->getNotifiesCount() == 0)
        $userNotificsTable = "<hr><p>" . \Engine\LanguageManager::GetTranslation("no_notifications") . "</p>";
    else {
        $userNotificsTable = "";
        for ($i = 0; $i < $user->Notifications()->getNotifiesCount(); $i++){
            $ntf = $user->Notifications()->getNotifies();
            $userNotificsTable .= "<hr><div class=\"profile-notification-body\">";
            if (!$ntf[$i]["isRead"])
                $userNotificsTable .= "<span class=\"glyphicons glyphicons-info-sign\" style=\"font-size: 25px;\"></span>";
            $userForNotification = new \Users\User($ntf[$i]["fromUid"]);
            $userNotificsTable .= "<a class=\"profile-link\" href=\"profile.php?uid=" . $userForNotification->getId() . "\">" . $userForNotification->getNickname() . "</a>";

            switch ($ntf[$i]["type"]){
                case 1:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("added_to_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("added_to_he");
                    $userNotificsTable .= " " . $prefix . " <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">"
                    . \Engine\LanguageManager::GetTranslation("to_report_room") . "</a> ". \Engine\LanguageManager::GetTranslation("for_discussions");
                    break;
                case 2:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("added_to_friendlist_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("added_to_friendlist_he");
                    $userNotificsTable .= " $prefix.";
                    break;
                case 3:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("changed_profile_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("changed_profile_he");
                    $userNotificsTable .= " $prefix";
                    break;
                case 4:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_from_discussions_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_from_discussions_he");
                    $userNotificsTable .= " $prefix";
                    break;
                case 5:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("add_message_to_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("add_message_to_he");
                    $userNotificsTable .= " $prefix <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">".
                        \Engine\LanguageManager::GetTranslation("to_report_room") . "</a> " . \Engine\LanguageManager::GetTranslation("for_discussions");
                    break;
                case 6:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("add_message_to_your_topic_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("add_message_to_your_topic_he");
                    $userNotificsTable .= " $prefix <a href=\"index.php?topic=" . $ntf[$i]["subject"] . "\">".
                        \Engine\LanguageManager::GetTranslation("to_topic")."</a>";
                    break;
                case 7:
                    $prefix = \Engine\LanguageManager::GetTranslation("like_your_post");
                    $userNotificsTable .= " $prefix <a href=\"index.php?topic=". $ntf[$i]["subject"] . "\">". \Engine\LanguageManager::GetTranslation("topic")."</a >.";
                    break;
                case 8:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("move_your_topic_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("move_your_topic_he");
                    $userNotificsTable .= " $prefix <a href=\"index.php?topic=". $ntf[$i]["subject"] . "\">" . \Engine\LanguageManager::GetTranslation("topic").".</a>.";
                    break;
                case 9:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_your_topic_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_your_topic_he");
                    $userNotificsTable .= " $prefix";
                    break;
                case 10:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("change_text_in_your_report_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("change_text_in_your_report_he");
                    $userNotificsTable .= " $prefix";
                    break;
                case 11:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_your_report_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_your_report_he");
                    $userNotificsTable .= " $prefix";
                    break;
                case 12:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("change_text_in_your_topic_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("change_text_in_your_topic_he");
                    $userNotificsTable .= " $prefix";
                    break;
                case 13:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("change_status_topic_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("change_status_topic_he");
                    $userNotificsTable .= " $prefix";
                    break;
                case 14:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("register_with_referer_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("register_with_referer_he");
                    $userNotificsTable .= " $prefix";
                    break;
                case 15:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("closed_your_report_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("closed_your_report_he");
                    $userNotificsTable .= " $prefix <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">" . \Engine\LanguageManager::GetTranslation("to_report"). "</a>.";
                    break;
                case 16:
                    if ($userForNotification->getSex() == 2) {
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_your_answer_in_report_she");
                        $suffix = \Engine\LanguageManager::GetTranslation("pm_to_know_details_she");
                    }
                    else {
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_your_answer_in_report_he");
                        $suffix = \Engine\LanguageManager::GetTranslation("pm_to_know_details_he");
                    }
                    $userNotificsTable .= " $prefix <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">"
                    . \Engine\LanguageManager::GetTranslation("in_report").".</a>. " . $suffix;
                    break;
                case 17:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("change_your_answer_in_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("change_your_answer_in_he");
                    $userNotificsTable .= " $prefix  <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">" . \Engine\LanguageManager::GetTranslation("to_report"). "</a>.";
                    break;
                case 18:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("added_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("added_he");
                    $suffix = \Engine\LanguageManager::GetTranslation("for_discussions");
                    $nAID = end(explode(",", $ntf[$i]["subject"]));
                    $nANickname = \Users\UserAgent::GetUserNick($nAID);
                    $userNotificsTable .= " $prefix <a href=\"profile.php?uid=$nAID\">$nANickname</a> в <a href=\"index.php?page=report&preg=see&rid=" . reset(explode(",", $ntf[$i]["subject"])) . "\">"
                                        . \Engine\LanguageManager::GetTranslation("to_report_room") ."</a> $suffix.";
                    break;
                case 19:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("remove_he");
                    $nAID = end(explode(",", $ntf[$i]["subject"]));
                    $nANickname = \Users\UserAgent::GetUserNick($nAID);
                    $userNotificsTable .= " $prefix <a href=\"profile.php?uid=$nAID\">$nANickname</a> из <a href=\"index.php?page=report&preg=see&rid=" . reset(explode(",", $ntf[$i]["subject"])) .
                        "\">". \Engine\LanguageManager::GetTranslation("from_room")."</a> ". \Engine\LanguageManager::GetTranslation("for_discussions").".";
                    break;
                case 20:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("close_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("close_he");
                    $nAID = end(explode(",", $ntf[$i]["subject"]));
                    $nANickname = \Users\UserAgent::GetUserNick($nAID);
                    $userNotificsTable .= " $prefix <a href=\"index.php?page=report&rid=".reset(explode(",", $ntf[$i]["subject"]))."\">
                            " . \Engine\LanguageManager::GetTranslation("to_report") . "</a>, ".
                            \Engine\LanguageManager::GetTranslation("created_by") . "<a href=\"profile.php?uid=$nAID\">$nANickname</a>.";
                    break;
                case 21:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("has_mentioned_in_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("has_mentioned_in_he");
                    $subjectNotification = $ntf[$i]["subject"];
                    $userNotificsTable .= " $prefix <a href=\"index.php?topic=$subjectNotification\">". \Engine\LanguageManager::GetTranslation("to_topic"). "</a>.";
                    break;
                case 22:
                    if ($userForNotification->getSex() == 2)
                        $prefix = \Engine\LanguageManager::GetTranslation("has_mentioned_in_she");
                    else
                        $prefix = \Engine\LanguageManager::GetTranslation("has_mentioned_in_he");
                    $comment = new \Forum\TopicComment($ntf[$i]["subject"]);
                    $commentId = $comment->getId();
                    $topicId = $comment->getTopicParentId();
                    $userNotificsTable .= " $prefix <a href=\"index.php?topic=$topicId#comment-$commentId\">" . \Engine\LanguageManager::GetTranslation("in_comment") . "</a>.";
                    break;
            }
            $userNotificsTable .= "<p class=\"profile-notification-time\">" . Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $ntf[$i]["createTime"])) . "</p>";
            $userNotificsTable .= "</div>";
            if (!$ntf[$i]["isRead"]) $user->Notifications()->setRead($ntf[$i]["id"]);
        }
    }

    $userNotifics = str_replace_once("{PROFILE_PAGE:USER_NOTIFICATIONS}", $userNotificsTable, $userNotifics);
    //End building.

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Friendlist building.
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userfriends.html";
    $userFriendList = getBrick();

    if ($user->FriendList()->getFriendsCount() == 0)
        $friendListTable = "<tr><td colspan=\"4\" style=\"text-align: center\">". \Engine\LanguageManager::GetTranslation("no_friends")  . "</td></tr>";
    else {
        $friendListTable = "<tr><td class=\"table-friends-online-td\" colspan=\"4\">" . \Engine\LanguageManager::GetTranslation("friends_online"). "</td></tr>";
        $onlineList = \Users\UserAgent::GetOnlineFriends($user->getId());
        if (\Users\UserAgent::GetOnlineFriendsCount($user->getId()) == 0){
            $friendListTable .= "<tr><td colspan=\"4\" class=\"table-no-online-friends-td\">" . \Engine\LanguageManager::GetTranslation("no_online_friends") . "</td></tr>";
        } else
        for ($i = 0; $i < \Users\UserAgent::GetOnlineFriendsCount($user->getId()); $i++) {
            $friend = new \Users\User($onlineList[$i]);
            $friendListTable .= "<tr>
                                    <td><img class=\"profile-friends-avatar\" src=\"" . $friend->getAvatar() . "\"></td>
                                    <td><a class=\"profile-profile-link\" href=\"profile.php?uid=". $friend->getId() . "\">" . $friend->getNickname() ."</a>
                                    <td>" . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $user->FriendList()->getFriendFromDB($onlineList[$i])["regdate"])) . "</td>
                                    <td></td>
                                 </tr>";
        }
        $friendListTable .= "<tr><td colspan=\"4\" class=\"table-friends-list-td\">" . \Engine\LanguageManager::GetTranslation("other_friends") . "</td></tr>";
        @$offlineList = array_diff($user->FriendList()->getFriendsList(), $onlineList);
        for ($i = 0; $i < count($offlineList); $i++) {
            $friend = new \Users\User($offlineList[$i]["friendId"]);
            $friendListTable .= "<tr>
                                    <td><img class=\"profile-friends-avatar\" src=\"".$friend->getAvatar() ."\"></td>
                                    <td><a class=\"profile-profile-link\" href=\"profile.php?uid=". $friend->getId() . "\">" . $friend->getNickname() ."</a></td>
                                    <td>" . Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $offlineList[$i]["regdate"])) . "</td>
                                    <td></td>
                                 </tr>";
        }
    }

    $userFriendList = str_replace_once("{PROFILE_PAGE:USER_FRIEND_TABLE}", $friendListTable, $userFriendList);

    //End building.

    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}",\Engine\LanguageManager::GetTranslation("profile"), $header);

    $main = str_replace_once("{PROFILE_PAGE_TITLE}", \Engine\LanguageManager::GetTranslation("profile"). " " . $user->getNickname() . " - " . \Engine\Engine::GetEngineInfo("sn"), $main);
    $main = str_replace_once("{PROFILE_PAGE_GUI_SCRIPT}", $profileJS, $main);
    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", "", $main);
    $main = str_replace_once("{PROFILE_MAIN_BODY}",$profileMainPanel, $main);
    $main = str_replace("{PROFILE_PAGE:USER_NICKNAME}", $user->getNickname(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_ID}", $user->UserGroup()->getId(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_COLOR}", ($user->UserGroup()->getColor() == "#000000") ? "#ffffff" : $user->UserGroup()->getColor(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_NAME}", $user->UserGroup()->getName(), $main);
    $lastOnline = 0;
    if ($user->getLastTime() == 0){
        if ($user->getSex() == 2)
            $lastOnline = \Engine\LanguageManager::GetTranslation("not_sign_in_she");
        else
            $lastOnline = \Engine\LanguageManager::GetTranslation("not_sign_in_he");
    }
    else
    {
        if (\Engine\Engine::GetSiteTime() > $user->getLastTime()+15*60) {
            if ($user->getSex() == 2)
                $lastOnline =  \Engine\LanguageManager::GetTranslation("signed_in_she");
            else
                $lastOnline =  \Engine\LanguageManager::GetTranslation("signed_in_he");
            $lastOnline .= " " . \Engine\LanguageManager::GetTranslation("in") . " " .  \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $user->getLastTime()));
        } else {
            $lastOnline = "<span style=\"color: #00dd00;\">". \Engine\LanguageManager::GetTranslation("online"). "</span>";
        }
    }
    $main = str_replace("{PROFILE_PAGE:USER_LASTONLINE}", $lastOnline, $main);
    $main = str_replace_once("{PROFILE_PAGE_INFO}", $userInfo, $main);
    $main = str_replace_once("{PROFILE_PAGE_EDIT}", $userEdit, $main);
    $main = str_replace_once("{PROFILE_PAGE_PM}", $userPMs, $main);
    $main = str_replace_once("{PROFILE_PAGE_NOTIFICS}", $userNotifics, $main);
    $main = str_replace_once("{PROFILE_PAGE_FRIENDS}", $userFriendList, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REGDATETIME}", (($user->getSex() == 2) ? "а " : " ") .\Engine\Engine::DateFormatToRead($user->getRegDate()) . ".", $main);
    $main = str_replace("{PROFILE_PAGE:USER_TOPICS_CREATED_COUNT}", \Forum\ForumAgent::GetCountTopicOfAuthor($user->getId()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_COMMENTS_CREATED_COUNT}", \Forum\ForumAgent::GetCountOfCommentOfUser($user->getId()), $main);
    $main = str_replace_once("{PROFILE_REPUTATIONER:STYLESHEET}", "<link rel=\"stylesheet\" href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/reputationer-style.css\">", $main);
    $main = str_replace_once("{PROFILE_PAGE:USER_REPUTATION_AONCLICK}", "onclick=\"$('#reputation-frame').show();\"", $main);
    $main = str_replace_once("{PROFILE_REPUTATIONER_BLOCK}", $reputationerBlock, $main);
    $main = str_replace_once("{PROFILE_UPLOADER_BLOCK}", $uploaderBlock, $main);
    $main = str_replace("{PROFILE_JS:SHOW_PANEL}", "showPanel('$parentDivName');" . PHP_EOL . "showSubpanel('$parentDivName', $subpanelDivNumber);", $main);

    if ($user->IsVKPublic() || $user->getId() == $_SESSION["uid"])
        $userVKLink = ($user->getVK() == "") ? "VK: " . \Engine\LanguageManager::GetTranslation("not_setted") . "<br>" : "VK: <a class=\"profile-profile-link\" href=\"http://vk.com/".htmlentities($user->getVK())."\">" . htmlentities($user->getVK()) . "</a><br>";
    else $userVKLink = "";
    if ($user->IsBirthdayPublic() || $user->getId() == $_SESSION["uid"])
        $userBirthday = $user->getBirth() == "" ?  \Engine\LanguageManager::GetTranslation("birthday") . ": " . \Engine\LanguageManager::GetTranslation("not_setted")."<br>"
                                                : \Engine\LanguageManager::GetTranslation("birthday") . ": " . htmlentities($user->getBirth()) . "<br>";
    else $userBirthday = "";
    if ($user->IsSkypePublic() || $user->getId() == $_SESSION["uid"])
        $userSkypeLink = $user->getSkype() == "" ? "Skype: " . \Engine\LanguageManager::GetTranslation("not_setted") . "<br>" : "Skype: <a class=\"profile-profile-link\" href=\"skype:". htmlentities($user->getSkype())."?chat\">написать</a><br>";
    else $userSkypeLink = "";
    if ($user->IsEmailPublic() || $user->getId() == $_SESSION["uid"])
        $userEmailLink = "Email: <a class=\"profile-profile-link\" href=\"mailto:".$user->getEmail()."\">" . $user->getEmail() . "</a><br>";
    else $userEmailLink = "";
    if ($user->getReferer() != null)
        $userRefererLink = "Реферер: <a class=\"profile-profile-link\" href=\"profile.php?uid=".$user->getReferer()->getId()."\">". $user->getReferer()->getNickname() . "</a><br>";
    else $userRefererLink = "";
    switch ($user->getSex()){
        case 1:
            $userSex = "<span class=\"glyphicons glyphicons-gender-intersex\"></span> " . \Engine\LanguageManager::GetTranslation("not_setted");
            break;
        case 2:
            $userSex = "<span class=\"glyphicons glyphicons-gender-male\"></span> " . \Engine\LanguageManager::GetTranslation("gender_male");
            break;
        case 3:
            $userSex = "<span class=\"glyphicons glyphicons-gender-female\"></span> " . \Engine\LanguageManager::GetTranslation("gender_female");
            break;
    }
    if ($user->getFrom() == "")
        $userFrom = \Engine\LanguageManager::GetTranslation("not_setted");
    else
        $userFrom = htmlentities($user->getFrom());
    if ($user->getRealName() == "")
        $userRealName = \Engine\LanguageManager::GetTranslation("not_setted");
    else
        $userRealName = htmlentities($user->getRealName());

    $main = str_replace("{PROFILE_PAGE:USER_FROM}", $userFrom, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REALNAME}", $userRealName, $main);
    $main = str_replace("{PROFILE_PAGE:USER_BIRTHDAY_LINK}", $userBirthday, $main);
    $main = str_replace("{PROFILE_PAGE:USER_SEX}", $userSex, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REFERER}", $userRefererLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REP_POINTS}", $user->getReputation()->getReputationPoints() . " " . \Engine\LanguageManager::GetTranslation("point(s)"), $main);
    $main = str_replace("{PROFILE_PAGE:USER_EMAIL}", $userEmailLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_SKYPE_LINK}", $userSkypeLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_VK_LINK}", $userVKLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_HOBBIES}", $user->getHobbies() == "" ? \Engine\LanguageManager::GetTranslation("not_setted") : htmlentities($user->getHobbies()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ABOUT}", $user->getAbout() == "" ? \Engine\LanguageManager::GetTranslation("not_setted") : htmlentities($user->getAbout()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_VK}", $user->getVK(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_SKYPE}", $user->getSkype(), $main);
    if ($user->getSignature() == ""){
        $signature = \Engine\LanguageManager::GetTranslation("not_setted");
    } else {
        $signature = nl2br(html_entity_decode(\Engine\Engine::ChatFilter(\Engine\Engine::CompileBBCode($user->getSignature()))));
    }
    $main = str_replace("{PROFILE_PAGE:USER_SIGNATURE}", $signature, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REPORT_CREATED_COUNT}", $user->getReportsCreatedCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FRIENDS_COUNT}", $user->FriendList()->getFriendsCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ONLINE_FRIENDS_COUNT}", $user->FriendList()->getOnlineFriendCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_REALNAME_TEXT}", htmlentities($user->getRealName()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_BIRTHDAY_TEXT}", htmlentities($user->getBirth()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FROM_TEXT}", htmlentities($user->getFrom()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ABOUT_TEXT}", htmlentities($user->getAbout()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_HOBBIES_TEXT}", htmlentities($user->getHobbies()), $main);
    if ($user->getId() == $_SESSION["uid"]){
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userperrors.phtml";
        $userPageErrors = getBrick();
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/aup_buttons.html";
        $userFootBtns = getBrick();

        $sexOptions = "<option value=\"1\"". (($user->getSex() == 1) ? " selected" : "" ). ">" . \Engine\LanguageManager::GetTranslation("not_setted")."</option>". PHP_EOL .
            "<option value=\"2\"". (($user->getSex() == 2) ? " selected" : "" ). ">".\Engine\LanguageManager::GetTranslation("gender_male")."</option>" . PHP_EOL .
            "<option value=\"3\"". (($user->getSex() == 3) ? " selected" : "" ). ">" . \Engine\LanguageManager::GetTranslation("gender_female") . "</option>";
        if (count($user->Blacklister()->getList()) == 0) {
            $blacklistTable = "<td colspan=\"4\" style=\"text-align: center;\"><span class=\"glyphicons glyphicons-info-sign\"></span> " . \Engine\LanguageManager::GetTranslation("empty_blacklist") . "</td>";
        } else {
            for ($i = 0; $i <= count($user->Blacklister()->getList()) - 1; $i++) {
                $blacklistTable .= "<td>" . \Users\UserAgent::GetUserNick($user->Blacklister()->getList()[$i]["bid"]) . "</td>" . PHP_EOL;
                $blacklistTable .= "<td>" . Engine\Engine::DatetimeFormatToRead($user->Blacklister()->getList()[$i]["addedtime"]) . "</td>" . PHP_EOL;
                $blacklistTable .= "<td>" . (($user->Blacklister()->getList()[$i]["comment"] == "") ? "<пусто>" : $user->Blacklister()->getList()[$i]["comment"]) . "</td>" . PHP_EOL;
                $blacklistTable .= "<td><button class=\"profile-profile-btn\" type=\"submit\" formaction=\"/site/scripts/blacklister.php?buid=" . $user->Blacklister()->getList()[$i]["bid"] . ">".
                    \Engine\LanguageManager::GetTranslation("remove"). "</button></td>" . PHP_EOL;
            }
        }
        $main = str_replace("{PROFILE_PAGE:USER_FRIENDLIST_MANAGE_BTN}", "<div class=\"profile-profile-panel-btn-group\">
                                        <button type=\"button\" onclick=\"showPanel('friends')\" class=\"profile-profile-panel-btn\">" . \Engine\LanguageManager::GetTranslation("manage_friendlist") . "</button>
                                    </div>", $main);

        $main = str_replace("{PROFILE_ERRORS_INFO}", $userPageErrors, $main);
        $main = str_replace("{PROFILE_FOOTER_BTNS}", $userFootBtns, $main);
        $main = str_replace("{PROFILE_NOTIFICS_SPAN_CLASS}", "profile-btn-" . (($notNotifiedCount != 0 && $page != "news") ? "new-" : "" ) . "counter", $main);
        $main = str_replace("{PROFILE_NOTIFICS_SPAN_COUNT}", ($page == "news") ? 0 : (($notNotifiedCount > 10) ? "10+" : $notNotifiedCount), $main);
        $main = str_replace("{PROFILE_PM_SPAN_CLASS}", "profile-btn-" . (($notReadCount != 0) ? "new-" : ""). "counter", $main);
        $main = str_replace("{PROFILE_PM_SPAN_COUNT}", ($notReadCount > 10) ? "10+" : $notReadCount, $main);
        $main = str_replace("{PROFILE_PAGE:USER_SEX_SELECTOR}", $sexOptions, $main);
        $main = str_replace("{PROFILE_PAGE:USER_PRIVATE_VK}", ($user->IsVKPublic()) ? "checked" : "", $main);
        $main = str_replace("{PROFILE_PAGE:USER_PRIVATE_SKYPE}", ($user->IsSkypePublic()) ? "checked" : "", $main);
        $main = str_replace("{PROFILE_PAGE:USER_PRIVATE_EMAIL}", ($user->IsEmailPublic()) ? "checked" : "", $main);
        $main = str_replace("{PROFILE_PAGE:USER_PRIVATE_BIRTHDAY}", ($user->IsBirthdayPublic()) ? "checked" : "", $main);
        $main = str_replace("{PROFILE_PAGE:USER_PRIVATE_ACC}", ($user->IsAccountPublic()) ? "checked" : "", $main);
        $main = str_replace("{PROFILE_PAGE:USER_BLACKLIST_TABLE}", $blacklistTable, $main);
        if ($user->UserGroup()->getPermission("user_signs")) {
            include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/usereditsignature.html";
            $userEditSignatureForm = getBrick();
            $main = str_replace("{PROFILE_PAGE:USER_SIGNATURE_FORM}", $userEditSignatureForm, $main);
            $main = str_replace("{PROFILE_PAGE:USER_SIGNATURE_TEXT}", $user->getSignature(), $main);
        }
        else $main = str_replace_once("{PROFILE_PAGE:USER_SIGNATURE_FORM}", null, $main);
        if ($user->UserGroup()->getPermission("enterpanel"))
            $main = str_replace_once("{PROFILE_AUTH_ADMINPANEL_BTN}", "<a class=\"profile-profile-btn\" href=\"adminpanel.php\"><span class=\"profile-btn-span\"></span> "
                    . \Engine\LanguageManager::GetTranslation("adminpanel_btn") . "</a>", $main);
        else
            $main = str_replace_once("{PROFILE_AUTH_ADMINPANEL_BTN}", "", $main);
    }
    $main = str_replace("{PROFILE_PAGE:USER_AVATAR}", $user->getAvatar(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_AVATAR_SIZE}", \Engine\Engine::GetEngineInfo("aw") . "x" . \Engine\Engine::GetEngineInfo("ah"), $main);
}

if ($user !== false && $session === 26 && $user->getId() == $_SESSION["uid"] && !$user->getActiveStatus()){
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authactivation.html";
    $authActivateForm = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authactivateerrors.phtml";
    $authActivationErrors = getBrick();

    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", $authActivationErrors, $main);
    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}", \Engine\LanguageManager::GetTranslation("account_activation"), $header);
    $main = str_replace_once("{PROFILE_MAIN_BODY}", $authActivateForm, $main);
    $main = str_replace_once("{PROFILE_PAGE_TITLE}", \Engine\LanguageManager::GetTranslation("account_activation") ." - " . \Engine\Engine::GetEngineInfo("sn"), $main);
}

if (((!$session && \Engine\Engine::GetEngineInfo("gsp") && !empty($user) && $user->IsAccountPublic())
    || ($session === true && $user !== false && $user->getId() != $_SESSION["uid"] && \Users\UserAgent::GetUser($_SESSION["uid"])->UserGroup()->getPermission("user_see_foreign")))
    && ($user->IsAccountPublic() || $user->FriendList()->isFriend($_SESSION["uid"]))){
    ///////////////////////////////////////////////////////////////////////
    /// Build additional fields mechanism.
    ///////////////////////////////////////////////////////////////////////

    $additionalFields = \Users\UserAgent::GetAdditionalFieldsList();
    $userAdFields = $user->getAdditionalFields();
    $customAF = [];
    $contactAF = [];
    $infoAF = [];
    /********************************************
     * $infoEditAF[] - array with fields to edit info additional fields.
     * And etc.
     *******************************************/
    foreach($additionalFields as $fieldProp){
        $content = "";
        $isPrivate = false;
        $fieldName = $fieldProp["name"];
        $tag = "";
        $title = "";
        $closingTag = "";
        foreach ($userAdFields as $adField){
            if ($fieldProp["id"] == $adField["fieldId"]){
                $content = htmlentities($adField["content"]);
                $isPrivate = $adField["isPrivate"];
            }
        }
        if ($content != ""){
            if ($fieldProp["link"] != ""){
                $tag = "<a class=\"profile-profile-link\" href=\"" . str_replace("{{1}}", $content, $fieldProp["link"])  ."\"";
                $closingTag = "</a>";
            }
            if ($fieldProp["description"] != ""){
                $title = " title=\"" . htmlentities($fieldProp["description"]). "\"";
            }
            if ($fieldProp["link"] == "" && $title != ""){
                $tag = "<span";
                $closingTag = "</span>";
            }
            if ($fieldProp["description"] != ""){
                $tag .= $title . ">" . $content . $closingTag;
            }

            if ($tag != "") {
                $result = $fieldName . ": " . $tag . "<br>";
            }
        } else {
            if ($fieldProp["type"] !== "3")
                $result = $fieldName . ": " . \Engine\LanguageManager::GetTranslation("not_setted"). "<br>";
            else {
                $value = (\Users\UserAgent::GetAdditionalFieldContentOfUser($user->getId(), $id) == null) ? $fieldProp["custom"] :
                    \Users\UserAgent::GetAdditionalFieldContentOfUser($user->getId(), $id);
                $result = $fieldName . ": " . $value . "<br>";
            }
        }
        switch ($fieldProp["type"]){
            case 1:
                $infoAF[] = $result;
                break;
            case 2:
                $contactAF[] = $result;
                break;
            case 3:
                $customAF[] = $result;
                break;
        }

    }

    //Display on main profile page.
    $infoAFJoined = implode("", $infoAF);
    $customAFJoined = implode("", $customAF);
    $contactAFJoined = implode("", $contactAF);

    //End building.

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userprofile.html";
    $profileMainPanel = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userscript.js";
    $profileJS = getBrick();

    $main = str_replace_once("{PROFILE_PAGE_TITLE}", \Engine\LanguageManager::GetTranslation("profile") . " " . $user->getNickname() . " - " . \Engine\Engine::GetEngineInfo("sn"), $main);
    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}",\Engine\LanguageManager::GetTranslation("profile"), $header);

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userinfo.html";
    $userInfo = getBrick();

    if ($user->IsVKPublic() || $user->FriendList()->isFriend($_SESSION["uid"]))
        $userVKLink = ($user->getVK() == "") ? "VK: " . \Engine\LanguageManager::GetTranslation("not_setted") . "<br>" : "VK: <a class=\"profile-profile-link\" href=\"http://vk.com/".htmlentities($user->getVK())."\">" . $user->getVK() . "</a><br>";
    else $userVKLink = "";
    if ($user->IsBirthdayPublic() || $user->FriendList()->isFriend($_SESSION["uid"]))
        $userBirthday = $user->getBirth() == "" ? \Engine\LanguageManager::GetTranslation("birthday") . ": " . \Engine\LanguageManager::GetTranslation("not_setted") . "<br>" :
             \Engine\LanguageManager::GetTranslation("birthday") . ": " . htmlentities($user->getBirth()) . "<br>";
    else $userBirthday = "";
    if ($user->IsSkypePublic() || $user->FriendList()->isFriend($_SESSION["uid"]))
        $userSkypeLink = $user->getSkype() == "" ? "Skype: " . \Engine\LanguageManager::GetTranslation("not_setted") ."<br>" : "Skype: <a class=\"profile-profile-link\" href=\"skype:". $user->getSkype()."?chat\">написать</a><br>";
    else $userSkypeLink = "";
    if ($user->IsEmailPublic() || $user->FriendList()->isFriend($_SESSION["uid"]))
        $userEmailLink = "Email: <a class=\"profile-profile-link\" href=\"mailto:".$user->getEmail()."\">" . $user->getEmail() . "</a><br>";
    else $userEmailLink = "";

    if ($user->getReferer() != null)
        $userRefererLink = "Реферер: <a class=\"profile-profile-link\" href=\"profile.php?uid=".$user->getReferer()->getId()."\">". $user->getReferer()->getNickname() . "</a><br>";
    else $userRefererLink = "";
    switch ($user->getSex()){
        case 1:
            $userSex = "<span class=\"glyphicons glyphicons-gender-intersex\"></span> " . \Engine\LanguageManager::GetTranslation("not_setted");
            break;
        case 2:
            $userSex = "<span class=\"glyphicons glyphicons-gender-male\"></span> " . \Engine\LanguageManager::GetTranslation("gender_male");
            break;
        case 3:
            $userSex = "<span class=\"glyphicons glyphicons-gender-female\"></span> " . \Engine\LanguageManager::GetTranslation("gender_female");
            break;
    }
    include_once "./site/reputationer.php";

    $main = str_replace_once("{PROFILE_PAGE_GUI_SCRIPT}", $profileJS, $main);
    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", "", $main);
    $main = str_replace_once("{PROFILE_MAIN_BODY}",$profileMainPanel, $main);
    $main = str_replace("{PROFILE_PAGE:USER_AVATAR}", $user->getAvatar(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_NICKNAME}", $user->getNickname(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_ID}", $user->UserGroup()->getId(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_COLOR}", ($user->UserGroup()->getColor() == "#000000") ? "#ffffff" : $user->UserGroup()->getColor(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_NAME}", $user->UserGroup()->getName(), $main);
    //Механизм последнего входа.
    $lastOnline = 0;
    if ($user->getLastTime() == 0){
        if ($user->getSex() == 2)
            $lastOnline = \Engine\LanguageManager::GetTranslation("not_sign_in_she");
        else
            $lastOnline = \Engine\LanguageManager::GetTranslation("not_sign_in_he");
    }
    else
    {
        if (\Engine\Engine::GetSiteTime() > $user->getLastTime()+15*60) {
            if ($user->getSex() == 2)
                $lastOnline =  \Engine\LanguageManager::GetTranslation("signed_in_she");
            else
                $lastOnline =  \Engine\LanguageManager::GetTranslation("signed_in_he");
            $lastOnline .= " ". \Engine\LanguageManager::GetTranslation("in") . " " .  \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $user->getLastTime()));
        } else {
            $lastOnline = "<span style=\"color: #00dd00;\">". \Engine\LanguageManager::GetTranslation("online"). "</span>";
        }
    }
    $main = str_replace("{PROFILE_PAGE:USER_LASTONLINE}", $lastOnline, $main);
    $main = str_replace_once("{PROFILE_PAGE_INFO}", $userInfo, $main);
    $main = str_replace_once("{PROFILE_PAGE_EDIT}", null, $main);
    $main = str_replace_once("{PROFILE_PAGE_PM}", null, $main);
    $main = str_replace_once("{PROFILE_PAGE_NOTIFICS}", null, $main);
    $main = str_replace_once("{PROFILE_PAGE_FRIENDS}", null, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REGDATETIME}", (($user->getSex() == 2) ? "а " : " ") . \Engine\Engine::DateFormatToRead($user->getRegDate()) . ".", $main);
    $main = str_replace("{PROFILE_PAGE:USER_TOPICS_CREATED_COUNT}", \Forum\ForumAgent::GetCountTopicOfAuthor($user->getId()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_COMMENTS_CREATED_COUNT}", \Forum\ForumAgent::GetCountOfCommentOfUser($user->getId()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FROM}", htmlentities($user->getFrom()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_REALNAME}", htmlentities($user->getRealName()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_BIRTHDAY_LINK}", $userBirthday, $main);
    $main = str_replace("{PROFILE_PAGE:USER_SEX}", $userSex, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REFERER}", $userRefererLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REP_POINTS}", $user->getReputation()->getReputationPoints() . " " . \Engine\LanguageManager::GetTranslation("point(s)"), $main);
    $main = str_replace("{PROFILE_PAGE:USER_EMAIL}", $userEmailLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_SKYPE_LINK}", $userSkypeLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_VK_LINK}", $userVKLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_HOBBIES}", $user->getHobbies() == "" ? "не указано" : htmlentities($user->getHobbies()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ABOUT}", $user->getAbout() == "" ? "не указано" : htmlentities($user->getAbout()), $main);
    if ($user->getSignature() == ""){
        $signature = "не указано";
    } else {
        $signature = nl2br(html_entity_decode(\Engine\Engine::ChatFilter(\Engine\Engine::CompileBBCode($user->getSignature()))));
    }
    $main = str_replace("{PROFILE_PAGE:USER_SIGNATURE}", $signature, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REPORT_CREATED_COUNT}", $user->getReportsCreatedCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FRIENDS_COUNT}", $user->FriendList()->getFriendsCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ONLINE_FRIENDS_COUNT}", $user->FriendList()->getOnlineFriendCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FRIENDLIST_MANAGE_BTN}", "", $main);
    $main = str_replace_once("{PROFILE_PAGE:CUSTOM_ADDITIONALS}", $customAFJoined, $main);
    $main = str_replace_once("{PROFILE_PAGE:CONTACT_ADDITIONALS}", $contactAFJoined, $main);
    $main = str_replace_once("{PROFILE_PAGE:INFO_ADDITIONALS}", $infoAFJoined, $main);
    $main = str_replace_once("{PROFILE_REPUTATIONER:STYLESHEET}", "<link rel=\"stylesheet\" href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/reputationer-style.css\">", $main);
    $main = str_replace_once("{PROFILE_PAGE:USER_REPUTATION_AONCLICK}", "onclick=\"$('#reputation-frame').show();\"", $main);
    $main = str_replace("{PROFILE_JS:SHOW_PANEL}", "showPanel('info');" . PHP_EOL . "showSubpanel('info', 1);", $main);

    if ($session === true && $user->getId() != $_SESSION["uid"] && \Users\UserAgent::GetUser($_SESSION["uid"])->UserGroup()->getPermission("user_see_foreign")) {
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/afup_buttons.html";
        $userFootBtns = getBrick();
        $userFootBtns = str_replace_once("{USER_NICKNAME}", $user->getNickname(), $userFootBtns);
    } elseif($session === false && \Engine\Engine::GetEngineInfo("gsp")){
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/nup_buttons.html";
        $userFootBtns = getBrick();
    }

    $main = str_replace("{PROFILE_ERRORS_INFO}", $userPageErrors, $main);
    $main = str_replace("{PROFILE_FOOTER_BTNS}", $userFootBtns, $main);
}

if (($session !== true && !empty($user) && !\Engine\Engine::GetEngineInfo("gsp")) ||
    (isset($_GET["uid"]) && !\Users\UserAgent::IsUserExist($_GET["uid"])) ||
    ($session === true && isset($_GET["uid"]) && $_GET["uid"] != $_SESSION["uid"] && !\Users\GroupAgent::IsHavePerm(\Users\UserAgent::GetUserGroupId($_SESSION["uid"]), "user_see_foreign")) ||
    ($session === true && isset($_GET["uid"]) && $_GET["uid"] != $_SESSION["uid"] && $user->Blacklister()->isBlocked($_SESSION["uid"]))){

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/seeperrors.phtml";
    $userPageErrors = getBrick();

    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}", \Engine\LanguageManager::GetTranslation("access_is_denied"), $header);

    $main = str_replace_once("{PROFILE_PAGE_TITLE}", \Engine\LanguageManager::GetTranslation("access_is_denied") . " - " . \Engine\Engine::GetEngineInfo("sn"), $main);
    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", $userPageErrors, $main);
    $main = str_replace_once("{PROFILE_MAIN_BODY}", null, $main);

}

/*********************************************************************************************/

/***********************************Block profile page if user is not exist.*****************/

if (!$session || empty($user)){
    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}", "Авторизация", $header);

    $main = str_replace_once("{PROFILE_PAGE_TITLE}", "Авторизация - " . \Engine\Engine::GetEngineInfo("sn"), $main);

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authscript.js";
    $authJS = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/autherrors.phtml";
    $authErrors = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/auth.html";
    $authForm = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authsignup.html";
    $authSignUpForm = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authregerrors.phtml";
    $authRegErrors = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authremaindpass.html";
    $authRemaind = getBrick();

    //Create fields in registration form.
    $additionalFields = \Users\UserAgent::GetAdditionalFieldsList();
    $additionalFieldsInputString = "";
    $additionalFieldsInputNecessaryString = "";
    for ($i = 0; $i < count($additionalFields); $i++){
        $fieldProp = $additionalFields[$i];
        if ($fieldProp["type"] != "2")
            continue;
        else {
            if ($fieldProp["inRegister"] == 1) {
                if ($fieldProp["isRequied"] == 1) {
                    $additionalFieldsInputNecessaryString .= "<div><input class=\"profile-input\" type=\"text\" id=\"profile-adfield-" . $fieldProp["id"] . "\" name=\"profile-adfield-" . $fieldProp["id"] . "\"
                                                          placeholder=\"" . $fieldProp["description"] . "*\"></div>";
                } else {
                    $additionalFieldsInputString .= "<div><input class=\"profile-input\" type=\"text\" id=\"profile-adfield-" . $fieldProp["id"] . "\" name=\"profile-adfield-" . $fieldProp["id"] . "\"
                                                          placeholder=\"" . $fieldProp["description"] . "\"></div>";
                }
            }
        }
    }
    $authSignUpForm = str_replace_once("{AUTH_PAGE:NECESSARY_ADDITIVE_FIELDS}", $additionalFieldsInputNecessaryString, $authSignUpForm);
    $authSignUpForm = str_replace_once("{AUTH_PAGE:NOT_NECESSARY_ADDITIVE_FIELDS}", $additionalFieldsInputString, $authSignUpForm);
    ///////////////////////////////////////
    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", (!empty($_REQUEST["res"]) && !in_array($_REQUEST["res"], ["ic", "nc", "nr"])) ? $authErrors : "", $main);
    $main = str_replace_once("{PROFILE_MAIN_BODY}", $authForm, $main);
    $main = str_replace_once("{PROFILE_PAGE_GUI_SCRIPT}", "", $main);
    $main = str_replace_once("{AUTH_PAGE:SIGN_UP}", $authSignUpForm, $main);
    $main = str_replace_once("{AUTH_PAGE:RULES}", html_entity_decode(\Engine\Engine::CompileBBCode(file_get_contents("./engine/config/rules.sfc", FILE_USE_INCLUDE_PATH))), $main);
    $main = str_replace_once("{AUTH_PAGE:REGISTER_ERRORS}", $authRegErrors, $main);
    $emailTipText = \Engine\LanguageManager::GetTranslation("email_notify") . "<br>";
    if (\Engine\Engine::GetEngineInfo("na"))
        $emailTipText .= \Engine\LanguageManager::GetTranslation("activation_notify");
    $main = str_replace_once("{AUTH_PAGE:EMAIL_TIP}", $emailTipText, $main);
    $main = str_replace_once("{AUTH_PAGE:CAPTCHA_PIC}", "<img src=\"$captchaImgPath\" alt=\"Captcha\">", $main);
    //<img src=\"$captchaImgPath\">
//    print_r($captchaImgPath);
    $main = str_replace_once("{AUTH_PAGE:CAPTCHA_ID}", $captchaID, $main);
    $main = str_replace("{AUTH_PAGE:UID_INPUT_PLACEHOLDER}", \Engine\Engine::GetEngineInfo("na") ?
        \Engine\LanguageManager::GetTranslation("email_or_login") : \Engine\LanguageManager::GetTranslation("nickname"), $main);
    $main = str_replace("{AUTH_REMAINDER}", $authRemaind, $main);

    if (!empty($_REQUEST["res"])){
        if (in_array($_REQUEST["res"], ["ic", "nc", "nr"]))
            $authJS .= "showPanel('signup'); showSubpanel('signup', 2);";
    }


}

/**********************************************************************************************/

$main = str_replace_once("{PROFILE_PAGE_HEADER}",$header, $main);
$main = str_replace_once("{PROFILE_PAGE_FOOTER}",$footer, $main);

$main = str_replace_once("{PROFILE_REPUTATIONER:STYLESHEET}", "", $main);
$main = str_replace_once("{PROFILE_REPUTATIONER:JS}", "", $main);
$main = str_replace_once("{PROFILE_UPLOADER:JS}", "", $main);
$main = str_replace_once("{PROFILE_UPLOADER_BLOCK}", $uploaderBlock, $main);
$main = str_replace_once("{PROFILE_UPLOADER:STYLESHEET}", "", $main);
$main = str_replace_once("{PROFILE_REPUTATIONER_BLOCK}", $reputationerBlock, $main);
$main = str_replace_once("{PROFILE_PAGE_GUI_CUSTOM_SCRIPT}", $customScript, $main);
$main = str_replace_once("{PROFILE_JS:SHOW_PANEL}", $authJS, $main);

if (\Engine\Engine::GetEngineInfo("smt")){
    if (\Engine\Engine::GetEngineInfo("sms") == 0) {
        $main = str_replace_once("{METRIC_JS}", null, $main);
    } else {
        $main = str_replace_once("{METRIC_JS}", \Engine\Engine::GetAnalyticScript(), $main);
    }
} else {
    $main = str_replace_once("{METRIC_JS}", null, $main);
}

ob_end_clean();
$main = str_replace_once("{PROFILE_PAGE:PAGE_NAME}", "", $main);
$main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", "", $main);
$main = str_replace_once("{PROFILE_MAIN_BODY}", $test, $main);
//$main = str_replace_once("{PROFILE_MAIN_BODY}", \Engine\LanguageManager::GetTranslation("ua_info"), $main);
$main = str_replace_once("{IMAGER_STYLESHEET}", "", $main);
$main = str_replace_once("{IMAGER}", "", $main);
$main = str_replace_once("{IMAGER_JS}", "", $main);

include_once "./site/scripts/SpoilerController.js";
$spoilerManager = getBrick();
$main = str_replace_once("{SPOILER_CONTROLLER:JS}", $spoilerManager, $main);

\Engine\PluginManager::Integration($main);

?>