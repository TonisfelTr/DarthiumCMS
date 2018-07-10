<!DOCTYPE html>
<?php
include_once "./engine/main.php";
\Engine\Engine::LoadEngine();
if (isset($_REQUEST["banned-log-out"])) {
    \Users\UserAgent::SessionDestroy();
    header("Location: index.php");
    exit;
}

$sessionRes = \Users\UserAgent::SessionContinue();
if ($sessionRes === True) $user = new \Users\User($_SESSION["uid"]);
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
    $unbanTime = ($unbanTime == 0) ? "Перманентно" : \Engine\Engine::DateFormatToRead(date("Y-m-d", $unbanTime));
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
    $unbanTime = ($unbanTime == 0) ? "Перманентно" : \Engine\Engine::DateFormatToRead(date("Y-m-d", $unbanTime));
} else { header("Location: index.php"); exit; }
?>
<html>
<head>
    <meta charset="utf-8">
    <link href="site/templates/Tonisfel/css/ap-style.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <title><?php echo "Блокировка -" . \Engine\Engine::GetEngineInfo("sn");?></title>
</head>
<body class="banned-screen-body" style="font-family: 'PT Sans', sans-serif;">
    <h2>Вы были заблокированы.</h2>
    <?php if ($ban == 1) { ?><h4>Ваш аккаунт был заблокирован Администрацией. Если Вы не согласны с данным решением, свяжитесь с ней
    через обратную связь. Для этого, выйдете из аккаунта и воспользуйтесь формой обратной связи.</h4> <?php }
    else { ?><h4>Вы были заблокированы Администрацией. Если вы не согласны с данным решением, то попробуйте связаться с ней
    через друзей, возможно, это поможет в решении конфликта.</h4><?php }?>
    <div class="banned-info-form">
        <div class="banned-info-text">
            <strong>Вы были заблокированны по причине:</strong> <?php if ($ban == 1) print \Guards\SocietyGuard::GetBanUserParam($user->getId(), "reason");
            else print \Guards\SocietyGuard::GetIPBanParam($_SERVER["REMOTE_ADDR"], "reason");?><br>
            <strong>Решение было принятно:</strong> <?php if ($ban == 1) print \Users\UserAgent::GetUserNick(\Guards\SocietyGuard::GetBanUserParam($user->getId(), "author"));
            else print \Users\UserAgent::GetUserNick(\Guards\SocietyGuard::GetIPBanParam($_SERVER["REMOTE_ADDR"], "author"));?><br>
            <strong>Дата разблокировки аккаунта:</strong> <?php print $unbanTime;?><br>
            <?php if ($unbanTime != "Перманентно") { ?> Если дата наступила, но Вас ещё не разблокировали, значит, что Вы были заблокированы до конкретного времени. Попробуйте зайти завтра, либо позднее.<br><?php } ?>
        </div>
        <?php if ($ban == 1) { ?><form method="post" action="banned.php"><button class="button-banned-logout" type="submit" name="banned-log-out">Выйти из аккаунта</button></form><?php } ?>
    </div>
</body>
</html>