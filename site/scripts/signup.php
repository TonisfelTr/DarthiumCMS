<?php
require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

@$nickname = $_POST["profile-reg-nickname-input"];
@$password = $_POST["profile-reg-password-input"];
@$rePassword = $_POST["profile-reg-repassword-input"];
@$referer = $_POST["profile-reg-referer-input"];
@$email = $_POST["profile-reg-email-input"];
@$captcha = $_POST["profile-reg-captcha-input"];

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
if ($nicknameResult.$passwordResult.$emailResult.$refererResult == ("ok,ok,ok;ok,ok,ok;ok,ok;null;" || "ok,ok,ok;ok,ok,ok;ok,ok;ok;")){
    if (empty($captcha)){
        \Guards\CaptchaMen::RemoveCaptcha();
        header("Location: ../../profile.php?signup&res=nc");
        exit;
    }
    if (!\Guards\CaptchaMen::CheckCaptcha($captcha, $_POST["profile-reg-captcha-id"], 1)){
        \Guards\CaptchaMen::RemoveCaptcha();
        header("Location: ../../profile.php?signup&res=ic");
        exit;
    }

    if (\Users\UserAgent::AddUser($nickname, $password, $email, $referer,
            true, @$_POST["profile-reg-realname-input"], @$_POST["profile-reg-city-input"], @$_POST["profile-reg-sex-input"]) === TRUE) {
        $additionalFields = \Users\UserAgent::GetAdditionalFieldsList();
        for ($i = 0; $i < count($additionalFields); $i++){
            $fieldProp = $additionalFields[$i];
            if ($fieldProp["inRegister"] == 1){
                echo 1;
                if ($fieldProp["isRequied"] == 1) {
                    if (empty($_POST["profile-adfield-" . $fieldProp["id"]])){
                        header("Location: ../../profile.php?signup&res=nsnp");
                        exit;
                    } else {
                        echo 2;
                        \Users\UserAgent::SetAdditionalFieldContent(\Engine\DataKeeper::getMax("tt_users", "id"), $fieldProp["id"], $_POST["profile-adfield-" . $fieldProp["id"]]);
                    }
                } else {
                    echo 3;
                    \Users\UserAgent::SetAdditionalFieldContent(\Engine\DataKeeper::getMax("tt_users", "id"), $fieldProp["id"], $_POST["profile-adfield-" . $fieldProp["id"]]);
                }
            }
            else
                continue;
        }
        header("Location: ../../profile.php?res=sr");
        exit;
    } else {
        header("Location: ../../profile.php?signup&res=nr");
        exit;
    }
} else {
    header("Location: ../../profile.php?signup");
    exit;
}
?>