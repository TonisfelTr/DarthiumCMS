<?php

if (!function_exists("getBrick") && !function_exists("str_replace_once")) {
    function getBrick()
    {
        $e = ob_get_contents();
        ob_clean();
        return $e;
    }

    function str_replace_once($search, $replace, $text)
    {
        $pos = strpos($text, $search);
        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }
}

include_once "site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/error/main.html";

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
    $errorMain = str_replace_once("{ERROR_MANAGER:MODE_TIP}", "" ,$errorMain);

$errorMain = str_replace_once("{ERROR_MANAGER:EXCEPTION_FORMATED_TEXT}", nl2br($exception->getTraceAsString()), $errorMain);
echo $errorMain;

ob_end_flush();

\Guards\Logger::addVisitLog("I ran into an error. See the error log for details.");

exit;