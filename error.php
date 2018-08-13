<?php

include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/error/main.html";
$errorMain = getBrick();

$errorMain = str_replace_once("{ERROR_CODE}", \Engine\ErrorManager::GetError(), $errorMain);
$errorMain = str_replace_once("{ENGINE_META:DESCRIPTION}", \Engine\Engine::GetEngineInfo("ssc"), $errorMain);
$errorMain = str_replace_once("{ENGINE_META:KEYWORDS}", \Engine\Engine::GetEngineInfo("sh"), $errorMain);

if (isset($lastText))
    $errorMain = str_replace_once("{ERROR_MANAGER:MESSAGE}", $lastText, $errorMain);
else
    $errorMain = str_replace_once("{ERROR_MANAGER:MESSAGE}", \Engine\ErrorManager::GetErrorCode(\Engine\ErrorManager::GetError()), $errorMain);

if ($itisjoke)
    $errorMain = str_replace_once("{ERROR_MANAGER:MODE_TIP}", "<p>$itisjoke</p>", $errorMain);
else
    $errorMain = str_replace_once("{ERROR_MANAGER:MODE_TIP}", "", $errorMain);
echo $errorMain;

$errorMain = str_replace_once("{ERROR_MANAGER:EXCEPTION_FORMATED_TEXT}", $exception->getTrace(), $errorMain);
ob_end_flush();
exit;