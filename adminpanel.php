<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/1/16
 * Time: 5:50 AM
 */
include "engine/main.php";
define("TT_AP", true);
ob_start();
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()){
    $user = new \Users\User($_SESSION["uid"]);
}
else {
    header("Location: profile.php");
    exit;
}
//Проверка на наличие доступа в АП.
if (!isset($user) || !$user->UserGroup()->getPermission("enterpanel")){ header("Location: index.php?page=errors/forbidden"); exit; }
if (isset($user)) if ($user->isBanned()) { header("Location: banned.php"); exit; }
if( \Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){ header("Location: banned.php"); exit; }
?>
<!DOCTYPE HTML>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo "Администраторская панель - " . \Engine\Engine::GetEngineInfo("sn");?></title>
    <script src="libs/js/ie-emulator.js"></script>
    <script src="libs/js/jquery-3.1.0.min.js"></script>
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="libs/bootstrap/js/bootstrap.min.js"></script>
    <link href="libs/bootstrap/css/ie10-viewport.css" rel="stylesheet">
    <link href="libs/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="libs/bootstrap/css/glyphicons-regular.css" rel="stylesheet">
    <link href="adminpanel/css/ap-style.css" rel="stylesheet">
    <link href="adminpanel/css/uploader-style.css" rel="stylesheet">
    <link href="adminpanel/css/icon.ico" rel="icon">
    <?php if (@$_GET["p"] == "staticc")
    echo "<link href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/sp-style.css\" rel=\"stylesheet\">";
    ?>
</head>
<body>
<?php include "adminpanel/subpanels/uploader.php"; ?>
<div class="wrapper">
    <div class="container">
        <!-- Static navbar -->
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <span class="navbar-brand">Навигация</span>
                </div>
                <div class="navbar-collapse collapse" id="navbar">
                    <ul class="nav navbar-nav">
                        <li <?php if (!isset($_GET["p"])) echo "class='active'"; ?>><a href="adminpanel.php">Главная</a></li>
                        <li <?php if (isset($_GET["p"])) if ($_GET["p"] == 'settings') echo "class='active'"; ?>><a href="?p=settings">Настройки</a></li>
                        <li <?php if (isset($_GET["p"])) if ($_GET["p"] == 'reports') echo "class='active'"; ?>><a href="?p=reports">Жалобы
                                <?php if (($rc = \Guards\ReportAgent::GetUnreadedReportsCount()) > 0) { ?><span class="adminpanel-reports-inc"><span class="glyphicons glyphicons-bell"></span> <?php echo $rc; ?></span><?php } ?></a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="index.php">На сайт</a></li>
                        <li><a href="profile.php"><?php echo $user->getNickname(); ?></a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div><!--/.container-fluid -->
        </nav>
        <!-- Main component for a primary marketing message or call to action -->
        <div class="jumbotron" id="jumbotron">
            <h1>Административная панель</h1>
            <p>Здесь вы можете управлять сайтом.</p>
        </div>
    </div> <!-- /container -->
    <div class="divider_header">

    </div><br>
    <?php
    #####################################################################################
    /* Раздел с ошибками. Здесь находится div, внутри которого форма для вывода ошибок. */
    #####################################################################################
    if (isset($_GET["res"])){ ?> <div class="container-fluid"><?php
        if ($_GET["res"] == "1"){ ?><div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> У вас недостаточно прав для совершенния данного действия.</div><?php }
        if ($_GET["res"] == "2"){ ?><div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> Указанной подстраницы нет в данном модуле.</div><?php }
        if (isset($_GET["p"])) {
            if ($_GET["p"] == "settings") {
                if ($_GET["res"] == "2s") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Настройки были
                        успешно сохранены!
                    </div><?php }
                if ($_GET["res"] == "2n") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> Не получилось
                        сохранить настройки полностью. Проверьте доступ к файлам на сервере, возможно, они
                        отсутствуют или доступ к ним закрыт.
                    </div><?php }
            }
            if ($_GET["p"] == "groups") {
                if ($_GET["res"] == "3se") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Итендефицирующая
                        информация группы сохранена!
                    </div><?php }
                if ($_GET["res"] == "3spc") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Права группы
                        успешно
                        изменены и сохранены!
                    </div><?php }
                if ($_GET["res"] == "3sgc") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Группа успешно
                        создана!
                    </div><?php }
                if ($_GET["res"] == "3sgd") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Группа успешно
                        удалена!
                    </div><?php }
                if ($_GET["res"] == "3nlfs") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> Имя группы
                        должно
                        содержать более 4 символов и меньше 50.
                    </div><?php }
                if ($_GET["res"] == "3nmfts") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> Имя группы
                        должно
                        содержать более 4 символов и меньше 50.
                    </div><?php }
                if ($_GET["res"] == "3ne") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> У вас
                        недостаточно прав
                        для изменения итендефицирующей информации группы.
                    </div><?php }
                if ($_GET["res"] == "3npc") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> У вас
                        недостаточно прав
                        для изменения прав группы.
                    </div><?php }
                if ($_GET["res"] == "3ngc") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> Не получилось
                        создать
                        группу.
                    </div><?php }
                if ($_GET["res"] == "3ngd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> Не получилось
                        удалить
                        группу.
                    </div><?php }
                if ($_GET["res"] == "3ndd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> Эту группу
                        нельзя удалить: она важна для работы сайта!
                    </div><?php }
                if ($_GET["res"] == "3ngs") { ?>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Вы не выбрали
                        группу.
                    </div><?php }
                if ($_GET["res"] == "3ngmm") { ?>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Не получилось
                        переместить членов удаляемой группы. Группа не была удалена.
                    </div><?php }
                if ($_GET["res"] == "3ngsd") { ?>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Нельзя удалить
                        начальную группу.
                    </div><?php }
            }
            if ($_GET["p"] == "users"){
                if ($_GET["res"] == "4ncdu") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Вы не можете удалить сами себя или профиль главного администратора.
                    </div><?php }
                if ($_GET["res"] == "4sdu") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Пользователи были успешно удалены!
                    </div><?php }
                if ($_GET["res"] == "4ndu") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-alert"></span> Пользователи не были удалены...
                    </div><?php }
                if ($_GET["res"] == "4ndus") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-alert"></span> Пользователи не были удалены:
                        не получено ни одного пользователя для удаления.
                    </div><?php }
                if ($_REQUEST["res"] == "4nbu") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Не удалось заблокировать данного пользователя.
                    </div> <?php }
                if ($_REQUEST["res"] == "4sbu") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    Данный пользователь был успешно заблокирован.
                    </div> <?php }
                if ($_REQUEST["res"] == "4sbus") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    Данные пользователи были успешно заблокированы.
                </div> <?php }
                if ($_REQUEST["res"] == "4nbus") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    Не удалось заблокировать пользователей по шаблону "<?php echo htmlentities($_REQUEST["bnns"]); ?>".
                </div> <?php }
                if ($_REQUEST["res"] == "4nuu") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    Не удалось разблокировать данный(-ые) аккаунт(-ы).</div> <?php }
                if ($_REQUEST["res"] == "4suu") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    Данный(-ые) аккаунт(-ы) были успешно разблокирован(-ы).
                    </div> <?php }
                if ($_REQUEST["res"] == "4nbeu") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Данный(-ые) аккаунт(-ы) не существует(-ют).
                    </div> <?php }
                if ($_REQUEST["res"] == "4nibu") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Данный(-ые) аккаунт(-ы) уже заблокирован(-ы).
                </div> <?php }
                if ($_REQUEST["res"] == "4nihs") { ?><div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span>
                    Вы не заполнили строку IP-адреса.
                </div> <?php }
                if ($_REQUEST["res"] == "4sib") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    IP-адрес был успешно заблокирован.
                </div> <?php }
                if ($_REQUEST["res"] == "4nib") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    Не удалось заблокировать IP-адрес.
                </div> <?php }
                if ($_REQUEST["res"] == "4niab") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Данный IP-адрес уже заблокирован.
                </div> <?php }
                if ($_REQUEST["res"] == "4nibe") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Внутренняя ошибка исполнения. Запрос в базу данных не удался.
                </div> <?php }
                if ($_REQUEST["res"] == "4niub") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    Не удалось разблокировать IP-адрес.
                </div> <?php }
                if ($_REQUEST["res"] == "4siub") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    Данный IP-адрес был разблокирован.
                </div> <?php }
                if ($_REQUEST["res"] == "4nrnn") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Никнейм не может быть пустым.
                </div> <?php }
                if ($_REQUEST["res"] == "4nre") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    У пользователя должен быть Email.
                </div> <?php }
                if ($_REQUEST["res"] == "4nrp") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    У пользователя должен быть пароль.
                </div> <?php }
                if ($_REQUEST["res"] == "4nru") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    Не удалось зарегистрировать пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4sru") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    Пользователь "<?php echo $_REQUEST["nunn"]; ?>" был зарегистрирован!
                </div> <?php }
                if (($_REQUEST["res"] == "4nvnn") || ($_REQUEST["res"] == "4nenvn")) { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    В выбранном никнейме есть запрещённые символы. Вы можете использовать только цифры, буквы латинского алфавита и точку.
                    </div> <?php }
                if (($_REQUEST["res"] == "4nve") || ($_REQUEST["res"] == "4neve")) { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Вы ввели неверный адрес электронной почты.
                    </div> <?php }
                if (($_REQUEST["res"] == "4nnee") || ($_REQUEST["res"] == "4neee")) { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Пользователь с такими никнеймом или email уже есть.
                    </div> <?php }
                if (($_REQUEST["res"] == "4nne") || ($_REQUEST["res"] == "4neenn")) { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Пользователь таким никнеймом уже есть.
                    </div> <?php }
                if ($_REQUEST["res"] == "4ncsafc") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось поменять содержимое дополнительных полей.
                </div> <?php }
                if ($_REQUEST["res"] == "4nep") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось поменять пароль данного пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4nef") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить графу "Откуда".
                </div> <?php }
                if ($_REQUEST["res"] == "4nev") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить VK ID пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4nes") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить Skype пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4nesx") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить графу "Пол" пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4nern") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить графу "Настоящее имя" пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4nebd") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить графу "Дата рождения" пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4nehs") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить список хобби пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4nea") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить графу "Обо мне" пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4nesg") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить подпись пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4neav") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Не удалось изменить аватарку пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4neavvf") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Этот файл не может быть аватаркой.
                </div> <?php }
                if ($_REQUEST["res"] == "4neavvs") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Аватарка имеет неправильные размеры. Убедитесь, что она удовлетворяет требованиям.
                </div> <?php }
                if ($_REQUEST["res"] == "4neavvb") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    Аватарка весит больше 6 мегабайт.
                </div> <?php }
                if ($_REQUEST["res"] == "4seu") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    Изменения были сохранены!
                </div> <?php }
                if ($_REQUEST["res"] == "4sua") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    Аккаунт пользователя "<?php echo \Users\UserAgent::GetUserNick($_REQUEST["uid"]); ?>" был активирован.
                </div> <?php }
                if ($_REQUEST["res"] == "4nua") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    Не удалось активировать данного пользователя.
                </div> <?php }
                if ($_REQUEST["res"] == "4neu") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    Такого пользователя не существует.
                </div> <?php }
                if ($_REQUEST["res"] == "4neae") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    Такой Email уже зарегистрирован.
                </div> <?php }
            }
            if ($_GET["p"] == "report"){
                if ($_GET["res"] == "5nrid") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не указан уникальный номер жалобы для совершения данного действия.
                    </div><?php }
                if ($_GET["res"] == "5nnas") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не указан уникальный номер ответа для совершения данного действия.
                    </div><?php }
                if ($_GET["res"] == "5ncr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Не получилось назначить данный ответ как решение проблемы жалобы.
                    </div><?php }
                if ($_GET["res"] == "5nmt") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Вы не указали текст Вашего ответа.
                    </div><?php }
                if ($_GET["res"] == "5ntsm") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Текст Вашего ответа слишком короткий. Он должен быть больше 4 символов в длину.
                    </div><?php }
                if ($_GET["res"] == "5nad") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не удалось опубликовать Ваш ответ.
                    </div><?php }
                if ($_GET["res"] == "5ntr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Жалобы, для которой Вы хотите совершить данное действие, не существует.
                    </div><?php }
                if ($_GET["res"] == "5nta") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Ответ, для которого Вы хотите совершить данное действие, не существует.
                    </div><?php }
                if ($_GET["res"] == "5ncds") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Нельзя удалить ответ, который является решением жалобы!
                    </div><?php }
                if ($_GET["res"] == "5sda") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Ответ был успешно удалён!
                    </div><?php }
                if ($_GET["res"] == "5nda") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Не удалось удалить ответ.
                    </div><?php }
                if ($_GET["res"] == "5naacr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Нельзя совершать данное действие с закрытой жалобой.
                    </div><?php }
                if ($_GET["res"] == "5nroai") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Не указан уникальный номер ответа или жалобы для редактирования.
                    </div><?php }
                if ($_GET["res"] == "5sad") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Ваш ответ был успешно опубликован.
                    </div><?php }
                if ($_GET["res"] == "5scr") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Данный ответ был помечен, как решение данной проблемы. Жалоба была закрыта.
                    </div><?php }
                if ($_GET["res"] == "5sea") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Данный ответ был успешно отредактирован.
                    </div><?php }
                if ($_GET["res"] == "5ser") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Текст данной жалобы был успешно отредактирован.
                    </div><?php }
                if ($_GET["res"] == "5sdr") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Жалоба успешно удалена.
                    </div><?php }
                if ($_GET["res"] == "5ndr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Не удалось удалить жалобу.
                    </div><?php }
                if ($_GET["res"] == "5nea") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не удалось отредактировать ответ.
                    </div><?php }
                if ($_GET["res"] == "5ner") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не удалось изменить текст жалобы.
                    </div><?php }
                if ($_GET["res"] == "5ne") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Жалобы с таким номером нет в системе.
                    </div><?php }
                if ($_GET["res"] == "5nsrd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не выделены жалобы для удаления.
                    </div><?php }
                if ($_GET["res"] == "5ndsr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не удалось удалить какую-то жалобу.
                    </div><?php }
                if ($_GET["res"] == "5sdsr") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-info-sign"></span> Выделенные жалобы были удалены.
                    </div><?php }
            }
            if ($_GET["p"] == "categories") {
                if ($_GET["res"] == "6ncid") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не указан уникальный номер категории для совершения данного действия.
                    </div><?php }
                if ($_GET["res"] == "6nct") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Категории с таким уникальным номером не существует.
                    </div><?php }
                if ($_GET["res"] == "6ncc") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Не удалось создать категорию.
                    </div><?php }
                if ($_GET["res"] == "6nct") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Категории с таким уникальным номером не существует.
                    </div><?php }
                if ($_GET["res"] == "6scc") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Категория была успешно создана.
                    </div><?php }
                if ($_GET["res"] == "6nvcn") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Имя категории не может быть больше 50 символов и короче 4.
                    </div><?php }
                if ($_GET["res"] == "6ncn") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Вы не ввели название категории.
                    </div><?php }
                if ($_GET["res"] == "6ncd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Вы не ввели описание категории.
                    </div><?php }
                if ($_GET["res"] == "6sce") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Категория была успешно отредактирована.
                    </div><?php }
                if ($_GET["res"] == "6scdt") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> Категория(-и) была(-и) успешно удалена(-ы).
                    </div><?php }
                if ($_GET["res"] == "6ncdt") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> Не удалось удалить категорию(-и).
                    </div><?php }

            }
            if ($_GET["p"] == "staticc") {
                if ($_GET["res"] == "7nsn") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Вы не ввели название страницы.
                    </div><?php }
                if ($_GET["res"] == "7nbn") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Название страницы слишком маленькое. Оно должно быть больше 4 символов в длину.
                    </div><?php }
                if ($_GET["res"] == "7nst") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не указан уникальный номер категории для совершения данного действия.
                    </div><?php }
                if ($_GET["res"] == "7nbt") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не указан уникальный номер категории для совершения данного действия.
                    </div><?php }
                if ($_GET["res"] == "7ntbd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove-sign"></span> Описание страницы слишком длинное. Оно не должно превышать 100 символов.
                    </div><?php }
                if ($_GET["res"] == "7ncp") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не удалось создать статическую страницу...
                    </div><?php }
                if ($_GET["res"] == "7npe") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Такой страницы не существует.
                    </div><?php }
                if ($_GET["res"] == "7ndsp") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не получилось удалить некоторые страницы. Попробуйте ещё раз.
                    </div><?php }
                if ($_GET["res"] == "7npse") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Не удалось сохранить изменения статической страницы...
                    </div><?php }
                if ($_GET["res"] == "7nspe") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Какой-то из выбранных для удаления страниц уже не существует. Обновите страницу и попробуйте ещё раз.
                    </div><?php }
                if ($_GET["res"] == "7nssan") { ?>
                    <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> Вы не ввели никнейм автора.
                    </div><?php }
                if ($_GET["res"] == "7nssn") { ?>
                    <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> Вы не ввели название статической страницы.
                    </div><?php }
                if ($_GET["res"] == "7scp") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span> Статическая страница была успешно создана!
                    </div><?php }
                if ($_GET["res"] == "7sphbe") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span> Статическая страница успешно отредактирована.
                    </div><?php }
                if ($_GET["res"] == "7srsp") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span> Выделенные статические страницы были успешно удалены!
                    </div><?php }
            }
        }
    ?></div><?php }
    ################################################
    /* Проверка на наличие раздела у админ панели */
    ################################################
    if (!isset($_GET["p"])) { ?>
    <div class="container-fluid">
        <div class="center">Настройки сайта и движка</div>
        <hr />
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("change_engine_settings")) {?>
            <div class="linker">
                <a class="linkin" href="?p=settings"><span class="glyphicon glyphicon-cog"></span> Настройки</a>
                <p class="helper">Настройки сайта и движка.</p>
            </div>
            <?php } ?>
            <?php if ($user->UserGroup()->getPermission("change_design")) {?>
            <div class="linker">
                <a class="linkin" href="?p=design"><span class="glyphicons glyphicons-magic"></span> Дизайн</a>
                <p class="helper">Изменение шаблонов сайта.</p>
            </div>
            <?php } ?>
        </div>
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("report_talking") &&
            $user->UserGroup()->getPermission("report_foreign_remove") &&
            $user->UserGroup()->getPermission("report_foreign_edit") &&
            $user->UserGroup()->getPermission("report_close")
            ) { ?>
            <div class="linker">
                <a class="linkin" href="?p=reports"><span class="glyphicon glyphicon-fire"></span> Жалобы</a>
                <p class="helper">Управление жалобами игроков.</p>
            </div> <?php } ?>
        </div>
    </div><br />
    <div class="container-fluid">
        <div class="center">Управление пользователями</div>
        <hr />
        <div class="col-lg-6">
            <div class="linker">
                <a class="linkin" href="?p=users"><span class="glyphicon glyphicon-user"></span> Пользователи</a>
                <p class="helper">Создание, удаление, редактирование и блокировка пользователей сайта.</p>
            </div>
        </div>
        <?php if ($user->UserGroup()->getPermission("group_change") ||
                  $user->UserGroup()->getPermission("group_create") ||
                  $user->UserGroup()->getPermission("group_delete") ||
                  $user->UserGroup()->getPermission("change_perms")) {?>
        <div class="col-lg-6">
            <div class="linker">
                <a class="linkin" href="?p=groups"><span class="glyphicons glyphicons-group"></span> Группы</a>
                <p class="helper">Управление привилегиями групп и их списком.</p>
            </div>
        </div>
        <?php } ?>
    </div><br/>
    <div class="container-fluid">
        <div class="center">Управление контентом</div>
        <hr />
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("rules_edit")) {?>
            <div class="linker">
                <a class="linkin" href="?p=rules"><span class="glyphicons glyphicons-list"></span> Правила</a>
                <p class="helper">Редактирование правил сайта. Они будут показаны при регистрации.</p>
            </div> <?php } if ($user->UserGroup()->getPermission("category_create") ||
            $user->UserGroup()->getPermission("category_edit") ||
            $user->UserGroup()->getPermission("category_delete")){?>
            <div class="linker">
                <a class="linkin" href="?p=categories"><span class="glyphicons glyphicons-show-thumbnails"></span> Категории</a>
                <p class="helper">Управление категориями сайта: их создание, удаление и манипуляции.</p>
            </div>
            <?php } ?>
        </div>
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("sc_create_pages") ||
                        $user->UserGroup()->getPermission("sc_edit_pages") ||
                        $user->UserGroup()->getPermission("sc_remove_pages") ||
                        $user->UserGroup()->getPermission("sc_design_edit")) { ?>
            <div class="linker">
                <a class="linkin" href="?p=staticc"><span class="glyphicons glyphicons-pen"></span> Управление статическим контентом</a>
                <p class="helper">Добавление, удаление и редактирование статического контента сайта.</p>
            </div>
            <?php } ?>
        </div>
    </div>
    <br>
    <?php if ($user->UserGroup()->getPermission("bmail_sende") ||
              $user->UserGroup()->getPermission("bmail_sends") ) { ?>
    <div class="container-fluid">
        <div class="center">Рассылка сообщений</div>
        <hr>
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("bmail_sende")) { ?>
            <div class="linker">
                <a class="linkin" href="?p=emailsender"><span class="glyphicons glyphicons-file"></span> Электроннный почтальон</a>
                <p class="helper">Отправка email сообщений всем пользователям.</p>
            </div>
            <?php } ?>
        </div>
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("bmail_sends")) { ?>
            <div class="linker">
                <a class="linkin" href="?p=pmsender"><span class="glyphicons glyphicons-file-cloud"></span> Почтовик сайта</a>
                <p class="helper">Отправка личных сообщений каждому зарегистрированному пользователю.</p>
            </div>
            <?php } ?>
        </div>
    </div>
        <?php } ?>
    <?php } else { ?>
    <div class="container-fluid">
       <?php if (file_exists("adminpanel/".$_GET["p"].".php")) include_once "adminpanel/".$_GET["p"].".php";
             elseif ($_GET["p"] == "forbidden") include_once "adminpanel/errors/forbidden.php";
             else include_once "adminpanel/errors/notfound.php";?>
    </div>
    <?php } ?>
</div>
<div class="footer">
    <p class="footer">
        Tonisfel Tavern CMS.<br>
        Администраторская панель.<br>
        Разработчик НЕ поддерживает изменение исходного кода частей данной панели.<br>
        Все дополнения для админ-панели являются неофициальными.<br>
        Разработчик - Багданов Илья.<br>
        Все права защищены ©.<br>
    </p>
</div>
</body>
<?php ob_end_flush(); ?>
</html>

