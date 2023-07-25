<?php

namespace Builder\Controllers;

use Builder\Parser\HtmlDOM;
use Engine\Engine;
use Engine\ErrorManager;
use Exceptions\Exemplars\DisabledOutputBufferingError;
use Exceptions\Exemplars\EmptyOutputBufferError;
use Exceptions\Exemplars\HashPageError;
use Exceptions\Exemplars\TagCompilationError;
use Exceptions\TavernException;
use Guards\Logger;
use Guards\SocietyGuard;
use Throwable;
use Users\Models\User;
use Users\UserAgent;

class BuildManager
{
    private static function throwIfContainsDoubleDot(string $path) {
        if (str_contains(substr($path, 4), "..")) {
            throw new TavernException("", ErrorManager::EC_FILE_NOT_EXIST);
        }
    }

    private static function convertToValid(string $htmlText) : string {
        $argumentsPattern = '((\s*([a-zA-Z\-]+)\s*=\s*"(.*?)"\s*)*)';

        $htmlText = preg_replace('/(\n*)(<\?php(.*?)?>)/', "$2\r$1    ", $htmlText);

        $htmlText = preg_replace('/(<\/\w+>)/', "$1\r", $htmlText);
        $htmlText = preg_replace("/<!DOCTYPE(.*?)>/", "<!DOCTYPE$1>\r", $htmlText);
        $htmlText = preg_replace("/\s*<html$argumentsPattern>/", "\r<html$1>\r", $htmlText);
        $htmlText = preg_replace("/<head$argumentsPattern>/", "<head$1>\r", $htmlText);
        $htmlText = preg_replace("/<meta$argumentsPattern>/", "<meta$1>\r", $htmlText);
        $htmlText = preg_replace("/<link$argumentsPattern>/", "<link$1>\r", $htmlText);
        $htmlText = preg_replace("/<body$argumentsPattern>/", "<body$1>\r", $htmlText);
        $htmlText = preg_replace("/<div$argumentsPattern>/", "<div$1>\r", $htmlText);
        $htmlText = preg_replace("/<nav$argumentsPattern>/", "<nav$1>\r", $htmlText);
        $htmlText = preg_replace("/<button$argumentsPattern>/", "<button$1>\r", $htmlText);
        $htmlText = preg_replace("/<ul$argumentsPattern>/", "<ul$1>\r", $htmlText);
        $htmlText = preg_replace("/<ol$argumentsPattern>/", "<ol$1>\r", $htmlText);
        $htmlText = preg_replace("/<li$argumentsPattern>/", "<li$1>\r", $htmlText);
        $htmlText = preg_replace("/<li$argumentsPattern>\s*<a$argumentsPattern>(.*?)<\/a>\s*<\/li>/", '<li$1><a$5>$6</a></li>' . "\r", $htmlText);
        $htmlText = preg_replace("/<select$argumentsPattern>/", "<select$1>\r", $htmlText);
        $htmlText = preg_replace("/<option$argumentsPattern>(.*?)<\/option>/", "<option$1>$5</option>\r", $htmlText);

        $htmlText = str_replace("</ul>", "</ul>\r", $htmlText);

        $htmlText = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $htmlText);

        return $htmlText;
    }

    private static function compilePhpCode(string $filePath, string &$htmlText, bool $withRemoving = false) : string {
        //self::convertToValid($htmlText)
        if (file_put_contents($filePath, $htmlText, FILE_USE_INCLUDE_PATH) === false) {
            throw new HashPageError("", ErrorManager::EC_PAGE_HASHING_ERROR);
        }

        if (!ob_start()) {
            throw new EmptyOutputBufferError("", ErrorManager::EC_FAILED_START_OUTPUT_BUFFERING);
        }
        else {
            require $filePath;
            $htmlText = ob_get_contents();
            if (!$htmlText) {
                throw new EmptyOutputBufferError("", ErrorManager::EC_EMPTY_OUTPUT_BUFFER);
            }
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        if ($withRemoving) {
            unlink($filePath);
        }

        return $htmlText;
    }

    public static function replaceOnceInString($search, $replace, $text) : string {
        $pos = strpos($text, $search);
        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }

    public static function dropAndDie($var) : string {
        while (ob_get_level()) {
            ob_end_clean();
        }

        if (is_string($var)) {
            echo '<code>';
            echo htmlentities($var);
            echo '</code>';
        }
        else {
            var_dump($var);
        }

        exit(1);
    }

    public static function fileExists(string $path, string $root = HOME_ROOT, bool $strict = false) : bool {
        $path = $root . $path;

        $fileExists = file_exists($path);
        if (!$strict) {
            return $fileExists;
        }
        elseif (!$fileExists) {
            throw new TavernException("File doesn't exist by path \"{path}\" (exists: {exist}, is directiory: {is_directory})",
                                      ErrorManager::EC_FILE_NOT_EXIST,
                                      [
                                          $path,
                                          file_exists($path) ? "true" : "false",
                                          is_dir($path) ? "true" : "false",
                                      ]);
        }

        return false;
    }

    public static function fileRemove(string $path, string $root = HOME_ROOT) : bool {
        self::fileExists($path, $root, true);

        return unlink($root . $path);
    }

    public static function include(string $path, bool $onlyOnce = false, string $root = HOME_ROOT) {
        self::throwIfContainsDoubleDot($path);

        $outputBufferingConxtent = '';
        $rawPath                 = $path;
        $path                    = $_SERVER["DOCUMENT_ROOT"] . "/$root" . $path;

        BuildManager::fileExists($rawPath, $root, true);

        ob_start();
        if ($onlyOnce === false) {
            require $path;
        }
        else {
            require_once $path;
        }
        $outputBufferingContent = ob_get_contents();
        ob_end_clean();

        return $outputBufferingContent;
    }

    public static function includeWithCompile(string $path, bool $onlyOnce = false, string $root = HOME_ROOT) {
        $included = BuildManager::include($path, $onlyOnce, $root);

        return BuildManager::createHashAndDrop($included);
    }

    public static function includeContent(string $path, string $root = HOME_ROOT) {
        self::throwIfContainsDoubleDot($path);

        $path = $root . $path;

        $result = file_get_contents($path);

        if ($result === false) {
            throw new TavernException("File doesn't exist by path \"{path}\" (exists: {exist}, is directiory: {is_directory})", ErrorManager::EC_FILE_NOT_EXIST, [
                $path,
                file_exists($path) ? "true" : "false",
                is_dir($path) ? "true" : "false",
            ]);
        }
        else {
            return $result;
        }
    }

    public static function includeFromTemplate(string $path, bool $onlyOnce = false) {
        return self::include($path, $onlyOnce, TEMPLATE_ROOT) . PHP_EOL;
    }

    public static function includeFromTemplateAndCompile(string $path, bool $onlyOnce = false) {
        $included = self::include($path, $onlyOnce, TEMPLATE_ROOT) . PHP_EOL;

        return self::createHashAndDrop($included, true, basename($path));
    }

    public static function includeContentFromTemplate(string $path) {
        return self::includeContent($path, TEMPLATE_ROOT);
    }

    public static function includeFromAdminpanelTemplate(string $path, bool $onlyOnce = false) {
        return self::include($path, $onlyOnce, ADMINPANEL_TEMPLATE_ROOT) . PHP_EOL;
    }

    public static function includeContentFromAdminpanelTemplate(string $path) {
        return self::includeContent($path, ADMINPANEL_TEMPLATE_ROOT);
    }

    public static function includeFromPlugin(string $plugin, string $path, bool $onlyOnce = false) {
        return self::include("$plugin/$path", $onlyOnce, ADDONS_ROOT);
    }

    public static function includeContentFromPlugin(string $plugin, string $path) {
        return self::includeContent("$plugin/$path", ADDONS_ROOT);
    }

    public static function getFileExtension(string $path, string $root = HOME_ROOT) : string {
        return pathinfo($root . $path, PATHINFO_EXTENSION);
    }

    public static function showBannedPage(User $user) {
        if ($user == false) {
            return "";
        }

        if ($user->isBanned() || SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)) {
            echo BuildManager::include("banned.php");
            exit;
        }

        return "";
    }

    public static function showOfflinePage() {
        $isAuthorizated = UserAgent::isAuthorized();

        if ($isAuthorizated) {
            if (isset($_GET["offline_visit"]) && UserAgent::getCurrentUser()->getUserGroup()->getPermission("offline_visitor")) {
                UserAgent::getCurrentSession()->setContent(["offline_visitor" => true]);
            }
        }

        if (!$isAuthorizated || !UserAgent::getCurrentSession()->getContent("offline_visitor")) {
            Logger::addAccessLog("I tried visit the site but it's offline");
            Logger::addVisitLog("I tried visit the site but it was offline...");
            echo BuildManager::include("offline.php");
            exit;
        }
    }

    public static function createHashAndDrop(string $htmlText = null, bool $isIncluded = false, string $suffix = '') {
        if (!TagAgent::isTagsRegistrationCompleted()) {
            TagAgent::registerSystemTags();
        }
        if (!TagAgent::isServiceTagsRegistrationCompleted()) {
            TagAgent::registerServiceTags();
        }
        if (!TagAgent::isHTMLTagsRegistrationCompleted()) {
            TagAgent::registerHtmlTags();
        }

        $hash = Engine::RandomGen(32);

        $result = TagAgent::compileHtmlTags($htmlText);
        $result = TagAgent::compileSystemTags($result);
        $result = TagAgent::compileServiceTags($result);

        if ($isIncluded) {
            $result = self::hardCompile($result);
        }
        //Есть ли сервисные теги
        $firstChecking = preg_match_all('/\{[a-zA-Z0-9_\-\!\@\$\%\&\*\?\>\+]+\|/', $result, $firstMatch);
        //Есть ли служебные теги
        $secondChecking = preg_match_all("/\{[0-9a-zA-Z_\-\:]+\}/", $result, $secondMatch);
        //Проверяем на пустоту
        if ($firstChecking || $secondChecking) {
            if (TagAgent::isCompileCorrected($result)) {
                throw new TagCompilationError($result, ErrorManager::EC_TAG_COMPILATION_DOES_NOT_ENDED);
            }
        }

        $result = self::compilePhpCode(HOME_ROOT . "site/hashed/html_{$hash}.php", $result);

        if ($suffix == 'main.html') {
            if (self::fileExists("site/hashed/main_html.php")) {
                self::fileRemove("site/hashed/main_html.php");
            }
            file_put_contents(HOME_ROOT . "site/hashed/main_html.php", $result);
        }

        return $result;
    }

    public static function hardCompile(string $htmlText = null) : string {
        $result = TagAgent::compileHtmlTags($htmlText);
        $result = TagAgent::compileServiceTags($result);
        $result = TagAgent::compileSystemTags($result);

        return $result;
    }

    public static function turnOffOutputBuffering() {
        $i = 0;
        while (ob_get_level()) {
            $i++;
            ob_end_clean();
        }

        return $i;
    }
}