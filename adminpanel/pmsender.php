<?php
if (!defined("TT_AP")){ header("Location: ../adminapanel.php?p=forbidden"); exit; }

if (!$user->UserGroup()->getPermission("bmail_sends")) { header("Location: ../adminpanel.php?res=1"); exit; }

?>

<div class="inner cover">
    <h1 class="cover-heading"><?= \Engine\LanguageManager::GetTranslation("postman.pm_panel.page_name"); ?></h1>
    <p class="lead"><?= \Engine\LanguageManager::GetTranslation("postman.pm_panel.page_description"); ?></p>
    <div class="alert alert-info">
        <span class="glyphicons glyphicons-question-sign"></span>
        <?= \Engine\LanguageManager::GetTranslation("postman.pm_panel.page_tip"); ?>
    </div>
    <form action="adminpanel/scripts/mailpostman.php" method="post">
        <div class="input-group">
            <div class="input-group-addon"><?= \Engine\LanguageManager::GetTranslation("postman.pm_panel.subject"); ?></div>
            <input class="form-control" name="pm-subject-input" type="text">
        </div>
        <div class="input-group">
            <div class="input-group-addon"><?= \Engine\LanguageManager::GetTranslation("postman.pm_panel.text"); ?></div>
            <textarea class="form-control" name="pmmail-text-message" style="min-height: 500px; min-width: 1250px;"></textarea>
        </div>
        <hr>
        <div class="btn-group">
            <button class="btn btn-default" name="pmmail-send-text"><span class="glyphicons glyphicons-message-in"></span> <?= \Engine\LanguageManager::GetTranslation("postman.send"); ?></button>
            <a class="btn btn-default" href="../adminpanel.php"><span class="glyphicons glyphicons-step-backward"></span> <?= \Engine\LanguageManager::GetTranslation("postman.back"); ?></a>
        </div>
    </form>
</div>
