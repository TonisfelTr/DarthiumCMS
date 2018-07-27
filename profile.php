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

###############################################
# Менеджер пользователя.
if ($session === TRUE || !empty($_GET["uid"])) $seeProfile = true; else $seeProfile = false;
if ($session === TRUE){
    $user = new \Users\User($_SESSION["uid"]);
    if (!empty($_REQUEST["res"])){
        $response = $_REQUEST["res"];
    }
    $notReadCount = $user->MessageManager()->getNotReadCount();

}
if (!empty($_GET["uid"])){
    if (\Users\UserAgent::IsUserExist($_GET["uid"]))
        $user = new \Users\User($_GET["uid"]);
    else $user = false;
}
if (!$session) {
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
    $uidPlaceholder = "Email или никнейм";
else $uidPlaceholder = "Никнейм";
###################################################
#Обработка прямого обращения с активацией:
if (!empty($_REQUEST["activate"]) && !empty($_REQUEST["uid"])){
    header("Location: ./site/scripts/activator.php?activate=".$_REQUEST["activate"]."&uid=".$_REQUEST["uid"]."&profile-activation-code-send-btn");
    exit;
}
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

    if (!empty($_GET["page"]) && $_GET["page"] == "wm"){
        if (!empty($_GET["mid"])){
            $quoteLetter = $user->MessageManager()->read($_GET["mid"]);
            if ($quoteLetter == false) $response = "nprm";
        }
    }
}

################################################
# Менеджер уведомлений.
if ($session === true && $user->getId() == $_SESSION["uid"]){
    $notificationsCount = $user->Notifications()->getNotifiesCount();
    $notNotifiedCount = $user->Notifications()->getNotificationsUnreadCount();
}
#################################################
# Отображение страниц
if (!empty($_GET["page"]))
    $page = $_GET["page"];
else
    $page = "none";

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
    if (($session !== TRUE ) && !\Engine\Engine::GetEngineInfo("gsp")) $errorCode = 1;
    elseif ($session !== true && $user === false && \Engine\Engine::GetEngineInfo("gsp")) $errorCode = 2;
    elseif ($session === TRUE && $user->getId() != $_SESSION["uid"] && !\Users\GroupAgent::IsHavePerm(\Users\UserAgent::GetUserGroupId($_SESSION["uid"]), "user_see_foreign")) $errorCode = 3;
    elseif (($session !== true && \Engine\Engine::GetEngineInfo("gsp") == false)
        || (!empty($user) && !$user->IsAccountPublic() && $session !== true)
        || (!empty($user) && $user->getId() != $_SESSION["uid"] && !$user->IsAccountPublic() && !$user->FriendList()->isFriend($_SESSION["uid"]))) $errorCode = 4;
}

include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/seeperrors.phtml";
$profileSeeErrors = getBrick();

/***********************************Block profile page if user is exist.*********************/

if ($session === true && $user->getId() == $_SESSION["uid"]){
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userprofile.html";
    $profileMainPanel = getBrick();
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userscript.js";
    $profileJS = getBrick();
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userinfo.html";
    $userInfo = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/useredit.html";
    $userEdit = getBrick();
    if ($user->UserGroup()->getPermission("change_profile")) {
        $userEdit = str_replace_once("{PROFILE_PAGE:USER_EDITPANEL_BTN}", "<button type=\"button\" id=\"profile-edit-btn-custom-panel\" class=\"profile-profile-panel-btn active\" onclick=\"showSubpanel('edit', 1);\"> Общие данные</button>", $userEdit);
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userprofileeditcustom.html";
        $profileEditCustomPanel = getBrick();
        $userEdit = str_replace_once("{PROFILE_PAGE:USER_EDITPANEL}", $profileEditCustomPanel, $userEdit);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Building PMs module.
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userpm.html";
    $userPMs = getBrick();
    if ($letter !== false){
        $userPMs = str_replace_once("{PROFILE_PAGE:USER_READ_PM_BUTTON}", "<button class=\"profile-profile-panel-btn active\" id=\"profile-pm-read-btn\" type=\"button\" onclick=\"showSubpanel('pm', 6);\"><span class=\"glyphicons glyphicons-message-out\"></span> Читать письмо</button>", $userPMs);
    } else {
        $userPMs = str_replace_once("{PROFILE_PAGE:USER_READ_PM_BUTTON}", "", $userPMs);
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_INCOMING_COUNT}", $user->MessageManager()->getIncomeSize(), $userPMs);
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_OUTCOMING_COUNT}", $user->MessageManager()->getOutcomeSize(), $userPMs);
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_SENDED_COUNT}", $user->MessageManager()->getSendedSize(), $userPMs);
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_BIN_COUNT}", $user->MessageManager()->getBinSize(), $userPMs);

    if ($user->MessageManager()->getIncomeSize() == 0) {
        $table = "<tr><td class=\"profile-pm-table-empty-td\" colspan=\"6\"><span class=\"glyphicons glyphicons-info-sign\"></span> У вас нет новых сообщений :(</td></tr>";
    } else
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
                    <button class=\"profile-table-btn\" title=\"Удалить сообщение\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?d=$id\"><span class=\"glyphicons glyphicons-erase\"></span></button>
                    <button class=\"profile-table-btn\" title=\"Читать сообщение\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?r=$id\"><span class=\"glyphicons glyphicons-message-out\"></span></button>
                    <button class=\"profile-table-btn\" title=\"Ответить на сообщение\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?q=$id\"><span class=\"glyphicons glyphicons-quote\"></span></button>
                </td>
            </tr>";
        }
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_INCOMING_TABLE}", $table, $userPMs);

    if ($user->MessageManager()->getOutcomeSize() == 0) {
        $table = "<tr><td class=\"profile-pm-table-empty-td\" colspan=\"6\"><span class=\"glyphicons glyphicons-info-sign\"></span> У вас нет исходящих сообщений...</td></tr>";
    } else
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
                    <button class=\"profile-table-btn\" title=\"Удалить сообщение\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?d=$id\"><span class=\"glyphicons glyphicons-erase\"></span></button>
                    <button class=\"profile-table-btn\" title=\"Читать сообщение\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?r=$id\"><span class=\"glyphicons glyphicons-message-out\"></span></button>
                    <button class=\"profile-table-btn\" title=\"Ответить на сообщение\" type=\"submit\" formaction=\"/site/scripts/pmanager.php?q=$id\"><span class=\"glyphicons glyphicons-quote\"></span></button>
                </td>
            </tr>";
        }
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_OUTCOMING_TABLE}", $table, $userPMs);

    if ($user->MessageManager()->getSendedSize() == 0) {
        $table = "<tr><td class=\"profile-pm-table-empty-td\" colspan=\"6\"><span class=\"glyphicons glyphicons-info-sign\"></span> Вы пока не отправили ни одного сообщения.</td></tr>";
    } else
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
                               <button class=\"profile-table-btn\" name=\"pm-read\" type=\"submit\" title=\"Прочитать сообщение\" formaction=\"/site/scripts/pmanager.php?r=$id\"><span class=\"glyphicons glyphicons-message-out\"></span></button>
                               <button class=\"profile-table-btn\" type=\"submit\" title=\"Удалить сообщение\" formaction=\"/site/scripts/pmanager.php?d=$id\"><span class=\"glyphicons glyphicons-erase\"></span></button>
                           </td>
                       </tr>";
        }
    }
    $userPMs = str_replace_once("{PROFILE_PAGE:USER_PM_SENDED_TABLE}", $table, $userPMs);

    if ($user->MessageManager()->getBinSize() == 0) {
        $table = "<tr><td class=\"profile-pm-table-empty-td\" colspan=\"6\"><span class=\"glyphicons glyphicons-info-sign\"></span> Ваша корзина пуста :)</td></tr>";
    } else
    {
        $table = "";
        for ($k = 0; $k < $user->MessageManager()->getBinSize(); $k++) {
            $icon = "bin";
            $sender = new \Users\User($user->MessageManager()->bin()[$k]["senderUID"]);
            $receiver = new \Users\User($user->MessageManager()->bin()[$k]["receiverUID"]);
            $subject = $user->MessageManager()->bin()[$k]["subject"];
            $time = \Engine\Engine::DatetimeFormatToRead($user->MessageManager()->bin()[$k]["receiveTime"]);
            $id = $user->MessageManager()->bin()[$k]["id"];
            $table .= "<tr onclick=\"submitForReadLetter($id);\">
                           <td class=\"profile-pm-table-icon\"><span class=\"glyphicons glyphicons-$icon\"></span></td>
                           <td class=\"profile-pm-table-author\"><a class=\"profile-link\" href=\"profile.php?uid=" . $sender->getId() > "\">" . $sender->getNickname() . "</a></td>
                           <td class=\"profile-pm-table-author\"><a class=\"profile-link\" href=\"profile.php?uid=" . $receiver->getId() . "\">" . $receiver->getNickname() . "</a></td>
                           <td class=\"profile-pm-table-subject\">$subject</td>
                           <td class=\"profile-pm-table-time\">$time</td>
                           <td class=\"profile-pm-table-btn-group\">
                               <button class=\"profile-table-btn\" name=\"pm-read\" type=\"submit\" title=\"Прочитать сообщение\" formaction=\"/site/scripts/pmanager.php?r=$id\"><span class=\"glyphicons glyphicons-message-out\"></span></button>
                               <button class=\"profile-table-btn\" type=\"submit\" title=\"Восстановить\" formaction=\"/site/scripts/pmanager.php?rt=$id\"><span class=\"glyphicons glyphicons-inbox-in\"></span></button>
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
        $userNotificsTable = "<hr><p>Пока что у Вас нет ни одного уведомления. Не расстраивайтесь! Когда-нибудь они точно появятся :)</p>";
    else {
        $userNotificsTable = "";
        for ($i = 0; $i < $user->Notifications()->getNotifiesCount(); $i++){
        $ntf = $user->Notifications()->getNotifies();
        $userNotificsTable .= "<hr><div class=\"profile-notification-body\">";
        if (!$ntf[$i]["isRead"])
             $userNotificsTable .= "<span class=\"glyphicons glyphicons-info-sign\" style=\"font-size: 25px;\"></span>";

        $userNotificsTable .= "<a class=\"profile-link\" href=\"profile.php?uid=" . $ntf[$i]["fromUid"] . "\">" . \Users\UserAgent::GetUserNick($ntf[$i]["fromUid"]) . "</a>";
        switch ($ntf[$i]["type"]){
            case 1:
                $userNotificsTable .= " добавил Вас в <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">комнату</a> для обсуждения жалобы.";
                break;
            case 2:
                $userNotificsTable .= " добавил Вас в свой список друзей.";
                break;
            case 3:
                $userNotificsTable .= " изменил Ваш профиль через администраторскую панель. Напишите ему, чтобы узнать детали.";
                break;
            case 4:
                $userNotificsTable .= " удалил Вас из комнаты для обсуждения жалобы.";
                break;
            case 5:
                $userNotificsTable .= " добавил своё сообщение в <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">комнате</a> для обсуждения жалобы.";
                break;
            case 6:
                $userNotificsTable .= " добавил своё сообщение к созданному Вами посту.";
                break;
            case 7:
                $userNotificsTable .= " понравился созданный Вами пост.";
                break;
            case 8:
                $userNotificsTable .= " перенёс Ваш пост.";
                break;
            case 9:
                $userNotificsTable .= " удалил созданный Вами пост. Напишите ему, чтобы узнать детали.";
                break;
            case 10:
                $userNotificsTable .= " изменил текст в созданной Вами жалобе.";
                break;
            case 11:
                $userNotificsTable .= " удалил созданную Вами жалобу. Напишите ему, чтобы узнать детали.";
                break;
            case 12:
                $userNotificsTable .= " изменил текст Вашего поста.";
                break;
            case 13:
                $userNotificsTable .= " поменял статус Вашего поста. Напишите ему, чтобы узнать детали, если они не указаны в посте.";
                break;
            case 14:
                $userNotificsTable .= " зарегистрировался, указав Вас в качестве реферера.";
                break;
            case 15:
                $userNotificsTable .= " закрыл созданную Вами <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">жалобу</a>.";
                break;
            case 16:
                $userNotificsTable .= " удалил Ваш ответ в <a href=\"index.php?page=report&preg=see&rid=" . $ntf[$i]["subject"] . "\">жалобе.</a>. Напишите ему, чтобы узнать детали.";
                break;
            case 18:
                $nAID = end(explode(",", $ntf[$i]["subject"]));
                $nANickname = \Users\UserAgent::GetUserNick($nAID);
                $userNotificsTable .= " добавил <a href=\"profile.php?uid=$nAID\">$nANickname</a> в <a href=\"index.php?page=report&preg=see&rid=" . reset(explode(",", $ntf[$i]["subject"])) . "\">комнату</a> для обсуждения жалобы.";
                break;
            case 19:
                $nAID = end(explode(",", $ntf[$i]["subject"]));
                $nANickname = \Users\UserAgent::GetUserNick($nAID);
                $userNotificsTable .= " удалил <a href=\"profile.php?uid=$nAID\">$nANickname</a> из <a href=\"index.php?page=report&preg=see&rid=" . reset(explode(",", $ntf[$i]["subject"])) . "\">комнаты</a> для обсуждения жалобы.";
                break;
            case 20:
                $nAID = end(explode(",", $ntf[$i]["subject"]));
                $nANickname = \Users\UserAgent::GetUserNick($nAID);
                $userNotificsTable .= " закрыл <a href=\"index.php?page=report&rid=".reset(explode(",", $ntf[$i]["subject"]))."\">жалобу</a>, созданную <a href=\"profile.php?uid=$nAID\">$nANickname</a>.";
                break;
        }
        $userNotificsTable .= "<p class=\"profile-notification-time\">" . Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $ntf[$i]["createTime"])) . "</p>";
        $userNotificsTable .= "</div>";
    if (!$ntf[$i]["isRead"]) $user->Notifications()->setRead($ntf[$i]["id"]);
        }
    }

    $userNotifics = str_replace_once("{PROFILE_PAGE:USER_NOTIFICATIONS}", $userNotificsTable, $userNotifics);
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //End building.
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userfriends.html";
    $userFriendList = getBrick();

    if ($user->FriendList()->getFriendsCount() == 0)
        $friendListTable = "<tr><td colspan=\"4\" style=\"text-align: center\">У вас пока что нет ни одного друга :(</td></tr>";
    else {
        $friendListTable = "<tr><td colspan=\"4\" style=\"text-align: center;\">Друзья онлайн</td></tr>";
        $onlineList = \Users\UserAgent::GetOnlineFriends($user->getId());
        for ($i = 0; $i < \Users\UserAgent::GetOnlineFriendsCount($user->getId()); $i++) {
            $friend = new \Users\User($onlineList[$i]);
            $friendListTable .= "<tr>
                                    <td><img class=\"profile-friends-avatar\" src=\"" . $friend->getAvatar() . "\"></td>
                                    <td><a class=\"profile-profile-link\" href=\"profile.php?uid=". $friend->getId() . "\">" . $friend->getNickname() ."</a>
                                    <td>" . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $user->FriendList()->getFriendFromDB($onlineList[$i])["regdate"])) . "</td>
                                    <td></td>
                                 </tr>";
        }
        $friendListTable .= "<tr><td colspan=\"4\" style=\"text-align: center;\">Остальные друзья</td></tr>";
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

    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}","Профиль", $header);

    $main = str_replace_once("{PROFILE_PAGE_TITLE}", "Профиль " . $user->getNickname() . " - " . \Engine\Engine::GetEngineInfo("sn"), $main);
    $main = str_replace_once("{PROFILE_PAGE_GUI_SCRIPT}", $profileJS, $main);
    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", $profileSeeErrors, $main);
    $main = str_replace_once("{PROFILE_MAIN_BODY}",$profileMainPanel, $main);
    $main = str_replace("{PROFILE_PAGE:USER_NICKNAME}", $user->getNickname(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_COLOR}", $user->UserGroup()->getColor(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_NAME}", $user->UserGroup()->getName(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_LASTONLINE}", (\Engine\Engine::GetSiteTime() > $user->getLastTime()+15*60) ? "заходил" . (($user->getSex() == 2) ? "а" : "")
        . " в " . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s",$user->getLastTime())) : "<span style=\"color: #00dd00;\">онлайн</span>", $main);
    $main = str_replace_once("{PROFILE_PAGE_INFO}", $userInfo, $main);
    $main = str_replace_once("{PROFILE_PAGE_EDIT}", $userEdit, $main);
    $main = str_replace_once("{PROFILE_PAGE_PM}", $userPMs, $main);
    $main = str_replace_once("{PROFILE_PAGE_NOTIFICS}", $userNotifics, $main);
    $main = str_replace_once("{PROFILE_PAGE_FRIENDS}", $userFriendList, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REGDATETIME}", \Engine\Engine::DateFormatToRead($user->getRegDate()) . ".", $main);

    if ($user->IsVKPublic())
        $userVKLink = ($user->getVK() == "") ? "VK: не указано<br>" : "VK: <a class=\"profile-profile-link\" href=\"http://vk.com/".htmlentities($user->getVK())."\">" . htmlentities($user->getVK()) . "</a><br>";
    else $userVKLink = "";
    if ($user->IsBirthdayPublic())
        $userBirthday = $user->getBirth() == "" ? "День рождения: не указано<br>" : "День рождения: " . htmlentities($user->getBirth()) . "<br>";
    else $userBirthday = "";
    if ($user->IsSkypePublic())
        $userSkypeLink = $user->getSkype() == "" ? "Skype: не указано<br>" : "Skype: <a class=\"profile-profile-link\" href=\"skype:". htmlentities($user->getSkype())."?chat\">написать</a><br>";
    else $userSkypeLink = "";
    if ($user->IsEmailPublic())
        $userEmailLink = "Email: <a class=\"profile-profile-link\" href=\"mailto:".$user->getEmail()."\">" . $user->getEmail() . "</a><br>";
    else $userEmailLink = "";
    if ($user->getReferer() != null)
        $userRefererLink = "Реферер: <a href=\"profile.php?uid=".$user->getReferer()->getId()."\">". $user->getReferer()->getNickname() . "</a><br>";
    else $userRefererLink = "";
    switch ($user->getSex()){
        case 1:
            $userSex = "мужской";
            break;
        case 2:
            $userSex = "женский";
            break;
        default:
            $userSex = "не указан";
            break;
    }
    if ($user->getFrom() == "")
        $userFrom = "не указано";
    else
        $userFrom = htmlentities($user->getFrom());
    if ($user->getRealName() == "")
        $userRealName = "не указано";
    else
        $userRealName = htmlentities($user->getRealName());

    $main = str_replace("{PROFILE_PAGE:USER_FROM}", $userFrom, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REALNAME}", $userRealName, $main);
    $main = str_replace("{PROFILE_PAGE:USER_BIRTHDAY_LINK}", $userBirthday, $main);
    $main = str_replace("{PROFILE_PAGE:USER_SEX}", $userSex, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REFERER}", $userRefererLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REP_POINTS}", $user->getReputation()->getReputationPoints() . " балл(ов).", $main);
    $main = str_replace("{PROFILE_PAGE:USER_EMAIL}", $userEmailLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_SKYPE_LINK}", $userSkypeLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_VK_LINK}", $userVKLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_HOBBIES}", $user->getHobbies() == "" ? "не указано" : htmlentities($user->getHobbies()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ABOUT}", $user->getAbout() == "" ? "не указано" : htmlentities($user->getAbout()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_VK}", $user->getVK(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_SKYPE}", $user->getSkype(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_SIGNATURE}", $user->getSignature() == "" ? "не указано" : nl2br(\Engine\Engine::CompileBBCode($user->getSignature())), $main);
    $main = str_replace("{PROFILE_PAGE:USER_REPORT_CREATED_COUNT}", $user->getReportsCreatedCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FRIENDS_COUNT}", $user->FriendList()->getFriendsCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ONLINE_FRIENDS_COUNT}", $user->FriendList()->getOnlineFriendCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_REALNAME_TEXT}", $user->getRealName(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_BIRTHDAY_TEXT}", $user->getBirth(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FROM_TEXT}", $user->getFrom(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ABOUT_TEXT}", $user->getAbout(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_HOBBIES_TEXT}", $user->getHobbies(), $main);
    if ($user->getId() == $_SESSION["uid"]){
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userperrors.phtml";
        $userPageErrors = getBrick();
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/aup_buttons.html";
        $userFootBtns = getBrick();

        $sexOptions = "<option value=\"0\"". (($user->getSex() == 0) ? " selected" : "" ). ">не указан</option>". PHP_EOL .
                      "<option value=\"1\"". (($user->getSex() == 1) ? " selected" : "" ). ">Мужской</option>" . PHP_EOL .
                      "<option value=\"2\"". (($user->getSex() == 2) ? " selected" : "" ). ">Женский</option>";
        if (count($user->Blacklister()->getList()) == 0) {
            $blacklistTable = "<td colspan=\"4\" style=\"text-align: center;\"><span class=\"glyphicons glyphicons-info-sign\"></span> Пока что Ваш чёрный список пуст.</td>";
        } else {
            for ($i = 0; $i <= count($user->Blacklister()->getList()) - 1; $i++) {
                $blacklistTable .= "<td>" . \Users\UserAgent::GetUserNick($user->Blacklister()->getList()[$i]["bid"]) . "</td>" . PHP_EOL;
                $blacklistTable .= "<td>" . Engine\Engine::DatetimeFormatToRead($user->Blacklister()->getList()[$i]["addedtime"]) . "</td>" . PHP_EOL;
                $blacklistTable .= "<td>" . (($user->Blacklister()->getList()[$i]["comment"] == "") ? "<пусто>" : $user->Blacklister()->getList()[$i]["comment"]) . "</td>" . PHP_EOL;
                $blacklistTable .= "<td><button class=\"profile-profile-btn\" type=\"submit\" formaction=\"/site/scripts/blacklister.php?buid=" . $user->Blacklister()->getList()[$i]["bid"] . ">Удалить</button></td>" . PHP_EOL;
            }
        }
        $main = str_replace("{PROFILE_PAGE:USER_FRIENDLIST_MANAGE_BTN}", "<div class=\"profile-profile-panel-btn-group\">
                                        <button type=\"button\" onclick=\"showPanel('friends')\" class=\"profile-profile-panel-btn\">Управление списком друзей</button>
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
            $main = str_replace_once("{PROFILE_AUTH_ADMINPANEL_BTN}", "<a class=\"profile-profile-btn\" href=\"adminpanel.php\"><span class=\"profile-btn-span\"></span> Админ-панель</a>", $main);

    }
    $main = str_replace("{PROFILE_PAGE:USER_AVATAR}", $user->getAvatar(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_AVATAR_SIZE}", \Engine\Engine::GetEngineInfo("aw") . "x" . \Engine\Engine::GetEngineInfo("ah"), $main);
}

if (((!$session && \Engine\Engine::GetEngineInfo("gsp") && !empty($user))
    || ($session === true && $user->getId() != $_SESSION["uid"] && \Users\UserAgent::GetUser($_SESSION["uid"])->UserGroup()->getPermission("user_see_foreign")))
    && ($user->IsAccountPublic() || $user->FriendList()->isFriend($_SESSION["uid"]))){
    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userprofile.html";
    $profileMainPanel = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userscript.js";
    $profileJS = getBrick();

    $main = str_replace_once("{PROFILE_PAGE_TITLE}", "Профиль " . $user->getNickname() . " - " . \Engine\Engine::GetEngineInfo("sn"), $main);
    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}","Профиль", $header);

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/userinfo.html";
    $userInfo = getBrick();

    if ($user->IsVKPublic())
        $userVKLink = ($user->getVK() == "") ? "VK: не указано<br>" : "VK: <a class=\"profile-profile-link\" href=\"http://vk.com/".$user->getVK()."\">" . $user->getVK() . "</a><br>";
    else $userVKLink = "";
    if ($user->IsBirthdayPublic())
        $userBirthday = $user->getBirth() == "" ? "День рождения: не указано<br>" : "День рождения: " . $user->getBirth() . "<br>";
    else $userBirthday = "";
    if ($user->IsSkypePublic())
        $userSkypeLink = $user->getSkype() == "" ? "Skype: не указано<br>" : "Skype: <a class=\"profile-profile-link\" href=\"skype:". $user->getSkype()."?chat\">написать</a><br>";
    else $userSkypeLink = "";
    if ($user->IsEmailPublic())
        $userEmailLink = "Email: <a class=\"profile-profile-link\" href=\"mailto:".$user->getEmail()."\">" . $user->getEmail() . "</a><br>";
    else $userEmailLink = "";
    if ($user->getReferer() != null)
        $userRefererLink = "Реферер: <a href=\"profile.php?uid=".$user->getReferer()->getId()."\"><br>". $user->getReferer()->getNickname() . "</a><br>";
    else $userRefererLink = "";
    switch ($user->getSex()){
        case 1:
            $userSex = "мужской";
            break;
        case 2:
            $userSex = "женский";
            break;
        default:
            $userSex = "не указан";
            break;
    }

    $main = str_replace_once("{PROFILE_PAGE_GUI_SCRIPT}", $profileJS, $main);
    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", $profileSeeErrors, $main);
    $main = str_replace_once("{PROFILE_MAIN_BODY}",$profileMainPanel, $main);
    $main = str_replace("{PROFILE_PAGE:USER_AVATAR}", $user->getAvatar(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_NICKNAME}", $user->getNickname(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_COLOR}", $user->UserGroup()->getColor(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_GROUP_NAME}", $user->UserGroup()->getName(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_LASTONLINE}", (\Engine\Engine::GetSiteTime() > $user->getLastTime()+15*60) ? "заходил" . (($user->getSex() == 2) ? "а" : "")
        . " в " . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s",$user->getLastTime())) : "<span style=\"color: #00dd00;\">онлайн</span>", $main);
    $main = str_replace_once("{PROFILE_PAGE_INFO}", $userInfo, $main);
    $main = str_replace_once("{PROFILE_PAGE_EDIT}", null, $main);
    $main = str_replace_once("{PROFILE_PAGE_PM}", null, $main);
    $main = str_replace_once("{PROFILE_PAGE_NOTIFICS}", null, $main);
    $main = str_replace_once("{PROFILE_PAGE_FRIENDS}", null, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REGDATETIME}", \Engine\Engine::DateFormatToRead($user->getRegDate()) . ".", $main);
    $main = str_replace("{PROFILE_PAGE:USER_FROM}", $user->getFrom(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_REALNAME}", $user->getRealName(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_BIRTHDAY_LINK}", $userBirthday, $main);
    $main = str_replace("{PROFILE_PAGE:USER_SEX}", $userSex, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REFERER}", $userRefererLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_REP_POINTS}", $user->getReputation()->getReputationPoints() . " балл(ов).", $main);
    $main = str_replace("{PROFILE_PAGE:USER_EMAIL}", $userEmailLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_SKYPE_LINK}", $userSkypeLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_VK_LINK}", $userVKLink, $main);
    $main = str_replace("{PROFILE_PAGE:USER_HOBBIES}", $user->getHobbies() == "" ? "не указано" : htmlentities($user->getHobbies()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ABOUT}", $user->getAbout() == "" ? "не указано" : htmlentities($user->getAbout()), $main);
    $main = str_replace("{PROFILE_PAGE:USER_VK}", $user->getVK(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_SKYPE}", $user->getSkype(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_BIRTHDAY}", $user->getBirth(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_SIGNATURE}", $user->getSignature() == "" ? "не указано" : nl2br(\Engine\Engine::CompileBBCode($user->getSignature())), $main);
    $main = str_replace("{PROFILE_PAGE:USER_REPORT_CREATED_COUNT}", $user->getReportsCreatedCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FRIENDS_COUNT}", $user->FriendList()->getFriendsCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_ONLINE_FRIENDS_COUNT}", $user->FriendList()->getOnlineFriendCount(), $main);
    $main = str_replace("{PROFILE_PAGE:USER_FRIENDLIST_MANAGE_BTN}", "", $main);


    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/seeperrors.phtml";
    $userPageErrors = getBrick();
    if ($session === true && $user->getId() != $_SESSION["uid"] && \Users\UserAgent::GetUser($_SESSION["uid"])->UserGroup()->getPermission("user_see_foreign")) {
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/afup_buttons.html";
        $userFootBtns = getBrick();
    } elseif(!$session && \Engine\Engine::GetEngineInfo("gsp")){
        include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/nup_buttons.html";
        $userFootBtns = getBrick();
    }

    $main = str_replace("{PROFILE_ERRORS_INFO}", $userPageErrors, $main);
    $main = str_replace("{PROFILE_FOOTER_BTNS}", $userFootBtns, $main);
}

if ($session === true) {
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

    $main = str_replace_once("{PROFILE_REPUTATIONER:STYLESHEET}", "<link rel=\"stylesheet\" href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/reputationer-style.css\">", $main);
    $main = str_replace_once("{PROFILE_PAGE:USER_REPUTATION_AONCLICK}", "onclick=\"$('#reputation-frame').show();\"", $main);
    $main = str_replace_once("{PROFILE_REPUTATIONER_BLOCK}", $reputationerBlock, $main);
    $main = str_replace_once("{PROFILE_UPLOADER_BLOCK}", $uploaderBlock, $main);
    $main = str_replace("{PROFILE_JS:SHOW_PANEL}", "showPanel('$parentDivName');" . PHP_EOL . "showSubpanel('$parentDivName', $subpanelDivNumber);", $main);
}

if (($session !== true && !\Engine\Engine::GetEngineInfo("gsp") && $user !== false)
    || (!empty($user) && !$user->IsAccountPublic() && $session !== true)
    || (!empty($user) && !$user->IsAccountPublic() && !$user->FriendList()->isFriend($_SESSION["uid"]))){

    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}", "Ограничение доступа", $header);

    $main = str_replace_once("{PROFILE_PAGE_TITLE}", "Ограничение доступа - " . \Engine\Engine::GetEngineInfo("sn"), $main);
    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", $profileSeeErrors, $main);
    $main = str_replace_once("{PROFILE_MAIN_BODY}", null, $main);

}

/*********************************************************************************************/

/***********************************Block profile page if user is not exist.*****************/

if (!$session && empty($user)){
    $header = str_replace_once("{PROFILE_PAGE:PAGE_NAME}", "Авторизация", $header);

    $main = str_replace_once("{PROFILE_PAGE_TITLE}", "Авторизация - " . \Engine\Engine::GetEngineInfo("sn"), $main);

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authscript.js";
    $authJS = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/autherrors.phtml";
    $authErrors = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/auth.html";
    $authForm = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authactivation.html";
    $authActivateForm = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authsignup.html";
    $authSignUpForm = getBrick();

    include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/authregerrors.phtml";
    $authRegErrors = getBrick();

    $main = str_replace_once("{PROFILE_PAGE_SEE_ERRORS}", (!empty($_REQUEST["res"]) && !in_array($_REQUEST["res"], ["ic", "nc", "nr"])) ? $authErrors : "", $main);
    $main = str_replace_once("{PROFILE_MAIN_BODY}", $authForm, $main);
    $main = str_replace_once("{PROFILE_PAGE_GUI_SCRIPT}", "", $main);
    $main = str_replace_once("{AUTH_PAGE:ACCOUNT_ACTIVATOR}", $authActivateForm, $main);
    $main = str_replace_once("{AUTH_PAGE:SIGN_UP}", $authSignUpForm, $main);
    $main = str_replace_once("{AUTH_PAGE:RULES}", html_entity_decode(\Engine\Engine::CompileBBCode(file_get_contents("./engine/config/rules.sfc", FILE_USE_INCLUDE_PATH))), $main);
    $main = str_replace_once("{AUTH_PAGE:REGISTER_ERRORS}", $authRegErrors, $main);
    $emailTipText = "Указывайте действующий Email. Это нужно, чтобы в случае потери доступа к аккаунту, Вы могли его восстановить.<br>";
    if (\Engine\Engine::GetEngineInfo("na")) $emailTipText .= "Администрация требует подтверждения регистрации. После окончания регистрации,
                                Вам на данный адрес придёт письмо с ссылкой для активации аккаунта. Также, Вы можете активировать аккаунт другим способом, следуя 
                                приложенной инструкции.";
    $main = str_replace_once("{AUTH_PAGE:EMAIL_TIP}", $emailTipText, $main);
    $main = str_replace_once("{AUTH_PAGE:CAPTCHA_PIC}", "<img src=\"$captchaImgPath\">", $main);
    $main = str_replace_once("{AUTH_PAGE:CAPTCHA_ID}", $captchaID, $main);
    $main = str_replace_once("{AUTH_PAGE:UID_INPUT_PLACEHOLDER}", \Engine\Engine::GetEngineInfo("na") ? "Email или никнейм" : "Никнейм", $main);

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

ob_end_clean();

echo $main;

?>
