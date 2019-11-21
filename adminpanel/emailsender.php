<?php
if (!defined("TT_AP")){ header("Location: ../adminapanel.php?p=forbidden"); exit; }

if (!$user->UserGroup()->getPermission("bmail_sende")) { header("Location: ../adminpanel.php?res=1"); exit; }

?>

<div class="inner cover">
    <h1 class="cover-heading">Электронный почтальон</h1>
    <p class="lead">Рассылка электронной почты всем пользователям сайта.</p>
    <div class="alert alert-info">
        <span class="glyphicons glyphicons-question-sign"></span>
        Данный почтальной отправляет Email сообщения всем пользователям ресурса, в том числе и Вам.
    </div>
    <form action="adminpanel/scripts/mailpostman.php" method="post">
        <div class="input-group">
            <div class="input-group-addon">Тема сообщения</div>
            <input class="form-control" name="email-subject-input" type="text">
        </div>
        <div class="input-group">
            <div class="input-group-addon">Текст сообщения</div>
            <textarea class="form-control" name="email-text-message" style="min-height: 500px; min-width: 1250px;"></textarea>
        </div>
        <hr>
        <div class="btn-group">
            <button class="btn btn-default" name="email-send-text"><span class="glyphicons glyphicons-message-in"></span> Отправить</button>
            <a class="btn btn-default" href="../adminpanel.php"><span class="glyphicons glyphicons-step-backward"></span> Назад</a>
        </div>
    </form>
</div>
