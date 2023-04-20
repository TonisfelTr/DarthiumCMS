<?php

namespace Builder\Controllers;

use Builder\Services\Tag;
use Builder\Services\HtmlTag;
use Builder\Services\ServiceTag;
use Engine\Engine;
use Engine\ErrorManager;
use Engine\LanguageManager;
use Engine\PluginManager;
use Engine\RouteAgent;
use Exceptions\Exemplars\InvalidRouteUrlChainLinkError;
use Exceptions\Exemplars\TagCompilationError;
use Exceptions\Exemplars\TagError;
use Users\UserAgent;

class TagAgent
{
    private const TC_DEFAULT           = 1;
    private const TC_HTML              = 2;
    private const TC_SERVICE           = 4;
    private const TC_TEMPORARY_DEFAULT = 8;

    protected static array $replacingCount = ["html" => [], "service" => [], "system" => []];

    private static array $tagsContainer                 = [];
    private static array $temporaryTagsContainer        = [];
    private static array $htmlTagsContainer             = [];
    private static array $serviceTagsContainer          = [];
    private static bool  $registeredServiceTag          = false;
    private static bool  $registeredHtmlTag             = false;
    private static bool  $registeredSystemTag           = false;
    private static bool  $enabledTemporaryTagsContainer = false;

    private static function doesTagExist(string $name, int $containerList = self::TC_DEFAULT) : bool {
        switch ($containerList) {
            case self::TC_DEFAULT:
                return isset(self::$tagsContainer[$name]);
            case self::TC_HTML:
                return isset(self::$htmlTagsContainer[$name]);
            case self::TC_SERVICE:
                return isset(self::$serviceTagsContainer[$name]);
            case self::TC_TEMPORARY_DEFAULT:
                return isset(self::$temporaryTagsContainer[$name]);
            default:
                throw new TagError("No container with that identification", ErrorManager::EC_INVALID_ARGUMENT);
        }
    }

    public static function temporaryContainerFilled() : bool {
        return !empty(self::$temporaryTagsContainer);
    }

    public static function useTemporaryContainer(callable $fn) : string {
        self::$enabledTemporaryTagsContainer = true;
        $html                                = $fn();
        self::$enabledTemporaryTagsContainer = false;
        self::$temporaryTagsContainer        = [];

        return $html;
    }

    public static function compileFromTemporaryContainer(string $html) : string {
        if (self::$enabledTemporaryTagsContainer) {
            /** @var Tag $tag Current tag from temporary container */
            foreach (self::$temporaryTagsContainer as $tag) {
                $html = $tag->execute($html);
            }
        }

        return $html;
    }

    public static function getReplacedTags() : array {
        return self::$replacingCount;
    }

    public static function getErrorPage() {
        while (ob_get_level()) {
            ob_end_clean();
        }

        return BuildManager::includeFromTemplate("errors/main.html");
    }

    public static function addToTagsContainer(Tag $tag, bool $strict = true) {
        if (self::$enabledTemporaryTagsContainer) {
            if (self::doesTagExist($tag->getName(), self::TC_TEMPORARY_DEFAULT)) {
                if ($strict) {
                    throw new TagError("", ErrorManager::EC_TAG_REGISTERED_ALREADY, [$tag->getName()]);
                }
                else {
                    return;
                }
            }

            self::$temporaryTagsContainer[$tag->getName()] = $tag;
        }
        else {
            if (self::doesTagExist($tag->getName())) {
                if ($strict) {
                    throw new TagError("", ErrorManager::EC_TAG_REGISTERED_ALREADY, [$tag->getName()]);
                }
                else {
                    return;
                }
            }

            self::$tagsContainer[$tag->getName()] = $tag;
        }
    }

    public static function addToHtmlTagsContainer(HtmlTag $tag) {
        if (self::doesTagExist($tag->getName(), self::TC_HTML)) {
            throw new TagError("", ErrorManager::EC_TAG_REGISTERED_ALREADY);
        }

        self::$htmlTagsContainer[$tag->getName()] = $tag;
    }

    public static function addToServiceTagsContainer(ServiceTag $tag) {
        if (self::doesTagExist($tag->getName(), self::TC_SERVICE)) {
            throw new TagError("", ErrorManager::EC_TAG_REGISTERED_ALREADY);
        }

        self::$serviceTagsContainer[$tag->getName()] = $tag;
    }

    public static function registerServiceTags() {
        if (self::$registeredServiceTag) {
            throw new TagError("", ErrorManager::EC_SERVICE_TAG_REGISTRATION_DISABLED);
        }

        ServiceTag::create(">", function ($content, array $arguments = []) {
            $arguments = array_map(function ($match) {
                if (str_contains($match, '$')) {
                    return $match;
                }
                elseif (is_integer((string)$match)) {
                    return $match;
                }
                else {
                    return "'$match'";
                }
            }, $arguments);

            $arguments = '[' . implode(', ', $arguments) . ']';
            return '<' . '?' . '=' . ' \Engine\RouteAgent::buildRoute(\'' . $content . '\', ' . $arguments . ') ' . '?' . '>';
        })->setUnknownCountOfArguments();
        ServiceTag::create("route", function (string $content, array $arguments = []) {
            $arguments = array_map(function ($match) {
                if (str_contains($match, '$')) {
                    return $match;
                }
                elseif (is_integer((string)$match)) {
                    return $match;
                }
                else {
                    return "'$match'";
                }
            }, $arguments);

            $arguments = '[' . implode(', ', $arguments) . ']';
            return '<' . '?' . '=' . ' \Engine\RouteAgent::buildRoute(\'' . $content . '\', ' . $arguments . ') ' . '?' . '>';
        })->setUnknownCountOfArguments();
        ServiceTag::create("?>", function ($content, array $arguments = []) {
            return RouteAgent::buildRoute($content, $arguments);
        })->setUnknownCountOfArguments();
        ServiceTag::create(">*", function (string $content) {
            if (RouteAgent::getCurrentRoute()->linkExists($content)) {
                $chainLinkContent = RouteAgent::getCurrentRoute()->getValueOfChainLink($content);
            }
            else {
                $chainLinkContent = "false";
            }

            return $chainLinkContent;
        });

        ServiceTag::create("%", function (string $content) {
            ;
            $language = LanguageManager::GetTranslation($content);

            return $language;
        });
        ServiceTag::create("lang", function (string $content) {
            $language = LanguageManager::GetTranslation($content);

            return $language;
        });

        ServiceTag::create("...", function (string $content) {
            return '<' . '?=' . " count($content) " . '?' . '>';
        });
        ServiceTag::create("count", function (string $content) {
            return '<' . '?=' . " count($content) " . '?' . '>';
        });

        ServiceTag::create("@", function (string $content) {
            return '<' . '?' . "= $content " . '?' . '>';
        });
        ServiceTag::create("php", function (string $content) {
            return '<' . '?' . "= $content " . '?' . '>';
        });
        ServiceTag::create("!@", function (string $content) {
            if (preg_match_all('/continue/', $content)) {
                return '';
            }

            return '<' . "?php $content; ?>";
        });
        ServiceTag::create("!php", function (string $content) {
            if (preg_match_all('/continue/', $content)) {
                return '';
            }

            return '<' . "?php $content; ?>";
        });
        ServiceTag::create("?@sys", function (string $content) {
            return '<' . '?' . '=' . ' \Builder\Controllers\TagAgent::compileSystemTags(' . $content . ') ' . '?' . '>';
        });
        ServiceTag::create("?@html", function (string $content) {
            return '<' . "?= htmlentities($content) ?>";
        });

        ServiceTag::create("dad", function (string $content) {
            return '<' . '?' . 'php ' . "\Builder\Controllers\BuildManager::dropAndDie($content);" . ' ?' . '>';
        });

        ServiceTag::create("*", function (string $content) {
            return '<' . '?' . 'php ' . "define('TT_$content', true); " . '?' . '>';
        });
        ServiceTag::create("define", function (string $content) {
            return '<' . '?' . 'php ' . "define('TT_$content', true); " . '?' . '>';
        });

        ServiceTag::create("p", function (string $content) {
            if (UserAgent::getCurrentUser() === false) {
                return "false";
            }

            return UserAgent::getCurrentUser()->getUserGroup()->getPermission($content) ? "true" : "false";
        });
        ServiceTag::create("perm", function (string $content) {
            if (UserAgent::getCurrentUser() === false) {
                return "false";
            }

            return UserAgent::getCurrentUser()->getUserGroup()->getPermission($content) ? "true" : "false";
        });

        ServiceTag::create("u*", function (string $content) {
            return '<?' . '= ' . '\Users\UserAgent::getCurrentUser()->' . $content . '()' . ' ?' . '>';
        });
        ServiceTag::create("user*", function (string $content) {
            return '<?' . '= ' . '\Users\UserAgent::getCurrentUser()->' . $content . '()' . ' ?' . '>';
        });
        ServiceTag::create("!u*", function (string $content) {
            return '<?' . 'php ' . '$user = \Users\UserAgent::getCurrentUser()->' . $content . '();' . ' ?' . '>';
        });
        ServiceTag::create("!user*", function (string $content) {
            return '<?' . 'php ' . '$user = \Users\UserAgent::getCurrentUser()->' . $content . '();' . ' ?' . '>';
        });
        ServiceTag::create("!u*?", function (string $content) {
            return '\Users\UserAgent::getCurrentUser()->' . $content;
        });

        self::$registeredServiceTag = true;
    }

    public static function registerSystemTags() {
        if (self::$registeredSystemTag) {
            throw new TagError("", ErrorManager::EC_SYSTEM_TAG_REGISTRATION_DISABLED);
        }

        BuildManager::include("engine/customs/tags/tags.php", true);

        Tag::create("{SITE_DOMAIN}")->setProcessingFunction(function () {
            return Engine::GetEngineInfo("site.domain");
        });
        Tag::create("{ENGINE_META:SITE_NAME}")->setProcessingFunction(function () {
            return Engine::GetEngineInfo("sn");
        });
        Tag::create("{ENGINE_META:SITE_TAGLINE}")->setProcessingFunction(function () {
            return Engine::GetEngineInfo("stl");
        });
        Tag::create("{ENGINE_META:DESCRIPTION}")->setProcessingFunction(function () {
            return Engine::GetEngineInfo("ssc");
        });
        Tag::create("{PLUGINS_STYLESHEETS}")->setProcessingFunction(function () {
            return PluginManager::IntegrateCSS();
        });
        Tag::create("{PLUGIN_HEAD_JS}")->setProcessingFunction(function () {
            return PluginManager::IntegrateHeaderJS();
        });
        Tag::create("{PLUGIN_FOOTER_JS}")->setProcessingFunction(function () {
            return PluginManager::IntegrateFooterJS();
        });
        Tag::create("{REPORT_PAGE:JS}")->setProcessingFunction(function () {
            return BuildManager::includeContent("report/reportscript.js", TEMPLATE_ROOT);
        });
        Tag::create("{SPOILER_CONTROLLER:JS}")->setProcessingFunction(function () {
            return BuildManager::includeContent("site/scripts/SpoilerController.js");
        });
        Tag::create("{METRIC_JS}")->setProcessingFunction(function () {
            return Engine::GetEngineInfo("sms") == 0 ? '' : Engine::GetAnalyticScript();
        });

        self::$registeredSystemTag = true;
    }

    public static function registerHtmlTags() {
        if (self::$registeredHtmlTag) {
            throw new TagError("", ErrorManager::EC_SERVICE_TAG_REGISTRATION_DISABLED);
        }

        HtmlTag::create("include", ["path", "root", "once"], ["path"])
               ->setProcessingFunction(function (string $content, array $arguments) {
                   $root   = array_key_exists("root", $arguments) ? constant($arguments['root']) : HOME_ROOT;
                   $isOnce = array_key_exists("once", $arguments) ? $arguments["once"] : false;
                   $path   = $arguments["path"];

                   return BuildManager::includeWithCompile($path, $isOnce, $root);
               })
               ->setWithoutClosing();

        HtmlTag::create("foreach", ["array", "value", "key"], ["array", "value"])
               ->setProcessingFunction(function (string $content, array $arguments) {
                   if ((isset($arguments["key"]) && !str_starts_with($arguments["key"], '$')) || !str_starts_with($arguments["value"], '$')) {
                       throw new TagError("\"foreach\" HTML tag's arguments \"key\" or \"value\" must start with \$", ErrorManager::EC_INVALID_TAG_ARGUMENT_SYNTAX);
                   }

                   $haystack = $arguments["array"];
                   $asKey    = $arguments["key"] ?? false;
                   $asValue  = $arguments["value"];

                   $result = PHP_EOL . '<' . '?' . "php foreach ($haystack as ";
                   if ($asKey) {
                       $result .= "$asKey => ";
                   }
                   $result .= "$asValue) { " . '?' . '>' . PHP_EOL;

                   return $result;
               });

//        HtmlTag::create("case", ["value"], ["value"])
//            ->setProcessingFunction(function ( string $content, array $arguments) {
//                $argument = $arguments["value"];
//                return '<' . '?php ' . 'case "' . $argument . '": { ' . '?' . '>';
//            })
//            ->setResultClosingTagAction(function() {
//                return '<' . '?' . 'php' . ' break; ' . '?' . '>'.
//                       '<' . '?' . 'php ' . '} ?' . '>';
//            });
//        HtmlTag::create("switch", ["by"], ["by"])
//            ->setProcessingFunction(function ( string $content, array $arguments) {
//               return '<' . '?' . 'php switch (' . $arguments["by"] . ') {' . '?' . '>';
//            });

        HtmlTag::create("continue", ["depth"])
               ->setWithoutClosing()
               ->setProcessingFunction(function (string $content, array $arguments) {
                   return '<' . '?' . 'php continue' .
                          (!empty($arguments["depth"])
                              ? "({$arguments["depth"]})"
                              : '') .
                          '; ' . '?' . '>';
               });

        HtmlTag::create("with", ["var", "value", "condition"], ["var", "value"])
               ->setProcessingFunction(function (string $content, array $arguments) {
                   if (isset($arguments["condition"])) {
                       $result = PHP_EOL . '<' . '?' . 'php' . " if ({$arguments["condition"]}) { " . '?' . '>';
                       $result .= PHP_EOL . '<' . '?' . 'php ';
                       $result .= "{$arguments["var"]} = {$arguments["value"]};";
                       $result .= ' ?' . '>' . PHP_EOL;
                   }
                   else {
                       $result = PHP_EOL . '<' . '?' . 'php ';
                       $result .= "{$arguments["var"]} = {$arguments["value"]};";
                       $result .= ' ?' . '>' . PHP_EOL;
                   }

                   return $result;
               })->setResultClosingTagAction(function (string $content, array $arguments) {
                if (isset($arguments["condition"])) {
                    $result = '<' . '?' . 'php ' . $arguments["var"] . ' = false; ' . ' ?' . '>' . PHP_EOL
                              . '<' . '?' . 'php ' . '}' . ' ?' . '>' . PHP_EOL;
                }
                else {
                    $result = '<' . '?' . 'php ' . $arguments["var"] . ' = false; ' . ' ?' . '>';
                }

                return $result;
            });

        HtmlTag::create("if", ["condition"], ["condition"])
               ->setProcessingFunction(function (string $content, array $arguments) {
                   $condition = $arguments["condition"];

                   return PHP_EOL . '<' . '?' . 'php ' . "if($condition) " . '{ ' . '?' . '>' . PHP_EOL;
               });

        foreach (self::$htmlTagsContainer as $htmlTag) {
            self::$replacingCount["html"][$htmlTag->getName()] = 0;
        }

        self::$registeredHtmlTag = true;
    }

    public static function compileSystemTags(string $htmlText) : string {
        foreach (self::$tagsContainer as $tag) {
            /** @var $tag Tag Currect executing tag */
            $htmlText = $tag->execute($htmlText);
        }
        return $htmlText;
    }

    public static function compileServiceTags(string $htmlText) : string {
        /** @var ServiceTag $serviceTag */
        foreach (self::$serviceTagsContainer as $serviceTag) {
            $htmlText = $serviceTag->execute($htmlText);
        }

        return $htmlText;
    }

    public static function compileHtmlTags(string $htmlText) : string {
        /** @var HtmlTag $htmlTag */
        foreach (self::$htmlTagsContainer as $htmlTag) {
            $htmlText = $htmlTag->execute($htmlText);
        }

        return $htmlText;
    }

    public static function getHtmlTagsName() : string {
        $result = '';

        foreach (self::$htmlTagsContainer as $htmlTag) {
            $result .= $htmlTag->getName() . ',';
        }

        $result = rtrim($result, ',');
        return $result;
    }

    public static function getServiceTag(string $name) : ServiceTag {
        foreach (self::$serviceTagsContainer as $serviceTag) {
            if ($serviceTag->getName() == $name) {
                return $serviceTag;
            }
        }
    }

    public static function getHtmlTag(string $name) : HtmlTag {
        foreach (self::$htmlTagsContainer as $htmlTag) {
            if ($htmlTag->getName() == $name) {
                return $htmlTag;
            }
        }
    }
}