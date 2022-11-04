<?php

namespace Engine;

use Exceptions\Exemplars\InvalidErrorCodeError;
use Exceptions\TavernException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionException;
use Throwable;

class ErrorManager
{
    private const ERRORS_DESCRIPTIONS = [
        999 => "Engine has no errors",
        0 => "The engine cannot be started.",
        1 => "Database data is not set.",
        2 => "Database connection has not been established.",
        3 => "These nickname or email are already exist.",
        4 => "This nickname is already exist.",
        5 => "This banned-var is exist.",
        6 => "This banned-var is not exist.",
        7 => "This account does not exist.",
        8 => "Captcha is not created",
        9 => "STMT error in query for SQL",
        10 => "Group with that ID is not exist.",
        11 => "That user id is not exist.",
        12 => "File is not exist.",
        13 => "This file can't be uploaded.",
        14 => "Permission denied.",
        15 => "Group name is too short.",
        16 => "Group name is too long",
        17 => "Group with that name is already exist.",
        18 => "That file cannot be the avatar.",
        19 => "Picture has not needed sizes.",
        20 => "This picture has too big weight",
        21 => "Nickname has invalid symbols.",
        22 => "Email has invalid symbols.",
        23 => "This referrer is not exist.",
        24 => "Denial of service. Stolen session!",
        25 => "Invalid UID or PWD",
        26 => "This account is not active.",
        27 => "This file has too much weight.",
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
        39 => "Plugin doesn't found",
        40 => "Plugin doesn't have configuration file.",
        /*-------------------------------------*/
        41 => "This page doesn't exist",
    ];

    public const EC_SUCCESS = 999;
    public const EC_ENGINE_START_FAILED = 0;
    public const EC_INVALID_DATABASE_DATA = 1;
    public const EC_DATABASE_CONNECTION_FAILED = 2;
    public const EC_FIRST_IDENTIFIER_EXISTS = 3;
    public const EC_NICKNAME_EXISTS = 4;
    public const EC_BANNED_VAR_EXISTS = 5;
    public const EC_BANNED_VAR_NOT_EXIST = 6;
    public const EC_ACCOUNT_NOT_EXIST = 7;
    public const EC_CAPTCHA_NOT_GENERATED = 8;
    public const EC_PREPARED_STATEMENT_CONTENT_MISTAKE = 9;
    public const EC_GROUP_NOT_EXIST = 10;
    public const EC_USER_NOT_EXIST = 11;
    public const EC_FILE_NOT_EXIST = 12;
    public const EC_FILE_CANNOT_UPLOADED = 13;
    public const EC_NOT_PERMITTED = 14;
    public const EC_GROUP_NAME_TOO_SHORT = 15;
    public const EC_GROUP_NAME_TOO_LONG = 16;
    public const EC_GROUP_NAME_EXISTS = 17;
    public const EC_INVALID_AVATAR_FILE = 18;
    public const EC_INVALID_PICTURE_SIZE = 19;
    public const EC_INVALID_PICTURE_WEIGHT = 20;
    public const EC_NICKNAME_CONTAINS_INVALID_SYMBOLS = 21;
    public const EC_EMAIL_CONTAINS_INVALID_SYMBOLS = 22;
    public const EC_REFERRER_NOT_EXIST = 23;
    public const EC_STOLEN_SESSION = 24;
    public const EC_INVALID_USER_ACCESS_DATA = 25;
    public const EC_INACTIVE_ACCOUNT = 26;
    public const EC_FILE_OVERWEIGHT = 27;
    public const EC_NO_FILE_TO_UPLOAD = 28;
    public const EC_REPORT_NOT_EXIST = 29;
    public const EC_ANSWER_IN_REPORT_NOT_EXIST = 30;
    public const EC_ANSWER_IS_SOLVE = 31;
    public const EC_CATEGORY_NOT_EXIST = 32;
    public const EC_ERROR_IN_SQL_QUERY = 33;
    public const EC_EMAIL_EXISTS = 34;
    public const EC_PANEL_NOT_EXIST = 35;
    public const EC_IP_REGISTERED = 36;
    public const EC_CONSTANT_PLUGIN_OWNER_EXISTS = 37;
    public const EC_PLUGIN_NO_LANGUAGES = 38;
    public const EC_PLUGIN_NOT_FOUND = 39;
    public const EC_PLUGIN_NO_CONFIGS = 40;
    public const EC_INVALID_PAGE = 41;

    /**
     * Get PHP function documentation.
     *
     * @param string $classWithNamespace Full path to class with necessary function.
     * @param string $funcName Function name in class.
     * @return false|string PHPDoc function tip
     */
    private static function getFunctionTip(string $classWithNamespace, string $funcName) {
        try {
            $class = new ReflectionClass($classWithNamespace);
            $phpDocLines = explode("\n", $class->getMethod($funcName)->getDocComment());
            $phpDoc = "";

            foreach ($phpDocLines as &$docLine) {
                $docLine = trim($docLine);
                if ($docLine == "/**" || $docLine == "*/" || strpos($docLine, "@") != false) {
                    continue;
                }

                $docLine = trim(trim($docLine, "*"));
                if (strlen($docLine) > 0) {
                    $phpDoc .= $docLine . "<br>";
                }
            }
        } catch (ReflectionException $ex) {
            return false;
        } finally {
            return $phpDoc;
        }
    }

    private static function getFullFunctionPath(Throwable $ex) : string {
        return $ex->getTrace()[0]["class"];
    }

    public static function throwIfErrorCodeInvalid(int $code) : void {
        if ($code !== 999 && $code !== 0 && $code > self::getCountErrors()) {
            throw new InvalidErrorCodeError("There is no error with this code");
        }
    }

    public static function getErrorDescription(int $code) : string {
        self::throwIfErrorCodeInvalid($code);

        return self::ERRORS_DESCRIPTIONS[$code];
    }

    public static function getCountErrors() : int {
        return count(self::ERRORS_DESCRIPTIONS)-1;
    }

    public static function throwHandlerHtml($ex) : void {
        $isTavernError = $ex instanceof TavernException;

        $firstInTrace = $ex->getTrace()[0];

        $fileContent = file_get_contents($firstInTrace["file"]);
        $relativeFilepath = str_replace($_SERVER["DOCUMENT_ROOT"], "", $firstInTrace["file"]);
        $lineNumber = $ex->getLine();
        $functionPath = self::getFullFunctionPath($ex);
        $functionName = $firstInTrace["function"];
        $functionDoc = self::getFunctionTip($functionPath, $functionName);

        ob_start();
        include_once Engine::ConstructTemplatePath("main", "errors");
        $content = ob_get_contents();
        ob_end_clean();

        $content = Engine::replaceFirst("{ERROR_PAGE_TITLE}", $isTavernError ? "Ошибка #{$ex->getErrorCode()}" : "Ошибка", $content);
        $content = Engine::replaceFirst("{SITE_NAME}", Engine::GetEngineInfo("sn"), $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_TIP}", !$functionDoc ? "" : $functionDoc, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:MESSAGE}", $ex->getMessage(), $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:LASTTEXT}", $isTavernError
                                                                            ? ""
                                                                            : "Необработанная системная ошибка", $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:FULL_FILEPATH}", $firstInTrace["file"], $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:RELATIVE_FILEPATH}", $relativeFilepath, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:CODE}", $fileContent, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_NAME}", $functionName, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:STACKTRACE}", rtrim($ex->getTraceAsString()), $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:LINE}", $ex->getLine(), $content);

        echo $content;
    }
}