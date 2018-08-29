<?php

$hasPerm = $user->UserGroup()->getPermission("look_statistic");
if (!$hasPerm){
    header("Location: " . TT_ADMINPANEL);
    exit;
} else {
    $staticType = \Engine\Engine::GetEngineInfo("smt"); ?>
<div class="inner cover">
    <h1 class="cover-heading">Статистика</h1>
    <p class="lead">Просмотр статистики сайта.</p>
    <?php if ($staticType == 1){ ?>
        <div class="alert alert-info">
            <span class="glyphicons glyphicons-info-sign"></span> Вы используете стронние сервисы для статистики. Чтобы просмотреть данные статистики, зайдите на портал, предоставляющий Вам сервис.
        </div>
    <?php } ?>
</div>
<?php } ?>