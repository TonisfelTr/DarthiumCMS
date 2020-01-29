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
            $reputationerError = "<span class=\"glyphicon glyphicon-remove\"></span> Вы не можете изменить репутацию самому себе.";
            break;
        case "sarp":
            $reputationerError = "<span class=\"glyphicon glyphicon-ok\"></span> Ваша оценка была успешно добавлена!";
            break;
        case "narp":
            $reputationerError = "<span class=\"glyphicon glyphicon-remove\"></span> Не удалось добавить вашу оценку.";
            break;
        case "nvc":
            $reputationerError = "<span class=\"glyphicon glyphicon-warning-sign\"></span> Вы ввели неверную капчу.";
            break;
    }
}

include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/reputationermain.html";
$reputationerBlock = getBrick();

$reputationerBlock = str_replace("{PROFILE_REPUTATIONER:HIDDEN_ATTR}", $hidden ? "hidden" : "", $reputationerBlock);
$reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:INFO_BLOCK}", !empty($_GET["res"]) ? $reputationerError : "", $reputationerBlock);

$reputationerAddBlock = "";
if ($accessType){
    include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/reputationeradd.html";
    $reputationerAddBlock = getBrick();

    $reputationerAddBlock = str_replace_once("{PROFILE_REPUTATIONER:UID}", $user->getId(), $reputationerAddBlock);
    $reputationerAddBlock = str_replace_once("{PROFILE_REPUTATIONER:CAPTCHA_IMG}", $captchaImgPath, $reputationerAddBlock);
    $reputationerAddBlock = str_replace_once("{PROFILE_REPUTATIONER:CAPTCHA_ID}", $captchaId, $reputationerAddBlock);
}
if ((\Engine\Engine::GetEngineInfo("vmr") == "y" && $user->getReputation()->getPointsFromUserCount($_SESSION["uid"]) == 0)
    || ($accessType && \Engine\Engine::GetEngineInfo("vmr") != "y")) {
    $reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_ADD}", $reputationerAddBlock, $reputationerBlock);
} else {
    $reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_ADD}", "<div class=\"alert alert-warning\"><span class=\"glyphicons glyphicons-warning-sign\"></span> Изменить репутацию пользователя можно только один раз.</div>", $reputationerBlock);
}

//Для избежания взаимодействия пользователей с системой замены, пользовательские данные будут вставлены после операций замены.
$reputationerList = "";
if (count($user->getReputation()->getReputationArray()) == 0)
    $reputationerList = "<span class=\"glyphicon glyphicon-info-sign\"></span> " . (($accessType) ? "Репутацию " . $user->getNickname() . " " : "Вашу репутацию ") . "ещё никто не изменил.";
else {
    for ($i = 1; $i <= count($user->getReputation()->getReputationArray()); $i++){
        $userRCGenderEnding = \Users\UserAgent::GetUserParam($user->getReputation()->getReputationArray()[$i]["authorId"], "sex") == 2 ? "a" : "";
        $userRCUID = $user->getReputation()->getReputationArray()[$i]["authorId"];
        $userRCNickname = \Users\UserAgent::GetUserNick($user->getReputation()->getReputationArray()[$i]["authorId"]);
        $userRCMark = ($user->getReputation()->getReputationArray()[$i]["type"] == 1) ? "<span style=\"color: lightgreen;\">положительная</span>" : "<span style=\"color: darkred;\">отрицательная</span>";
        $userRCDate = \Engine\Engine::DateFormatToRead(date("Y-m-d", $user->getReputation()->getReputationArray()[$i]["createDate"]));
        $userRCComment = htmlentities($user->getReputation()->getReputationArray()[$i]["comment"]);
        $reputationerList .= "<div class=\"reputation-change-info\">";
        $reputationerList .= "Изменил$userRCGenderEnding: ";
        $reputationerList .= "<a href=\"profile.php?uid=$userRCUID\">$userRCNickname</a><br>";
        $reputationerList .= "Оценка: $userRCMark<br>";
        $reputationerList .= "Поставлена: $userRCDate<br>";
        $reputationerList .= "Комментарий: $userRCComment";
        $reputationerList .= "</div>";
    }
}
$reputationerBlock = str_replace_once("{PROFILE_REPUTATIONER:REPUTATION_LIST}", $reputationerList, $reputationerBlock);

include "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/profile/reputationerscript.js";
$reputationBlockJS = getBrick();
$main = str_replace_once("{PROFILE_REPUTATIONER:JS}", $reputationBlockJS, $main);
?>