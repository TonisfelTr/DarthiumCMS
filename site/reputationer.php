<?php
$accessType =  ($user->getId() != $_SESSION["uid"]) ? true : false;
if ($accessType){
    $captchaId = \Guards\CaptchaMen::GenerateCaptcha();
    $captchaImgPath = \Guards\CaptchaMen::GenerateImage(\Guards\CaptchaMen::FetchCaptcha(4));
}
if (!empty($_GET["res"]) && in_array($_GET["res"], ["yuid", "sarp", "narp", "nvc"])) $hidden = false;
else $hidden = true;

if (!empty($_GET["res"])){
    $reputationerError = "";
    switch($_GET["res"]){
        case "yuid":
            $reputationerError = "<span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("reputationer.you_cannot_change_rep_yourself");
            break;
        case "sarp":
            $reputationerError = "<span class=\"glyphicon glyphicon-ok\"></span> " . \Engine\LanguageManager::GetTranslation("reputationer.mark_added_success");;
            break;
        case "narp":
            $reputationerError = "<span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("reputationer.mark_added_failed");;
            break;
        case "nvc":
            $reputationerError = "<span class=\"glyphicon glyphicon-warning-sign\"></span> ". \Engine\LanguageManager::GetTranslation("reputationer.invalid_captcha");
            break;
        case "nchot":
            $reputationerError = "<span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("reputationer.you_cannot_change_rep_more_one_time");
            break;
    }
}

include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/reputationermain.html";
$reputationerBlock = getBrick();

$reputationerBlock = str_replace("{PROFILE_REPUTATIONER:HIDDEN_ATTR}", $hidden ? "hidden" : "", $reputationerBlock);
$reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:INFO_BLOCK}", !empty($_GET["res"]) ? $reputationerError : "", $reputationerBlock);

$reputationerAddBlock = "";
include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/reputationeradd.html";
$reputationerAddBlock = getBrick();
if ($accessType) {
    $reputationerAddBlock = str_replace_once("{PROFILE_REPUTATIONER:UID}", $user->getId(), $reputationerAddBlock);
    $reputationerAddBlock = str_replace_once("{PROFILE_REPUTATIONER:CAPTCHA_IMG}", $captchaImgPath, $reputationerAddBlock);
    $reputationerAddBlock = str_replace_once("{PROFILE_REPUTATIONER:CAPTCHA_ID}", $captchaId, $reputationerAddBlock);
    if (\Engine\Engine::GetEngineInfo("vmr") && !empty($_SESSION) && $user->getReputation()->getPointsFromUserCount($_SESSION["uid"]) == 0) {
        $reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_ADD}", $reputationerAddBlock, $reputationerBlock);
    } elseif (!\Engine\Engine::GetEngineInfo("vmr")) {
        $reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_ADD}", $reputationerAddBlock, $reputationerBlock);
    } elseif (empty($_SESSION)){
        $reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_ADD}", "", $reputationerBlock);
    } else {
        $reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_ADD}", "<div class=\"alert alert-warning\"><span class=\"glyphicons glyphicons-warning-sign\"></span> ". \Engine\LanguageManager::GetTranslation("reputationer.change_rep_only_one_time_tip") . "</div>", $reputationerBlock);
    }
}
$reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_ADD}", "", $reputationerBlock);
//Для избежания взаимодействия пользователей с системой замены, пользовательские данные будут вставлены после операций замены.
$reputationerList = "";
if (count($user->getReputation()->getReputationArray()) == 0)
    $reputationerList = "<span class=\"glyphicon glyphicon-info-sign\"></span> " . (($accessType) ? \Engine\LanguageManager::GetTranslation("reputationer.reputation") . " " . $user->getNickname() . " " : \Engine\LanguageManager::GetTranslation("reputationer.your_reputation") . " ") . \Engine\LanguageManager::GetTranslation("reputationer.nobody_change");
else {
    for ($i = 1; $i <= count($user->getReputation()->getReputationArray()); $i++){
        $userRCGenderEnding = \Users\UserAgent::GetUserParam($user->getReputation()->getReputationArray()[$i]["authorId"], "sex") == 2 ? \Engine\LanguageManager::GetTranslation("reputationer.she_changed") : \Engine\LanguageManager::GetTranslation("reputationer.he_changed");
        $userRCUID = $user->getReputation()->getReputationArray()[$i]["authorId"];
        $userRCNickname = \Users\UserAgent::GetUserNick($user->getReputation()->getReputationArray()[$i]["authorId"]);
        $userRCMark = ($user->getReputation()->getReputationArray()[$i]["type"] == 1) ? "<span style=\"color: green;\">" . \Engine\LanguageManager::GetTranslation("reputationer.mark_positive") . "</span>" : "<span style=\"color: darkred;\">" . \Engine\LanguageManager::GetTranslation("reputationer.mark_negative") . "</span>";
        $userRCDate = \Engine\Engine::DateFormatToRead(date("Y-m-d", $user->getReputation()->getReputationArray()[$i]["createDate"]));
        $userRCComment = htmlentities(\Engine\Engine::MakeUnactiveCodeWords($user->getReputation()->getReputationArray()[$i]["comment"]));
        $reputationerList .= "<div class=\"reputation-change-info\">";
        $reputationerList .= "$userRCGenderEnding: ";
        $reputationerList .= "<a href=\"profile.php?uid=$userRCUID\">$userRCNickname</a><br>";
        $reputationerList .= \Engine\LanguageManager::GetTranslation("reputationer.mark") . ": $userRCMark<br>";
        $reputationerList .= \Engine\LanguageManager::GetTranslation("reputationer.date_from") . ": $userRCDate<br>";
        $reputationerList .= \Engine\LanguageManager::GetTranslation("reputationer.comment") . ": $userRCComment";
        $reputationerList .= "</div>";
    }
}
$reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_LIST}", $reputationerList, $reputationerBlock);

include "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/reputationerscript.js";
$reputationBlockJS = getBrick();
$main = str_replace_once("{PROFILE_REPUTATIONER:JS}", $reputationBlockJS, $main);
?>