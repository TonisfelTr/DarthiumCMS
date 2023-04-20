<?php

namespace Builder\Controllers;

use Builder\Parser\HtmlDOM;
use Engine\Engine;
use Engine\ErrorManager;
use Exceptions\Exemplars\HashPageError;
use Exceptions\Exemplars\TagCompilationError;
use Exceptions\TavernException;
use Guards\Logger;
use Guards\SocietyGuard;
use Throwable;
use Users\Models\User;
use Users\UserAgent;
use PHP_Beautifier as Beautifier;

class BuildManager
{
    private static function throwIfContainsDoubleDot(string $path) {
        if (str_contains(substr($path, 4), "..")) {
            throw new TavernException("", ErrorManager::EC_FILE_NOT_EXIST);
        }
    }

    private static function formatCode(string $htmlCode, string $filePath) : string {
        $dom = new HtmlDOM();
        $dom->load($htmlCode);

        $result = $dom->save($filePath, '   ');
        $result = htmlspecialchars_decode($result);

        return $result;
    }

    private static function compilePhpCode(string $filePath, string &$htmlText, bool $withRemoving = false) : string {
        if (file_put_contents($filePath, $htmlText, FILE_USE_INCLUDE_PATH) === false) {
            throw new HashPageError("", ErrorManager::EC_PAGE_HASHING_ERROR);
        }
        self::formatCode($htmlText, $filePath);

        ob_start();
        require $filePath;
        $htmlText = ob_get_contents();
        ob_end_clean();
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
        BuildManager::turnOffOutputBuffering();
        $outputBufferingContent = '';
        $rawPath                = $path;
        $path                   = $_SERVER["DOCUMENT_ROOT"] . "/$root" . $path;

        try {
            ob_start(function ($outputResult) {
                return $outputResult = ob_get_contents();
            });
            BuildManager::fileExists($rawPath, $root, true);

            if (!$onlyOnce) {
                require $path;
            }
            else {
                require_once $path;
            }
            $outputBufferingContent = ob_get_clean();
            BuildManager::turnOffOutputBuffering();
        } catch (Throwable $ex) {
            if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
                throw new TavernException("File doesn't exist by path \"{path}\" (exists: {exist}, is directiory: {is_directory})",
                                          ErrorManager::EC_FILE_NOT_EXIST,
                                          [
                                              $path,
                                              file_exists($path) ? "true" : "false",
                                              is_dir($path) ? "true" : "false",
                                          ]);
            }
            if (ob_get_level() > 0) {
                $outputBufferingContent = ob_get_clean();
                BuildManager::turnOffOutputBuffering();
            }
            ErrorManager::throwExceptionHandlerHtml($ex);
        } finally {
            if (ob_get_level() > 0) {
                $outputBufferingContent = ob_get_clean();
                BuildManager::turnOffOutputBuffering();
            }
        }

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

        return self::createHashAndDrop($included);
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

    public static function createHashAndDrop(string $htmlText = null) {
        $hash       = Engine::RandomGen(32);
        $hashedPage = HOME_ROOT . "uploads/hashed/$hash.php";

        if (file_exists(HOME_ROOT . '/uploads/hashed')) {
            foreach (glob(HOME_ROOT . '/uploads/hashed/*') as $file) {
                unlink($file);
            }
        }

        $result = TagAgent::compileSystemTags($htmlText);
        self::compilePhpCode($hashedPage, $result, true);

        $includeTag = TagAgent::getHtmlTag("include");
        $result     = $includeTag->execute($result);
        $ifTag      = TagAgent::getHtmlTag('if');
        $result     = $ifTag->execute($result);
        $foreachTag = TagAgent::getHtmlTag('foreach');
        $result     = $foreachTag->execute($result);
        $withTag    = TagAgent::getHtmlTag('with');
        $result     = $withTag->execute($result);
        $result     = TagAgent::compileServiceTags($result);
        self::compilePhpCode($hashedPage, $result);

        $result = TagAgent::compileHtmlTags($result);
        self::compilePhpCode($hashedPage, $result);

        $result = TagAgent::compileServiceTags($result);
        self::compilePhpCode($hashedPage, $result);

        while (str_contains($result, '<' . '?' . 'php') || str_contains($result, '<' . '?' . '=')) {
            self::compilePhpCode($hashedPage, $formatedHtml, false);
            $result = $formatedHtml;
        }

        if (preg_match_all('/\{[a-zA-Z0-9_\-\!\@\$\%\&\*\?\>\+]+\|/', $result) ||
            preg_match_all("/\{[0-9a-zA-Z_\-\:]+\}/", $result)) {
            throw new TagCompilationError($result, ErrorManager::EC_TAG_COMPILATION_DOES_NOT_ENDED);
        }

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