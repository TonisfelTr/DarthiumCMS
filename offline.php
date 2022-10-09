 <!DOCTYPE html>
<?php
include "engine/classes/engine/Engine.php";
\Engine\Engine::LoadEngine();
$sessionRes = \Users\UserAgent::SessionContinue();
if (\Engine\Engine::GetEngineInfo("ss") == 1) header("Location: index.php");
if ($sessionRes === True) $user = new \Users\Models\User($_SESSION["uid"]);
if (isset($user)) if ($user->isBanned()) { header("Location: banned.php"); exit; }
if(\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){ header("Location: banned.php"); exit; }
?>
<html>
<head>
    <meta charset="utf-8">
    <link href="site/templates/Tonisfel/css/ap-style.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <title><?php echo \Engine\Engine::GetEngineInfo("sn");?></title>
</head>
<body class="offline_screen_body" style="font-family: 'PT Sans', sans-serif;">
    <h2><?php echo \Engine\LanguageManager::GetTranslation("site_is_off");?></h2>
    <h4><?php echo \Engine\LanguageManager::GetTranslation("maintenance"); ?></h4>

    <?php if (isset($user) && $user->UserGroup()->getPermission("offline_visiter") == 1){ ?>
    <p><?php echo \Engine\LanguageManager::GetTranslation("go_to_site") .
        "<a href=\"index.php\">". \Engine\LanguageManager::GetTranslation("go_to_site_link") . "</a>"
        . \Engine\LanguageManager::GetTranslation("to_redirect") . ".</p>";
     } ?>
</body>
</html>

<?php

\Guards\Logger::addVisitLog("I tried visit the site but it was offline...");