<?php

use Engine\Engine;
use Engine\LanguageManager;
use Engine\RouteAgent;
use Users\UserAgent;

if (Engine::GetEngineInfo("ss") == 1) {
    RouteAgent::redirect("/");
}

$currentUser = UserAgent::getCurrentUser();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link href="site/templates/Tonisfel/css/ap-style.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <title><?php echo Engine::GetEngineInfo("sn");?></title>
</head>
<body class="offline_screen_body" style="font-family: 'PT Sans', sans-serif;">
    <h2><?php echo LanguageManager::GetTranslation("site_is_off");?></h2>
    <h4><?php echo LanguageManager::GetTranslation("maintenance"); ?></h4>

    <?php if (UserAgent::isAuthorized()) { ?>
        <?php if ($currentUser->getUserGroup()->getPermission("offline_visiter") == 1) { ?>
        <p>
            <?php echo LanguageManager::GetTranslation("go_to_site"); ?>
            <a href="/?offline_visit"><?php echo LanguageManager::GetTranslation("go_to_site_link") ?></a>
            <?php echo LanguageManager::GetTranslation("to_redirect") ?>
        </p>
        <?php } ?>
    <?php } ?>
</body>
</html>