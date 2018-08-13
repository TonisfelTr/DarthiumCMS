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
</div>
<?php } ?>