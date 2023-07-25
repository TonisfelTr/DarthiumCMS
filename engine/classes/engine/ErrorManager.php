<?php

namespace Engine;

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Builder\Services\Tag;
use Exceptions\Exemplars\EmptyOutputBufferError;
use Exceptions\Exemplars\InvalidErrorCodeError;
use Exceptions\Exemplars\TagCompilationError;
use Exceptions\TavernException;
use Guards\Logger;
use ParseError;
use ReflectionClass;
use ReflectionException;
use Throwable;

class ErrorManager
{
    private const ERRORS_DESCRIPTIONS = [
        999 => "Engine has no errors",
        0   => "The engine cannot be started.",
        1   => "Database data is not set.",
        2   => "Database connection has not been established.",
        3   => "These nickname or email are already exist.",
        4   => "This nickname is already exist.",
        5   => "This banned-var is exist.",
        6   => "This banned-var is not exist.",
        7   => "This account does not exist.",
        8   => "Captcha is not created",
        9   => "STMT error in query for SQL",
        10  => "Group with that ID is not exist.",
        11  => "That user id is not exist.",
        12  => "File is not exist",
        13  => "This file can't be uploaded.",
        14  => "Permission denied.",
        15  => "Group name is too short.",
        16  => "Group name is too long",
        17  => "Group with that name is already exist.",
        18  => "That file cannot be the avatar.",
        19  => "Picture has not needed sizes.",
        20  => "This picture has too big weight",
        21  => "Nickname has invalid symbols.",
        22  => "Email has invalid symbols.",
        23  => "This referrer is not exist.",
        24  => "Denial of service. Stolen session!",
        25  => "Invalid UID or PWD",
        26  => "This account is not active.",
        27  => "This file has too big size.",
        28  => "There is no file to upload.",
        29  => "This report is not exist.",
        30  => "This answer for report is not exist.",
        31  => "This answer is a solve of one report.",
        32  => "This category is not exist.",
        33  => "Error in MySQL query",
        34  => "This email is already exist.",
        /*****************************************/
        /* Errors of static content              */
        /*****************************************/
        35  => "This panel is not exist.",
        36  => "This IP address is registered",
        /****************************************/
        /* Errors of plugin manager.            */
        /****************************************/
        37  => "Plugin with that constant already exists!",
        38  => "Plugin has no languages files.",
        39  => "Plugin doesn't found",
        40  => "Plugin doesn't have configuration file.",
        /*-------------------------------------*/
        41  => "This page doesn't exist",
        42  => "Flash session container does not contain this key",
        43  => "Flash session content did not read",
        44  => "Sender has no file with that index",
        45  => "Invalid error code",
        46  => "Property is not available in current PHP version",
        48  => "Sent files count is one",
        49  => "File with this extension does not permitted to be uploaded",
        50  => "Cannot find file on the server storage",
        51  => "Invalid method got",
        52  => "This route cannot receive current HTML request",
        53  => "This route URL already exists",
        54  => "Invalid route name ({route_name})",
        55  => "Unavailable query parameter received",
        56  => "Route with that name already exists",
        57  => "Execute condition cannot be declared while RouteAgent has it",
        58  => "Access condition cannot be declared while RouteAgent has it",
        59  => "Invalid route URL",
        60  => "You have incorrect URL chain link syntax (url: {url_link})",
        61  => "Route URL has invalid symbols",
        62  => "Invalid properties syntax in URL chain link",
        63  => "Trying to include non-exists file",
        64  => "Absolute argument should not have any properties",
        65  => "Buffering level cannot be less then 1",
        66  => "Try to get non-exists buffering by level",
        67  => "Cannot register service tag after injecting dependencies",
        68  => "Tag with this name is registered already (tag name: {tag_name})",
        69  => "Tag name has invalid symbols",
        70  => "Tag processing function must return a string, {type} returned (tag's name - {tag_name})",
        71  => "Tag content cannot be empty",
        72  => "Tag content has invalid symbols (tag name: \"{tag_name}\", tag content: \"{tag_content}\")",
        73  => "Tag has colon but no arguments",
        74  => "One or more tag arguments have invalid symbols (tag: \"{tag_name}\", argument: \"{tag_argument}\")",
        75  => "Too few route arguments",
        76  => "Output buffering is active after URL parser job ends even",
        77  => "Too few required attributes given",
        78  => "Count of required attributes cannot be more then available",
        79  => "Service tag registering time had been expired",
        80  => "HTML tag registering time had been expired",
        81  => "Handler for URL route {route_url} is null",
        82  => "URL argument already exists in {route_url}",
        83  => "Invalid chain link name in {route_link} URL",
        84  => "URL chain link with name \"{link_name}\" doesn't exist",
        85  => "System tag registration time had been expired",
        86  => "{tag_name} tag doesn't exist",
        87  => "Cannot create hash file",
        88  => "Html tag \"{tag_name}\" has unavailable arguments",
        89  => "Html tag \"{tag_name}\" got too few required argument(s)",
        90  => "Route titling must be a string or callable in \"{route_name}\" route",
        91  => "Route title must be a string, {title_type} given",
        92  => "Invalid content for URL chain link (route: {route_url}, link: {link_name})",
        93  => "Closing tag must be a string or determinate in callback function (html tag: {htmltag_name})",
        94  => "Tags compilation hasn't been ended",
        95  => "No one compilation for tag \"{tag_name}\"",
        96  => "Engine has no parameter with name \"{parameter_name}\"",
        97  => "Route handler must be a string with path to handler file or a callable (route name: \"{route_name}\")",
        99  => "Cannot register service routes",
        100 => "Disabling output buffering is forbidden in including file (path: \"{path}\")",
        101 => "Output buffer is empty",
        102 => "Failed to start output buffering",
    ];

    public const EC_SUCCESS                                            = 999;
    public const EC_ENGINE_START_FAILED                                = 0;
    public const EC_INVALID_DATABASE_DATA                              = 1;
    public const EC_DATABASE_CONNECTION_FAILED                         = 2;
    public const EC_FIRST_IDENTIFIER_EXISTS                            = 3;
    public const EC_NICKNAME_EXISTS                                    = 4;
    public const EC_BANNED_VAR_EXISTS                                  = 5;
    public const EC_BANNED_VAR_NOT_EXIST                               = 6;
    public const EC_ACCOUNT_NOT_EXIST                                  = 7;
    public const EC_CAPTCHA_NOT_GENERATED                              = 8;
    public const EC_PREPARED_STATEMENT_CONTENT_MISTAKE                 = 9;
    public const EC_GROUP_NOT_EXIST                                    = 10;
    public const EC_USER_NOT_EXIST                                     = 11;
    public const EC_FILE_NOT_EXIST                                     = 12;
    public const EC_FILE_CANNOT_UPLOADED                               = 13;
    public const EC_NOT_PERMITTED                                      = 14;
    public const EC_GROUP_NAME_TOO_SHORT                               = 15;
    public const EC_GROUP_NAME_TOO_LONG                                = 16;
    public const EC_GROUP_NAME_EXISTS                                  = 17;
    public const EC_INVALID_AVATAR_FILE                                = 18;
    public const EC_INVALID_PICTURE_SIZE                               = 19;
    public const EC_INVALID_PICTURE_WEIGHT                             = 20;
    public const EC_NICKNAME_CONTAINS_INVALID_SYMBOLS                  = 21;
    public const EC_EMAIL_CONTAINS_INVALID_SYMBOLS                     = 22;
    public const EC_REFERRER_NOT_EXIST                                 = 23;
    public const EC_STOLEN_SESSION                                     = 24;
    public const EC_INVALID_USER_ACCESS_DATA                           = 25;
    public const EC_INACTIVE_ACCOUNT                                   = 26;
    public const EC_FILE_OVERSIZE                                      = 27;
    public const EC_NO_FILE_TO_UPLOAD                                  = 28;
    public const EC_REPORT_NOT_EXIST                                   = 29;
    public const EC_ANSWER_IN_REPORT_NOT_EXIST                         = 30;
    public const EC_ANSWER_IS_SOLVE                                    = 31;
    public const EC_CATEGORY_NOT_EXIST                                 = 32;
    public const EC_ERROR_IN_SQL_QUERY                                 = 33;
    public const EC_EMAIL_EXISTS                                       = 34;
    public const EC_PANEL_NOT_EXIST                                    = 35;
    public const EC_IP_REGISTERED                                      = 36;
    public const EC_CONSTANT_PLUGIN_OWNER_EXISTS                       = 37;
    public const EC_PLUGIN_NO_LANGUAGES                                = 38;
    public const EC_PLUGIN_NOT_FOUND                                   = 39;
    public const EC_PLUGIN_NO_CONFIGS                                  = 40;
    public const EC_INVALID_PAGE                                       = 41;
    public const EC_SESSION_NOT_CONTAIN                                = 42;
    public const EC_FLASH_SESSION_CONTENT_DID_NOT_READ                 = 43;
    public const EC_INVALID_FILE_INDEX                                 = 44;
    public const EC_INVALID_ERROR_CODE                                 = 45;
    public const EC_INVALID_PHP_VERSION                                = 46;
    public const EC_INVALID_ARGUMENT                                   = 47;
    public const EC_ONE_SENT_FILE                                      = 48;
    public const EC_NOT_PERMITTED_FILE_EXTENSION                       = 49;
    public const EC_CANNOT_FIND_TEMP_FILE                              = 50;
    public const EC_INVALID_METHOD_GOT                                 = 51;
    public const EC_INVALID_METHOD_FOR_ROUTE                           = 52;
    public const EC_DUPLICATED_ROUTE_URL                               = 53;
    public const EC_INVALID_ROUTE_NAME                                 = 54;
    public const EC_UNAVAILABLE_ROUTE_PARAMETER                        = 55;
    public const EC_DUPLICATED_ROUTE_NAME                              = 56;
    public const EC_INVALID_CONDITION_ALGORITHM                        = 57;
    public const EC_INVALID_ROUTE_URL                                  = 59;
    public const EC_INVALID_LINK_IN_URL                                = 60;
    public const EC_INVALID_SYMBOLS_IN_ROUTE_URL                       = 61;
    public const EC_INVALID_SYNTAX_OF_URL_CHAIN_LINK                   = 62;
    public const EC_FILE_NOT_FOUND                                     = 63;
    public const EC_ABSOLUTE_ARGUMENT_HAVE_PROPERTIES                  = 64;
    public const EC_INVALID_BUFFERING_INDEX                            = 65;
    public const EC_OUT_OF_RANGE_BUFFERING_LEVEL                       = 66;
    public const EC_REGISTER_SERVICE_TAGS_AFTER_INJECTING_DEPENDENCIES = 67;
    public const EC_TAG_REGISTERED_ALREADY                             = 68;
    public const EC_INVALID_TAG_NAME                                   = 69;
    public const EC_INVALID_RETURN_TYPE_OF_TAG_PROCESSING              = 70;
    public const EC_EMPTY_TAG_CONTENT                                  = 71;
    public const EC_INVALID_TAG_CONTENT                                = 72;
    public const EC_EMPTY_TAG_ARGUMENTS                                = 73;
    public const EC_INVALID_TAG_ARGUMENTS                              = 74;
    public const EC_TOO_FEW_ROUTE_ARGUMENTS                            = 75;
    public const EC_OUTPUT_BUFFERING_IS_ACTIVE_AFTER_LOADING_END       = 76;
    public const EC_TOO_FEW_REQUIRED_ATTRIBUTES_GIVEN                  = 77;
    public const EC_REQUIRED_ATTRIBUTES_COUNT_MORE_THEN_AVAILABLE      = 78;
    public const EC_SERVICE_TAG_REGISTRATION_DISABLED                  = 79;
    public const EC_HTML_TAG_REGISTRATION_DISABLED                     = 80;
    public const EC_INVALID_ROUTE_HANDLER                              = 81;
    public const EC_DUPLICATED_URL_CHAIN_LINK_NAME                     = 82;
    public const EC_INVALID_URL_CHAIN_LINK_NAME                        = 83;
    public const EC_URL_CHAIN_LINK_DOES_NOT_EXIST                      = 84;
    public const EC_SYSTEM_TAG_REGISTRATION_DISABLED                   = 85;
    public const EC_TAG_DOES_NOT_EXIST                                 = 86;
    public const EC_PAGE_HASHING_ERROR                                 = 87;
    public const EC_HTML_TAG_CONTAIN_UNAVAILABLE_ARGUMENTS             = 88;
    public const EC_HTML_TAG_CONTAIN_TOO_FEW_REQUIRED_ARGUMENTS        = 89;
    public const EC_ROUTE_TITLING_INVALID_TYPE                         = 90;
    public const EC_ROUTE_TITLE_IS_NOT_STRING                          = 91;
    public const EC_INVALID_URL_CHAIN_LINK_CONTENT                     = 92;
    public const EC_INVALID_CLOSING_TAG_ARGUMENT_TYPE                  = 93;
    public const EC_TAG_COMPILATION_DOES_NOT_ENDED                     = 94;
    public const EC_TAG_COMPILATION_HAS_NO_RESULT                      = 95;
    public const EC_INVALID_ENGINE_PARAMETER_NAME                      = 96;
    public const EC_INVALID_ROUTE_HANDLER_RESULT                       = 97;
    public const EC_INVALID_TAG_ARGUMENT_SYNTAX                        = 98;
    public const EC_ROUTES_REGISTER_ERROR                              = 99;
    public const EC_DISABLED_OUTPUT_BUFFERING                          = 100;
    public const EC_EMPTY_OUTPUT_BUFFER                                = 101;
    public const EC_FAILED_START_OUTPUT_BUFFERING                      = 102;

    private static function relativePath(string $path) : string {
        $hostDir = $_SERVER["DOCUMENT_ROOT"];
        $hostDir = explode("/", $hostDir);
        $hostDir = array_slice($hostDir, 0, -1);
        $hostDir = implode("/", $hostDir) . '/';

        $relativePath = substr($path, strlen($hostDir));
        return $relativePath;
    }

    /**
     * Get PHP function documentation.
     *
     * @param string $classWithNamespace Full path to class with necessary function.
     * @param string $funcName           Function name in class.
     * @return false|string PHPDoc function tip
     */
    private static function getFunctionTip(string $classWithNamespace, string $funcName) : string {
        $phpDoc = "";

        try {
            $class       = new ReflectionClass($classWithNamespace);
            $phpDocLines = explode("\n", $class->getMethod($funcName)->getDocComment());

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
        } catch (Throwable $ex) {
            return "";
        } finally {
            return $phpDoc;
        }
    }

    private static function getFullFunctionPath(Throwable $ex) : string {
        $traceContainer = empty($ex->getTrace()) ? debug_backtrace() : $ex->getTrace();

        foreach ($traceContainer as $trace) {
            if (isset($trace["class"])) {
                return $trace["class"];
            }
        }

        return $traceContainer[0]["function"];
    }

    public static function throwIfErrorCodeInvalid(int $code) : void {
        if ($code !== 999 && $code !== 0 && $code > self::getCountErrors()) {
            throw new InvalidErrorCodeError();
        }

        if (in_array($code, [
            ErrorManager::EC_INVALID_ARGUMENT,
        ])) {
            throw new InvalidErrorCodeError($code);
        }
    }

    public static function getErrorDescription(TavernException $ex, int $code) : string {
        if (!$ex->isCodeCorrect($code))
            self::throwIfErrorCodeInvalid($code);

        return self::ERRORS_DESCRIPTIONS[$code];
    }

    public static function getCountErrors() : int {
        return count(self::ERRORS_DESCRIPTIONS) - 1;
    }

    public static function throwExceptionHandlerHtml($ex) {
        http_response_code(500);

        $isInvalidErrorCode = $ex instanceof InvalidErrorCodeError;
        $isOutputBufferfing = $ex instanceof EmptyOutputBufferError;
        $isParseError       = $ex instanceof ParseError;
        $isTavernError      = $ex instanceof TavernException;

        if ($isInvalidErrorCode) {
            $stackTrace = $ex->getTrace();
            $fileTrace  = array_filter($stackTrace, function ($el) {
                if ($el['function'] == '__construct') {
                    return $el;
                }
            });
            $fileTrace  = reset($fileTrace);

            $fileErrorLine = $fileTrace['line'];
            $filePath      = $fileTrace['file'];
            $fileClass     = $fileTrace['class'];
            $fileContent   = file_get_contents($filePath);

            $content = TagAgent::getErrorPage();
            $content = TagAgent::compileSystemTags($content);
            $content = Engine::replaceFirst("{ERROR_PAGE_TITLE}", $isTavernError ? "Ошибка #{$ex->getErrorCode()}" : "Ошибка", $content);
            $content = Engine::replaceFirst("{SITE_NAME}", Engine::GetEngineInfo("sn"), $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:MESSAGE}", "[{$filePath}:{$fileErrorLine}]: {$ex->getMessage()}", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:LASTTEXT}", "Ошибка вывода исключения", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:CODE}",
                                            $fileContent,
                                            $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_NAME}", "throw new {$fileClass}", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_LINE}", $fileErrorLine, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:LINE}", $fileErrorLine, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:STACKTRACE}", rtrim($ex->getTraceAsString()), $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FULL_FILEPATH}", $filePath, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:RELATIVE_FILEPATH}", self::relativePath($filePath), $content);
        }
        elseif ($isOutputBufferfing) {
            $stackTrace         = $ex->getTrace();
            $functionLineNumber = $ex->getLine();
            $functionName       = $stackTrace[0]['function'];
            $lineNumber         = $ex->getLine();
            $filePath           = $ex->getFile();
            $relativeFilepath   = self::relativePath($filePath);
            $fileContent        = file_get_contents($ex->getFile());
            $functionDoc        = "";
            $thrownScript       = $ex->getFile();
            $thrownLineNumber   = $lineNumber;
            $exceptionName      = $ex->getName();

            $content = TagAgent::getErrorPage();
            $content = TagAgent::compileSystemTags($content);
            $content = Engine::replaceFirst("{ERROR_PAGE_TITLE}", $isTavernError ? "Ошибка #{$ex->getErrorCode()}" : "Ошибка", $content);
            $content = Engine::replaceFirst("{SITE_NAME}", Engine::GetEngineInfo("sn"), $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_TIP}", !$functionDoc ? "" : "<span class='fc-tip-hint'>$functionDoc</span>", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:MESSAGE}", "[$thrownScript:$thrownLineNumber]$exceptionName: {$ex->getMessage()}", $content);
            $content = Engine::replaceFirst(         "{ERROR_MANAGER:LASTTEXT}", $isTavernError
                ? ""
                : "Необработанная системная ошибка", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FULL_FILEPATH}", $filePath, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:RELATIVE_FILEPATH}", $relativeFilepath, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:CODE}",
                                            $fileContent,
                                            $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_NAME}", $functionName, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_LINE}", $functionLineNumber, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:STACKTRACE}", rtrim($ex->getTraceAsString()), $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:LINE}", $lineNumber, $content);
        }
        elseif ($isParseError) {
            $stackTrace         = $ex->getTrace();
            $functionLineNumber = $ex->getLine();
            $functionName       = $stackTrace[0]['function'];
            $lineNumber         = $ex->getLine();
            $filePath           = $ex->getFile();
            $relativeFilepath   = self::relativePath($filePath);
            $fileContent        = file_get_contents($ex->getFile());
            $functionDoc        = "";
            $thrownScript       = $ex->getFile();
            $thrownLineNumber   = $lineNumber;
            $exceptionName      = "ParseError";

            $content = TagAgent::getErrorPage();
            $content = TagAgent::compileSystemTags($content);
            $content = Engine::replaceFirst("{ERROR_PAGE_TITLE}", $isTavernError ? "Ошибка #{$ex->getErrorCode()}" : "Ошибка", $content);
            $content = Engine::replaceFirst("{SITE_NAME}", Engine::GetEngineInfo("sn"), $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_TIP}", !$functionDoc ? "" : "<span class='fc-tip-hint'>$functionDoc</span>", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:MESSAGE}", "[$thrownScript:$thrownLineNumber]$exceptionName: {$ex->getMessage()}", $content);
            $content = Engine::replaceFirst(         "{ERROR_MANAGER:LASTTEXT}", $isTavernError
                ? ""
                : "Необработанная системная ошибка", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FULL_FILEPATH}", $filePath, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:RELATIVE_FILEPATH}", $relativeFilepath, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:CODE}",
                                            $fileContent,
                                            $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_NAME}", $functionName, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_LINE}", $functionLineNumber, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:STACKTRACE}", rtrim($ex->getTraceAsString()), $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:LINE}", $lineNumber, $content);
        }
        elseif ($isTavernError) {
            $stackTrace = $ex->getTrace();

            if (!$ex->getFile()) {
                foreach ($stackTrace as $exceptionStep) {
                    if (isset($exceptionStep["file"])) {
                        $exceptionSourceFile = $exceptionStep["file"];
                        $lineNumber          = $exceptionStep["line"];
                        break;
                    }
                    else {
                        preg_match_all('/(\d+)/', $ex->getMessage(), $matches);
                        $lineNumber = $matches[0][1] ?? $matches[0][0];
                    }
                }
            }
            else {
                $exceptionSourceFile = $ex->getFile();
                if (str_contains($ex->getMessage(), 'on line') && !$isTavernError) {
                    preg_match_all("/(\d)+/", $ex->getMessage(), $exceptionLine);
                    $functionLineNumber = $stackTrace[0]['line'];
                    $lineNumber         = $exceptionLine[0][1] ?? $exceptionLine[0][0];
                }
                else {
                    $stackTrace         = debug_backtrace();
                    $functionLineNumber = $ex->getLine();
                    $lineNumber         = $ex->getLine();
                }
            }

            $exceptionName = explode('\\', get_class($ex))[2] ?? get_class($ex);
            $fileContent   = file_get_contents($exceptionSourceFile);
            $fileContent   = htmlspecialchars($fileContent, ENT_QUOTES | ENT_HTML5);
            if ($isInvalidErrorCode || $isTavernError) {
                $firstInTrace = $stackTrace[0];
            }
            else {
                $firstInTrace = $ex;
            }

            $functionPath     = self::getFullFunctionPath($ex);
            $functionName     = $firstInTrace["function"];
            $functionDoc      = self::getFunctionTip($functionPath, $functionName);
            $homeRootPath     = implode("/", array_slice(explode("/", $_SERVER["DOCUMENT_ROOT"]), 0, -1));
            $thrownLineNumber = $ex->getLine();
            $relativeFilepath = str_replace($homeRootPath, "", $exceptionSourceFile);
            $thrownScript     = str_replace($homeRootPath, "", $ex->getFile());

            $content = TagAgent::getErrorPage();
            $content = TagAgent::compileSystemTags($content);
            $content = Engine::replaceFirst("{ERROR_PAGE_TITLE}", $isTavernError ? "Ошибка #{$ex->getErrorCode()}" : "Ошибка", $content);
            $content = Engine::replaceFirst("{SITE_NAME}", Engine::GetEngineInfo("sn"), $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_TIP}", !$functionDoc ? "" : "<span class='fc-tip-hint'>$functionDoc</span>", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:MESSAGE}", "[$thrownScript:$thrownLineNumber]$exceptionName: {$ex->getMessage()}", $content);
            $content = Engine::replaceFirst(         "{ERROR_MANAGER:LASTTEXT}", $isTavernError
                ? ""
                : "Необработанная системная ошибка", $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FULL_FILEPATH}", $firstInTrace["file"], $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:RELATIVE_FILEPATH}", $relativeFilepath, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:CODE}",
                                            $fileContent,
                                            $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_NAME}", $functionName, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_LINE}", $functionLineNumber, $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:STACKTRACE}", rtrim($ex->getTraceAsString()), $content);
            $content = Engine::replaceFirst("{ERROR_MANAGER:LINE}", $lineNumber, $content);
        }
        else {
            self::throwErrorHandlerHtml($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());

            return;
        }

        echo $content;

        exit(0);
    }

    public static function throwErrorHandlerHtml(int $errorNo, string $errorMessage, string $errorFile, int $errorLine) {
        http_response_code(500);

        $ex = debug_backtrace();

        if (isset($ex[1]['args'][0]->xdebug_message)) {
            echo $ex[1]['args'][0]->xdebug_message;
        }

        $siteDomain     = ($_SERVER["HTTPS"] == 'on' ? 'https' : 'http') . "://" . $_SERVER["HTTP_HOST"];
        $rootPath       = array_slice(explode('/', $_SERVER["DOCUMENT_ROOT"]), 0, -1);
        $rootPath       = implode('/', $rootPath);
        $rootPathLength = strlen($rootPath);

        $stackTrace         = $ex[1] ?? $ex[0];
        $functionPath       = $stackTrace['class'];
        $functionName       = $stackTrace['function'];
        $functionDoc        = !is_null($functionPath) ? self::getFunctionTip($functionPath, $functionName) : null;
        $functionLineNumber = $stackTrace['line'];
        $thrownScript       = $errorFile;
        $thrownLineNumber   = $errorLine;
        $exceptionName      = "Error";
        $fullFilePath       = $errorFile ?? $stackTrace['file'];
        $relativeFilePath   = substr($fullFilePath, $rootPathLength + 1);
        $fileContent        = file_get_contents(HOME_ROOT . $relativeFilePath);
        $stackTraceAsString = '';

        foreach ($ex as $index => $stackTrace) {
            if ($index == 0) {
                continue;
            }

            $index -= 1;

            $stackTraceAsString .= "#$index {$stackTrace['file']}({$stackTrace['line']}): {$stackTrace['function']}()" . PHP_EOL;
        }

        $content = TagAgent::getErrorPage();
        $content = str_replace('{SITE_DOMAIN}', $siteDomain, $content);
        $content = Engine::replaceFirst("{ERROR_PAGE_TITLE}", "Внутренняя ошибка", $content);
        $content = Engine::replaceFirst("{SITE_NAME}", Engine::GetEngineInfo("sn"), $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_TIP}", !$functionDoc ? "" : $functionDoc, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:MESSAGE}", "[$thrownScript:$thrownLineNumber]$exceptionName: {$errorMessage}", $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:LASTTEXT}", "Необработанная системная ошибка", $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:FULL_FILEPATH}", $fullFilePath, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:RELATIVE_FILEPATH}", $relativeFilePath, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:CODE}", htmlentities($fileContent), $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_NAME}", $functionName, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:FUNCTION_LINE}", $functionLineNumber, $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:STACKTRACE}", rtrim($stackTraceAsString), $content);
        $content = Engine::replaceFirst("{ERROR_MANAGER:LINE}", $errorLine, $content);

        echo $content;

        exit(0);
    }

    public static function throwCompilationErrorHandlerHtml(TagCompilationError $ex) {
        function getLineNumberOf(array $codeLines, array $needles) : array {
            $positions = [];

            foreach ($needles as $needle) {
                foreach ($codeLines as $lineNum => $lineContent) {
                    if (str_contains($lineContent, $needle)) {
                        $positions[$needle][] = $lineNum + 1;
                    }
                }
            }

            return $positions;
        }

        http_response_code(500);

        $systemTagMatched  = [];
        $serviceTagMatched = [];
        $htmlTagMatched    = [];

        $htmlResult   = BuildManager::includeFromTemplate('errors/tags_error.html');
        $siteDomain   = ($_SERVER["HTTPS"] == 'on' ? 'https' : 'http') . "://" . $_SERVER["HTTP_HOST"];
        $content      = $ex->getUncompiledHtml();
        $contentLines = explode("\n", $ex->getUncompiledHtml());

        // Поиск неоткомпилированных тегов
        preg_match_all("/\{[0-9a-zA-Z_\-\:]+\}/", $content, $systemTagMatched);
        preg_match_all('/\{[a-zA-Z0-9_\-\>\*\@\!\.\%\?]+\|/', $content, $serviceTagMatched);
        preg_match_all(TagAgent::makeHtmlPattern(), $content, $htmlTagMatched);

        $systemTagMatched  = array_unique($systemTagMatched[0]);
        $serviceTagMatched = array_unique($serviceTagMatched[0]);
        $htmlTagMatched    = array_unique($htmlTagMatched[0]);

        if ($systemTagMatched) {
            $systemTags        = '';
            $systemTagsCounter = getLineNumberOf($contentLines, $systemTagMatched);
            foreach ($systemTagMatched as $systemTag) {
                if (TagAgent::isSystemTag($systemTag)) {
                    $systemTags .= "<li>$systemTag (" . implode(', ', $systemTagsCounter[$systemTag]) . ")</li>";
                }
            }
        }
        if (empty($systemTags)) {
            $systemTags = 'Все служебные теги откомпилированы!';
        }
        if ($serviceTagMatched) {
            $serviceTags        = '';
            $serviceTagsCounter = getLineNumberOf($contentLines, $serviceTagMatched);
            foreach ($serviceTagMatched as $serviceTag) {
                if (TagAgent::isServiceTag($serviceTag)) {
                    $serviceTags .= "<li>$serviceTag (" . implode(', ', $serviceTagsCounter[$serviceTag]) . ")</li>";
                }
            }
        }
        if (empty($serviceTags)) {
            $serviceTags = 'Все обрабатывающие теги откомпилированы!';
        }
        if ($htmlTagMatched) {
            $htmlTags        = '';
            $htmlTagsCounter = getLineNumberOf($contentLines, $htmlTagMatched);
            foreach ($htmlTagMatched as $htmlTag) {
                $htmlTagsLines = implode(', ', $htmlTagsCounter[$htmlTag]);
                $htmlTag       = htmlentities($htmlTag);
                $htmlTags      .= "<li>$htmlTag ($htmlTagsLines)</li>";
            }
        }
        if (empty($htmlTags)) {
            $htmlTags = "Все HTML-подобные теги были откомпилированы!";
        }


        $htmlResult = str_replace('{SITE_DOMAIN}', $siteDomain, $htmlResult);
        $htmlResult = BuildManager::replaceOnceInString('{SITE_NAME}', Engine::GetEngineInfo('sn'), $htmlResult);
        $htmlResult = BuildManager::replaceOnceInString('{ERROR_MANAGER:FUNCTION_TIP}', '', $htmlResult);
        $htmlResult = BuildManager::replaceOnceInString('{ERROR_MANAGER:CODE}', $content, $htmlResult);
        $htmlResult = BuildManager::replaceOnceInString('{SYSTEM_TAGS_LIST}', $systemTags, $htmlResult);
        $htmlResult = BuildManager::replaceOnceInString('{SERVICE_TAGS_LIST}', $serviceTags, $htmlResult);
        $htmlResult = BuildManager::replaceOnceInString('{HTML_TAGS_LIST}', $htmlTags, $htmlResult);

        echo $htmlResult;

        exit(0);
    }

    public static function throwFatalErrorHandlerHtml() {
        if (empty(error_get_last())) {
            return;
        }

        self::throwExceptionHandlerHtml(error_get_last());
        return;
    }
}