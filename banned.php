<!DOCTYPE html>
<?php
include_once "engine/classes/engine/Engine.php";
\Engine\Engine::LoadEngine();
if (isset($_REQUEST["banned-log-out"])) {
    \Users\UserAgent::SessionDestroy();
    header("Location: index.php");
    exit;
}

$sessionRes = \Users\UserAgent::SessionContinue();
if ($sessionRes === True) $user = new \Users\Models\User((new Session(\Users\Services\FlashSession::getSessionId()))->getContent()["uid"], true);
if (!$user->isBanned() && !\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)) { header("Location: index.php"); exit; }

if ($user->isBanned()){
    if (time() > \Guards\SocietyGuard::GetBanUserParam($user->getId(), "unban_time") &&
    \Guards\SocietyGuard::GetBanUserParam($user->getId(), "unban_time") != 0){
        Guards\SocietyGuard::Unban($user->getId());
        header("Location: index.php");
        exit;
    }
    $ban = 1;
    $unbanTime = \Guards\SocietyGuard::GetBanUserParam($user->getId(), "unban_time");
    $unbanTime = ($unbanTime == 0) ? \Engine\LanguageManager::GetTranslation("permanently") : \Engine\Engine::DateFormatToRead(date("Y-m-d", $unbanTime));
}
elseif(\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)) {
    if (time() >= \Guards\SocietyGuard::GetIPBanParam($_SERVER["REMOTE_ADDR"], "unban_time") &&
        \Guards\SocietyGuard::GetIPBanParam($_SERVER["REMOTE_ADDR"], "unban_time") != 0){
        \Guards\SocietyGuard::UnbanIP($_SERVER["REMOTE_ADDR"]);
        header("Location: index.php");
        exit;
    }
    $ban = 2;
    $unbanTime = \Guards\SocietyGuard::GetIPBanParam($_SERVER["REMOTE_ADDR"], "unban_time");
    $unbanTime = ($unbanTime == 0) ? \Engine\LanguageManager::GetTranslation("permanently") : \Engine\Engine::DateFormatToRead(date("Y-m-d", $unbanTime));
} else { header("Location: index.php"); exit; }
?>
<html>
<head>
    <meta charset="utf-8">
    <link href="site/templates/Tonisfel/css/ap-style.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <title><?php echo \Engine\LanguageManager::GetTranslation("banned") . " -" . \Engine\Engine::GetEngineInfo("sn");?></title>
</head>
<body class="banned-screen-body" style="font-family: 'PT Sans', sans-serif;">
    <h2><?php echo \Engine\LanguageManager::GetTranslation("you_have_been_banned"); ?></h2>
    <?php if ($ban == 1) { ?><h4><?php echo \Engine\LanguageManager::GetTranslation("account_banned"); ?></h4> <?php }
    else { ?><h4><?php echo \Engine\LanguageManager::GetTranslation("access_denied_banned"); ?></h4><?php }?>
    <div class="banned-info-form">
        <div class="banned-info-text">
            <strong><?php echo \Engine\LanguageManager::GetTranslation("banned_reason"); ?>:</strong> <?php if ($ban == 1) print \Guards\SocietyGuard::GetBanUserParam($user->getId(), "reason");
            else print \Guards\SocietyGuard::GetIPBanParam($_SERVER["REMOTE_ADDR"], "reason");?><br>
            <strong><?php echo \Engine\LanguageManager::GetTranslation("banned_by"); ?>:</strong> <?php if ($ban == 1) print \Users\UserAgent::GetUserNick(\Guards\SocietyGuard::GetBanUserParam($user->getId(), "author"));
            else print \Users\UserAgent::GetUserNick(\Guards\SocietyGuard::GetIPBanParam($_SERVER["REMOTE_ADDR"], "author"));?><br>
            <strong><?php echo \Engine\LanguageManager::GetTranslation("unban_date"); ?>:</strong> <?php print $unbanTime;?><br>
            <?php if ($unbanTime != \Engine\LanguageManager::GetTranslation("permanently"))
            { ?><?php echo \Engine\LanguageManager::GetTranslation("unban_info"); ?><br><?php } ?>
        </div>
        <?php if ($ban == 1) { ?><form method="post" action="banned.php"><button class="button-banned-logout" type="submit" name="banned-log-out"><?php echo \Engine\LanguageManager::GetTranslation("logout"); ?></button></form><?php } ?>
    </div>
</body>
</html>
<?php

\Guards\Logger::addAccessLog("I tried visit the site but I have been banned.");
\Guards\Logger::addVisitLog("I tried to visit the site... but saw only banned page.");