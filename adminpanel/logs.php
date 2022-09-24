<?php
if (!defined("TT_AP")){ header("Location: ../adminapanel.php?p=forbidden"); exit; }

if (!$user->UserGroup()->getPermission("logs_see")) { header("Location: ../adminpanel.php?res=1"); exit; }

?>

<div class="inner cover">
    <h1 class="cover-heading"><span class="glyphicon glyphicon-transfer"></span> <?php echo \Engine\LanguageManager::GetTranslation("logs_panel.panel_name"); ?></h1>
    <p class="lead"><?php echo \Engine\LanguageManager::GetTranslation("logs_panel.panel_description"); ?></p>
    <div class="alert alert-info">
        <span class="glyphicons glyphicons-question-sign"></span>
        <?php echo \Engine\LanguageManager::GetTranslation("logs_panel.panel_tip"); ?>
    </div>
    <textarea class="form-control logger" style="resize: vertical; height: 500px;" readonly><?php
            $logger = \Guards\Logger::GetLogged();
            for ($i = 0; $i < count($logger); $i++){
                $log = "[" . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $logger[$i]["datetime"])) . "] ";
                if ($user = \Users\UserAgent::GetUser($logger[$i]["authorId"])) {
                    if ($user === false) {
                        $log .= "[{\Engine\LanguageManager::GetTranslation('deleted_admin')}]";
                    } else {
                        $log .= $user->getNickname();
                    }
                }
                $log .= $logger[$i]["log_text"] . PHP_EOL;
                echo $log;
            }
        ?>
    </textarea>
    <hr>
    <div class="btn-group">
        <a class="btn btn-default" href="../adminpanel.php"><span class="glyphicons glyphicons-arrow-left"></span> <?php echo \Engine\LanguageManager::GetTranslation("logs_panel.back_btn"); ?></a>
    </div>
</div>
