<?php

include "../engine/main.php";
//\Engine\Engine::LoadEngine();

if (\Engine\Engine::SettingsSave("http://tonisfeltavern.com", "Таверна Тонисфели",
    "таверна, тонисфель, mmo, рассказы, квесты, игра, игры, тонисфели", 0, "Место, куда приходят слушать",
    "таверна, тонисфель, mmo, рассказы, квесты, игра, игры, тонисфели", "Russian", "Tonisfel", "+7",
    "bot.tonisfeltavern@gmail.com", "dFc2cSk2hmUA4AZP", "smtp.google.com", 465, "ssl",
    true, false, "users", 100,100,
    10*1024*1024, "gif,png,jpeg,doc,xml,zip,bmp", true, 2, 1) &&
file_put_contents("../engine/config/dbconf.sfc",
    json_encode([
        "dbName" => "ttavern",
        "dbHost" => "localhost",
        "dbLogin" => "tt_user",
        "dbPass" => "Ikhe66eNkA1"
    ]), FILE_USE_INCLUDE_PATH)) echo "Базовые настройки успешно восстановлены.";
else {
    echo "Не удалось восстановить базовые настройки.";
}