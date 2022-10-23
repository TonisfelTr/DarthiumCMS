<?php

namespace Engine;

class ErrorManager
{
    static private $lastError = 999;
    static private $errors = array(
        999 => "Engine has no errors",
        0 => "The engine cannot be started.",
        1 => "Database data is not set.",
        2 => "MYSQL connection has not been established.",
        3 => "These nickname or email are already exist.",
        4 => "This nickname is already exist.",
        5 => "This banned-var is exist.",
        6 => "This banned-var is not exist.",
        7 => "This account is not exist.",
        8 => "Captcha is not created",
        9 => "STMT error in query for SQL",
        10 => "Group with that ID is not exist.",
        11 => "That user id is not exist.",
        12 => "File is not exist.",
        13 => "This file can't be uploaded.",
        14 => "Permission denied.",
        15 => "Group name is too small.",
        16 => "Group name is too long",
        17 => "Group with that name is already exist.",
        18 => "That file cannot be the avatar.",
        19 => "Picture has not needed sizes.",
        20 => "This picture has size more then 6 MB",
        21 => "Nickname has invalid symbols.",
        22 => "Email has invalid symbols.",
        23 => "This referer is not exist.",
        24 => "Denial of service. Stolen session!",
        25 => "Invalid UID or PWD",
        26 => "This account is not active.",
        27 => "This file has too much size.",
        28 => "There is no file to upload.",
        29 => "This report is not exist.",
        30 => "This answer for report is not exist.",
        31 => "This answer is a solve of one report.",
        32 => "This category is not exist.",
        33 => "Error in MySQL query",
        34 => "This email is already exist.",
        /*****************************************/
        /* Errors of static content              */
        /*****************************************/
        35 => "This panel is not exist.",
        36 => "This IP address is registered",
        /****************************************/
        /* Errors of plugin manager.            */
        /****************************************/
        37 => "Plugin with that constant already exists!",
        38 => "Plugin has no languages files.",
        40 => "Plugin doesn't have configuration file.",
        /*-------------------------------------*/
        39 => "Syntax error in SQL code"
    );

    static public function GenerateError($errorCode)
    {

        ErrorManager::$lastError = $errorCode;

    }

    static public function GetError()
    {
        return self::$lastError;
    }

    static public function GetErrorCode($error)
    {
        if (self::$lastError == 999) return False;
        return array_search($error, self::$errors);
    }

    public static function PretendToBeDied($lastText, \Exception $exception)
    {
        function getBrick(){
            $e = ob_get_contents();
            ob_clean();
            return $e;
        }

        function str_replace_once($search, $replace, $text){
            $pos = strpos($text, $search);
            return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
        }

        ob_start();
        include_once Engine::ConstructTemplatePath("main", "error");
        $excCatcher = getBrick();

        $excCatcher = str_replace("{ERROR_CODE}", ErrorManager::GetError(), $excCatcher);
        $excCatcher = str_replace("{SITE_NAME}", Engine::GetEngineInfo("sn"), $excCatcher);
        $excCatcher = str_replace("{ERROR_MANAGER:EXCEPTION_FORMATED_TEXT}", nl2br($exception->getTraceAsString()), $excCatcher);
        $excCatcher = str_replace("{ERROR_MANAGER:MESSAGE}", $exception->getMessage(), $excCatcher);
        $excCatcher = str_replace("{ERROR_MANAGER:LASTTEXT}", $lastText, $excCatcher);
        echo $excCatcher;
        exit(1);
    }
}