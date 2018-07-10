<?php

include "../../../engine/main.php";
\Engine\Engine::LoadEngine();

@$nickname = $_POST["nickname"];
@$password = $_POST["password"];
@$rePassword = $_POST["rePassword"];
@$referer = $_POST["referer"];
@$email = $_POST["email"];

function preg_is_correct($pattern, $haystack){
    preg_match($pattern, $haystack, $arr);
    if (reset($arr) != $haystack) return false;
    else return true;
}

$nicknameResult = "";
$emailResult = "";
$passwordResult = "";
$refererResult = "";

//Проверка никнейма.
//Не отправлен.
if (empty($nickname)){
    $nicknameResult = "not_set";
} else {
    //Неверная длина.
    if (strlen($nickname) < 4 || strlen($nickname) > 16){
        $nicknameResult = "invalid_size,";
    } else $nicknameResult = "ok,";
    //Неверный никнейм.
    if (!preg_is_correct("/[a-zA-Z0-9_-]+/", $nickname)){
        $nicknameResult .= "invalid_nickname,";
    } else $nicknameResult .= "ok,";
    //Никнейм занят.
    if(\Users\UserAgent::IsNicknameExists($nickname)){
        $nicknameResult .= "exists_nickname";
    } else $nicknameResult .= "ok";
}
$nicknameResult .= ";";
#--------------------------------------------------------------------
//Проверка Email.
//Не задан адрес.
if (empty($email)){
    $emailResult = "not_set";
} else {
    //Слишком короткий.
    if (strlen($email) < 2) {
        $emailResult = "too_small,";
    } else $emailResult = "ok,";
    //Неверный формат.
    if (!preg_is_correct("/[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+/", $email)){
        $emailResult .= "invalid_email,";
    } else $emailResult .= "ok,";
    //Если нужна активация: проверка на существование в базе.
    if (\Engine\Engine::GetEngineInfo("na")) {
        if (\Users\UserAgent::IsEmailExists($email)) {
            $emailResult .= "is_exists";
        } else $emailResult .= "ok";
    } else $emailResult .= "ok";
}
$emailResult .= ";";
#----------------------------------------------------------------------
//Проверка паролей.
//Не заданы пароли.
if (empty($password) || empty($rePassword)){
    $passwordResult = "not_set";
} else {
    if (strlen($password) < 7){
        $passwordResult .= "too_small,";
    } else $passwordResult .= "ok,";
    if ($password != $rePassword){
        $passwordResult .= "not_equal";
    } else $passwordResult .= "ok";
}
$passwordResult .= ";";
#------------------------------------------------------------------------
//Проверка реферера.
if (!empty($referer)){
    if (\Users\UserAgent::IsNicknameExists($referer)){
        $refererResult .= "ok;";
    } elseif ($nickname == $referer){
        $refererResult .= "invalid_referer;";
    }
    else $refererResult .= "not_exists;";
} else $refererResult .= "null;";
#-------------------------------------------------------------------------
echo $nicknameResult.$emailResult.$passwordResult.$refererResult;