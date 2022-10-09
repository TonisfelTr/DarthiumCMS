<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";
\Engine\Engine::LoadEngine();

@$nickname = $_POST["profile-auth-for-nickname-input"];
@$email = $_POST["profile-auth-for-email-input"];

function Encrypt($s){
    $exp = explode("@", $s);
    for ($i = 2; $i < strlen($exp[0]); $i++){
        $exp[0][$i] = "*";
    }
    return $exp[0] . "@" . $exp[1];
}

if (!empty($nickname) && \Users\UserAgent::IsNicknameExists($nickname)) {
    $user = new \Users\Models\User(\Users\UserAgent::GetUserId($nickname));
    ob_start();
    include_once "../../../site/templates/" . Engine\Engine::GetEngineInfo("stp") . "/mailbody.html";
    $body = ob_get_contents();
    ob_end_clean();
    $newPassword = \Engine\Engine::RandomGen(8);
    $bodyMain = "<p> Вы получили данное сообщение, поскольку на нашем сайте при регистрации кто-то указал этот Email. Если это были не Вы, тогда
                              забудьте о существовании этого письма, предварительно кинув его в мусорку. А так же в небытье пустоты.</p>
                          <p> Мы получили запрос на восстановление пароля по никнейму. Мы изменим Ваш пароль на случайный,
                          который позже Вы сможете изменить в настройках безопасности Вашего профиля.</p>
                          <span class=\"mail-span\">Ваш новый пароль: </span>$newPassword
                          <p class=\"mail-link\">Не удаляйте данное сообщение до того, как Вы измените пароль!";
    $body = str_replace("{MAIL_TITLE}", "Напоминание пароля - Администрация \"" . \Engine\Engine::GetEngineInfo("sn") . "\"", $body);
    $body = str_replace("{MAIL_SITENAME}", Engine\Engine::GetEngineInfo("sn") , $body);
    $body = str_replace("{MAIL_NICKNAME_TO}", "Приветствуем!" , $body);
    $body = str_replace("{MAIL_BODY_MAIN}", $bodyMain, $body);
    $body = str_replace("{MAIL_FOOTER_INFORMATION}", "С уважением, Администрация \"" . Engine\Engine::GetEngineInfo("sn") . "\"<br>
                                                                         Все права защищены ©", $body);
    if (!\Engine\Mailer::SendMail($body, $user->getEmail(), "Напоминание пароля - Администрация \"" . Engine\Engine::GetEngineInfo("sn") . "\"")){
        echo "not sended";
        exit;
    } else {
        \Users\UserAgent::ChangeUserPassword($user->getId(), $newPassword, false);
        echo Encrypt($user->getEmail());
        exit;
    }
}

if (!empty($email) && Users\UserAgent::IsEmailExists($email)){
    $user = new \Users\Models\User(\Users\UserAgent::GetUserId($nickname));
    ob_start();
    include_once "../../../site/templates/" . Engine\Engine::GetEngineInfo("stp") . "/mailbody.html";
    $body = ob_get_contents();
    ob_end_clean();

    $newPassword = \Engine\Engine::RandomGen(8);
    \Users\UserAgent::ChangeUserPassword($user->getId(), $newPassword, false);
    $bodyMain = "<p> Вы получили данное сообщение, поскольку на нашем сайте при регистрации кто-то указал этот Email. Если это были не Вы, тогда
                              забудьте о существовании этого письма, предварительно кинув его в мусорку. А так же в небытье пустоты.</p>
                          <p> Мы получили запрос на восстановление пароля по адресу электронной почты. Мы изменим Ваш пароль на случайный,
                          который позже Вы сможете изменить в настройках безопасности Вашего профиля.</p>
                          <span class=\"mail-span\">Ваш новый пароль: </span>$newPassword
                          <p class=\"mail-link\">Не удаляйте данное сообщение до того, как Вы измените пароль!";
    $body = str_replace("{MAIL_TITLE}", "Напоминание пароля - Администрация \"" . \Engine\Engine::GetEngineInfo("sn") . "\"", $body);
    $body = str_replace("{MAIL_SITENAME}", Engine\Engine::GetEngineInfo("sn") , $body);
    $body = str_replace("{MAIL_NICKNAME_TO}", "Приветствуем!" , $body);
    $body = str_replace("{MAIL_BODY_MAIN}", $bodyMain, $body);
    $body = str_replace("{MAIL_FOOTER_INFORMATION}", "С уважением, Администрация \"" . Engine\Engine::GetEngineInfo("sn") . "\"<br>
                                                                         Все права защищены ©", $body);
    if (!\Engine\Mailer::SendMail($body, $email, "Напоминание пароля - Администрация \"" . Engine\Engine::GetEngineInfo("sn") . "\"")){
        echo "not sended";
        exit;
    } else {
        echo Encrypt($user->getEmail());
        exit;
    }
}

echo "not exist";
exit;