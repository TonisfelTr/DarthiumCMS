<?php $pageName = "Правила"; ?>
<h3><?=\Engine\LanguageManager::GetTranslation("rules")?>></h3>
<hr>
<?php
$rules = file_get_contents("engine/config/rules.sfc", FILE_USE_INCLUDE_PATH);
echo html_entity_decode(\Engine\Engine::CompileBBCode($rules));
?>