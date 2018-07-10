<!DOCTYPE html>
<?php
include_once "./engine/main.php";
\Engine\Engine::LoadEngine();
$sessionRes = \Users\UserAgent::SessionContinue();
if (\Engine\Engine::GetEngineInfo("ss") == 1) header("Location: index.php");
if ($sessionRes === True) $user = new \Users\User($_SESSION["uid"]);
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
    <h2>:( Сайт выключен</h2>
    <h4>Сейчас сайт находится на технических работах. Администрация сайта приносит извинения за технические неудобства.<br>
    Не волнуйтесь и не грустите! Скоро он снова будет работать и лучше прежнего! :)</h4>

    <?php if (isset($user) && $user->UserGroup()->getPermission("offline_visiter") == 1){ ?>
    <p>На самом деле, у Вас есть доступ на сайт, даже если он выключен. Тыкните <a href="index.php">сюда</a>, чтобы перейти.</p>
    <?php } ?>
</body>
</html>