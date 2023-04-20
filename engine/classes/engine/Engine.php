<?php

namespace Engine;

define("HOME_ROOT", "./../");
define("CONFIG_ROOT", "./../engine/config/");
define("ADDONS_ROOT", "./../addons/");
define("ROUTES_ROOT", "./../engine/routes/");
define("LANGUAGE_ROOT", "./../languages/");

define("MAIN_MODULE", "main.php");
define("PROFILE_MODULE", "profile.php");
define("ADMINPANEL_MODULE", "adminpanel.php");

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Exceptions\Exemplars\InvalidParameterNameError;
use Exceptions\Exemplars\NotLoadedEngineConfigError;
use Exceptions\TavernException;
use Users\Services\FlashSession;
use Users\UserAgent;

class Engine
{
    static private $DBName;
    static private $DBPass;
    static private $DBHost;
    static private $DBLogin;
    static private $DBPort;
    static private $DBDriver;

    static private $DomainSite;
    static private $SiteName;
    static private $SiteTagline;
    static private $SiteStatus;
    static private $SiteSubscribe;
    static private $SiteHashtags;
    static private $SiteLang;
    static private $SiteTemplate;
    static private $SiteRegionTime;

    static private $EmailAcc;
    static private $EmailPass;
    static private $EmailHost;
    static private $EmailPort;
    static private $EmailCP;

    static private $NeedActivate  = false;
    static private $StandartGroup = 1;
    static private $MultiAccPermitted;

    static private $ChatFilterMechanism    = 0;
    static private $ChatFilterReplaceFor;
    static private $AvatarHeight           = 100;
    static private $AvatarWidth            = 100;
    static private $UploadPermittedSize    = 10 * 1024 * 1024;
    static private $UploadPermittedFormats = "gif,png,img,tif,zip,rar,txt,doc";
    static private $CanGuestsSeeProfiles   = false;
    static private $CanUsersReputationVoteManyTimes;

    static private $SiteMetricStatus;

    private static function includeDependencies(string $className) {
        /** @var string $path Path to directiory with classes */
        $path = HOME_ROOT . "engine/classes/";

        $breadcrumbs = explode("\\", $className);
        $moduleName  = lcfirst($breadcrumbs[0]);
        $classFile   = end($breadcrumbs);

        $requiredFolders = ["Controllers", "Models", "Services"];
        $foldersExist    = !in_array(false, array_map(function ($folderName) use ($path, $moduleName) {
            return file_exists("$path/$moduleName/$folderName");
        }, $requiredFolders));
        $inPathToSub     = in_array($breadcrumbs[1], $requiredFolders);

        // If we have Handler.php file in module root
        if ($foldersExist && $inPathToSub && !class_exists("\{$breadcrumbs[0]}\Handler")) {
            $path .= "$moduleName/Handler";
            include_once "$path.php";

            if (file_exists("$path.php")) {
                call_user_func("\\{$breadcrumbs[0]}\\Handler::includeDependencies", $className);
            }

            return;
        }

        // If we don't have Handler.php file in module root...
        foreach ($breadcrumbs as $index => $breadcrumb) {
            if ($index !== count($breadcrumbs) - 1) {
                $path .= lcfirst($breadcrumb) . "/";
            }
            else {
                $path .= "$breadcrumb/";
            }
        }
        $path = rtrim($path, "/");

        if (file_exists("$path.php")) {
            include_once "$path.php";
        }
        else {
            include_once HOME_ROOT . "vendor/autoload.php";
        }
    }

    public static function ConstructTemplatePath($loadingPage, $module = "", $ext = "html") {
        return HOME_ROOT . "/site/templates/" . Engine::$SiteTemplate . "/$module/$loadingPage.$ext";
    }

    public static function GetDBInfo($code) {
        switch ($code) {
            case 1:
                return self::$DBLogin;
            case 2:
                return self::$DBPass;
            case 0:
                return self::$DBHost;
            case 3:
                return self::$DBName;
            case 4:
                return self::$DBPort;
            case 5:
                return (self::$DBDriver == "1") ? "mysql" : "pgsql";
        }

        return false;

    }

    public static function DateFormatToRead(string $string) {
        $month = [
            '01' => LanguageManager::GetTranslation("january_month"),
            '02' => LanguageManager::GetTranslation("febrary_month"),
            '03' => LanguageManager::GetTranslation("march_month"),
            '04' => LanguageManager::GetTranslation("april_month"),
            '05' => LanguageManager::GetTranslation("may_month"),
            '06' => LanguageManager::GetTranslation("june_month"),
            '07' => LanguageManager::GetTranslation("july_month"),
            '08' => LanguageManager::GetTranslation("august_month"),
            '09' => LanguageManager::GetTranslation("september_month"),
            '10' => LanguageManager::GetTranslation("october_month"),
            '11' => LanguageManager::GetTranslation("november_month"),
            '12' => LanguageManager::GetTranslation("december_month"),
        ];

        $exploded = explode("-", $string);
        $result   = $exploded[2] . " " . $month[$exploded[1]] . " " . $exploded[0] . " " . LanguageManager::GetTranslation("year");
        return $result;
    }

    public static function DatetimeFormatToRead($string) {
        //Format: Y-m-d H:i:s
        $month = [
            '01' => LanguageManager::GetTranslation("january_month"),
            '02' => LanguageManager::GetTranslation("febrary_month"),
            '03' => LanguageManager::GetTranslation("march_month"),
            '04' => LanguageManager::GetTranslation("april_month"),
            '05' => LanguageManager::GetTranslation("may_month"),
            '06' => LanguageManager::GetTranslation("june_month"),
            '07' => LanguageManager::GetTranslation("july_month"),
            '08' => LanguageManager::GetTranslation("august_month"),
            '09' => LanguageManager::GetTranslation("september_month"),
            '10' => LanguageManager::GetTranslation("october_month"),
            '11' => LanguageManager::GetTranslation("november_month"),
            '12' => LanguageManager::GetTranslation("december_month"),
        ];

        if ($string == "1970-01-01 00:00:00") {
            return LanguageManager::GetTranslation("never");
        }

        $parts  = explode(" ", $string);
        $date   = explode("-", $parts[0]);
        $result = $parts[1] . " " . $date[2] . " " . $month[$date[1]] . " " . $date[0] . " " . LanguageManager::GetTranslation("year");
        return $result;
    }

    public static function BooleanToWords($bool) {
        return ($bool) ? LanguageManager::GetTranslation("yes") : LanguageManager::GetTranslation("no");
    }

    public static function LoadEngine() {
        set_include_path($_SERVER["DOCUMENT_ROOT"]);
        spl_autoload_register("self::includeDependencies", true, true);

        $file = file_get_contents(CONFIG_ROOT . "config.sfc", FILE_USE_INCLUDE_PATH);
        $a    = unserialize($file);

        $engConf = json_decode(file_get_contents(CONFIG_ROOT . "dbconf.sfc", FILE_USE_INCLUDE_PATH), true);

        self::$EmailAcc  = $a["emailAcc"];
        self::$EmailPass = $a["emailPass"];
        self::$EmailHost = $a["emailHost"];
        self::$EmailPort = $a["emailPort"];
        self::$EmailCP   = $a["emailCP"];

        self::$DBName   = $engConf["dbName"];
        self::$DBPass   = $engConf["dbPass"];
        self::$DBHost   = $engConf["dbHost"];
        self::$DBLogin  = $engConf["dbLogin"];
        self::$DBPort   = $engConf["dbPort"];
        self::$DBDriver = $engConf["dbDriver"];

        self::$DomainSite     = $a["domainSite"];
        self::$SiteName       = $a["siteName"];
        self::$SiteTagline    = $a["siteTagline"];
        self::$SiteStatus     = $a["siteStatus"];
        self::$SiteSubscribe  = $a["siteSubscribe"];
        self::$SiteHashtags   = $a["siteHashtags"];
        self::$SiteLang       = $a["siteLang"];
        self::$SiteTemplate   = $a["siteTemplate"];
        self::$SiteRegionTime = $a["siteRegionTime"];

        self::$NeedActivate      = $a["needActivate"];
        self::$MultiAccPermitted = $a["multiAccount"];
        self::$StandartGroup     = $a["standartGroup"];

        self::$ChatFilterMechanism             = $a["chatFilterMechanism"] ?? 0;
        self::$ChatFilterReplaceFor            = $a["chatFilterReplaceFor"] ?? "";
        self::$AvatarHeight                    = $a["avatarHeight"];
        self::$AvatarWidth                     = $a["avatarWidth"];
        self::$UploadPermittedSize             = $a["uploadPermSize"];
        self::$UploadPermittedFormats          = $a["uploadPermFormats"];
        self::$CanGuestsSeeProfiles            = $a["guestsseeprofiles"];
        self::$CanUsersReputationVoteManyTimes = $a["multivoterep"];

        self::$SiteMetricStatus = $a["metricStatus"];

        define("TEMPLATE_ROOT", HOME_ROOT . "site/templates/" . Engine::GetEngineInfo("stp") . "/");
        define("ADMINPANEL_TEMPLATE_ROOT", HOME_ROOT . "adminpanel/templates/" . Engine::GetEngineInfo("stp") . "/");

        LanguageManager::load();

        error_reporting(E_ERROR | E_COMPILE_ERROR);

        @$htaccessGlobal = file_get_contents(".htaccess", FILE_USE_INCLUDE_PATH);
        @$htaccessGlobal = preg_replace("/php_value upload_max_filesize [0-9A-Za-z]+/", "php_value upload_max_filesize " . self::GetEngineInfo("ups"), $htaccessGlobal);
        file_put_contents($_SERVER["DOCUMENT_ROOT"] . ".htaccess", $htaccessGlobal);

        set_exception_handler([\Engine\ErrorManager::class, "throwExceptionHandlerHtml"]);
        set_error_handler([\Engine\ErrorManager::class, "throwErrorHandlerHtml"]);
        register_shutdown_function("\Engine\ErrorManager::throwFatalErrorHandlerHtml");

        ini_set('memory_limit', 204217728);

        MigrationAgent::run();
        FlashSession::SetUpSource();
        RouteAgent::registerRoutes();

        if (self::$SiteStatus != 1) {
            BuildManager::showOfflinePage();
        }

        TagAgent::registerHtmlTags();
        TagAgent::registerServiceTags();
        TagAgent::registerSystemTags();

        UserAgent::SessionContinue();
    }

    public static function SettingsSave($DomainSite, $siteName, $siteTagline, $siteStatus, $siteSubscribe, $siteHashtags, $siteLang, $siteTemplate, $siteRegionTime,
                                        $emailAcc, $emailPass, $emailHost, $emailPort, $emailCP,
                                        $needActivate, $multiAccPermitted, $standartGroup,
                                        $chatFilterMechanism, $chatFilterReplaceFor, $avatarHeight, $avatarWidth, $uploadPermittedSize, $uploadPermittedFormats, $canGuestsSeeProfiles,
                                        $canMultiRepVote,
                                        $siteMetricStatus) {
        $settingsArray = [
            'domainSite'           => $DomainSite,
            'siteName'             => $siteName,
            'siteTagline'          => $siteTagline,
            'siteStatus'           => $siteStatus,
            'siteSubscribe'        => $siteSubscribe,
            'siteHashtags'         => $siteHashtags,
            'siteLang'             => $siteLang,
            'siteTemplate'         => $siteTemplate,
            'siteRegionTime'       => $siteRegionTime,
            'emailAcc'             => $emailAcc,
            'emailPass'            => $emailPass,
            'emailHost'            => $emailHost,
            'emailPort'            => $emailPort,
            'emailCP'              => $emailCP,
            'needActivate'         => $needActivate,
            'multiAccount'         => $multiAccPermitted,
            'standartGroup'        => $standartGroup,
            'chatFilterMechanism'  => $chatFilterMechanism,
            'chatFilterReplaceFor' => $chatFilterReplaceFor,
            'avatarHeight'         => $avatarHeight,
            'avatarWidth'          => $avatarWidth,
            'uploadPermSize'       => $uploadPermittedSize,
            'uploadPermFormats'    => $uploadPermittedFormats,
            'guestsseeprofiles'    => $canGuestsSeeProfiles,
            "multivoterep"         => $canMultiRepVote,
            'metricStatus'         => $siteMetricStatus,
        ];
        if (file_put_contents(CONFIG_ROOT . "config.sfc", serialize($settingsArray))) return true;
        else {
            throw new NotLoadedEngineConfigError("Cannot load config file.");
        }
    }

    public static function GetEngineInfo($code) {
        switch ($code) {
            case "na":
                return self::$NeedActivate;
            case "map":
                return self::$MultiAccPermitted;
            case "sg":
                return self::$StandartGroup;
            case "sn":
                return self::$SiteName;
            case "stl":
                return self::$SiteTagline;
            case "sh":
                return self::$SiteHashtags;
            case "sl":
                return self::$SiteLang;
            case "srt":
                return self::$SiteRegionTime;
            case "dm":
            case "domain":
            case "site.domain":
                return self::$DomainSite;
            case "ss":
                return self::$SiteStatus;
            case "ssc":
                return self::$SiteSubscribe;
            case "el":
                return self::$EmailAcc;
            case "ep":
                return self::$EmailPass;
            case "eh":
                return self::$EmailHost;
            case "ept":
                return self::$EmailPort;
            case "ecp":
                return self::$EmailCP;
            case "aw":
                return self::$AvatarWidth;
            case "ah":
                return self::$AvatarHeight;
            case "ups":
                return self::$UploadPermittedSize;
            case "upf":
                return self::$UploadPermittedFormats;
            case "gsp":
                return self::$CanGuestsSeeProfiles;
            case "vmr":
                return self::$CanUsersReputationVoteManyTimes;
            case "stp":
                return self::$SiteTemplate;
            case "sms":
                return self::$SiteMetricStatus;
            case "cfm":
                return self::$ChatFilterMechanism;
            case "cfrf":
                return self::$ChatFilterReplaceFor;
        }

        throw new InvalidParameterNameError("", ErrorManager::EC_INVALID_ENGINE_PARAMETER_NAME, [$code]);
    }

    public static function RandomGen($lenght = 8) {

        $letters = [
            0  => 'a', 30 => 'A',
            1  => 'b', 31 => 'B',
            2  => 'c', 32 => 'C',
            3  => 'd', 33 => 'D',
            4  => 'e', 34 => 'E',
            5  => 'f', 35 => 'F',
            6  => 'g', 36 => 'G',
            7  => 'h', 37 => 'H',
            8  => 'j', 38 => 'J',
            9  => 'k', 39 => 'K',
            10 => 'l', 40 => 'L',
            11 => 'm', 41 => 'M',
            12 => 'n', 42 => 'N',
            13 => 'o', 43 => 'O',
            14 => 'p', 44 => 'P',
            15 => 'q', 45 => 'Q',
            16 => 'r', 46 => 'R',
            17 => 's', 47 => 'S',
            18 => 't', 48 => 'T',
            19 => 'u', 49 => 'U',
            20 => 'v', 50 => 'V',
            21 => 'w', 51 => 'W',
            22 => 'x', 52 => 'X',
            23 => 'y', 53 => 'Y',
            24 => 'z', 54 => 'Z',
            25 => '1', 55 => '2',
            26 => '3', 56 => '4',
            27 => '5', 57 => '6',
            28 => '7', 58 => '8',
            29 => '9', 59 => '0',
        ];

        $result = "";
        for ($i = 0 ; $i < $lenght ; $i++) {

            $result .= $letters[rand(0, 59)];

        }

        return $result;

    }

    public static function CompileBBCode($stext) {
        $text = $stext;

        $text = strip_tags($text);
        $text = nl2br($text);
        $text = str_ireplace("[ol]", "<ol type=\"1\">", $text);
        $text = str_ireplace("[/ol]", "</ol>", $text);
        $text = str_ireplace("[/size]", "</p>", $text);
        $text = str_ireplace("[/color]", "</span>", $text);
        $text = str_ireplace("[b]", "<strong>", $text);
        $text = str_ireplace("[/b]", "</strong>", $text);
        $text = str_ireplace("[i]", "<em>", $text);
        $text = str_ireplace("[/i]", "</em>", $text);
        $text = str_ireplace("[u]", "<ins>", $text);
        $text = str_ireplace("[/u]", "</ins>", $text);
        $text = str_ireplace("[s]", "<s>", $text);
        $text = str_ireplace("[/s]", "</s>", $text);
        $text = str_ireplace("[hr]", "<hr style=\"box-shadow: 1px 1px #3f3f3f;\">", $text);
        $text = str_ireplace("[quote]", "<p class=\"message-quote-author-sign\">" . LanguageManager::GetTranslation("quote_anonim_said") .
                                        ":</p><div class=\"message-quote-block\"><span style=\"font-size: 50px; display: inline-block;\">“</span>", $text);
        $text = str_ireplace("[/quote]", "</div>", $text);
        $text = str_ireplace("[/align]", "</p>", $text);
        $text =
            str_ireplace("[spoiler]", "<div class=\"col-12 div-spoiler\"><span class=\"glyphicons glyphicons-alert\"></span> " . LanguageManager::GetTranslation("spoiler") . "<div hidden>", $text);
        $text = str_ireplace("[/spoiler]", "</div></div>", $text);
        $text = preg_replace("/\[size\=(\d+)\]/", "<p style=\"font-size: $1px;\">", $text);
        $text =
            preg_replace("/\[youtube\=https:\/\/youtu\.be\/(.+)\]/", "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>", $text);
        while (preg_match("/\[img\=(.+?)\]/", $text, $results) != false) {
            $sizes = getimagesize($results[1]);
            $text  =
                preg_replace("/\[img\=(.+?)\]/", "<div class=\"img-container\"><a href=\"$1\" data-lightbox=\"image\"><img class=\"img-for-frame\" src=\"$1\"></a><p>$sizes[0]x$sizes[1]</p></div>", $text, 1);
        }
        while (preg_match("/\[\!img\=(.?)+\]/", $text) != false) {
            $text = preg_replace("/\[\!img\=(.+?)\]/", "<img class=\"img-unframed\" src=\"$1\" style=\"max-width: 90%; max-height: 90%; display: block; margin: -10px auto;\">", $text, 1);
        }
        $text = preg_replace("/\[align\=(.+?)\]/", "<p style=\"text-align: $1;\">", $text);
        $text = preg_replace("/\[color\=(.+?)\]/", "<span style=\"color: $1;\">", $text);
        $text = preg_replace("/\[\*\](.*)/", "<li>$1</li>", $text);
        $text = preg_replace("/\[quote\=(.+?)\]/", "<p class=\"message-quote-author-sign\">$1 " . LanguageManager::GetTranslation("quote_said") .
                                                   ":</p><div class=\"message-quote-block\"><span style=\"font-size: 50px; display: inline-block;\">“</span>", $text);
        $text = preg_replace("/\[link\=(.+?)\](.*)\[\/link\]/", "<a href=\"$1\" class=\"profile-link\">$2</a>", $text);
        PluginManager::ProcessingBBCodeFromDB($text);

        return $text;
    }

    public static function CompileMentions($stext) {
        $text = $stext;

        //Searching for mentions.
        preg_match_all("/@([A-Za-z0-9\-_]+)/", $text, $matches);
        for ($i = 0 ; $i < count($matches[1]) ; $i++) {
            if ($mentionUserId = UserAgent::GetUserId($matches[1][$i])) {
                $mentionUserNickname = UserAgent::GetUserNick($mentionUserId);
                $text                = preg_replace("/(@$mentionUserNickname)/", "<a href=\"profile.php?uid=$mentionUserId\" class=\"mention mention-success\">$1</a>", $text);
            }
            else {
                $mentionUserNickname = $matches[1][$i];
                $text                =
                    preg_replace("/(@$mentionUserNickname)/", "<span class=\"mention mention-fail\" title=\"" . LanguageManager::GetTranslation("this_user_does_not_exist") . "\">$1</a>", $text);
            }
        }
        return $text;
    }

    public static function GetLanguagePacks() {
        $filesIn         = scandir("./languages/");
        $filesListReturn = [];
        $s               = ".";
        for ($i = 2 ; $i < count($filesIn) ; $i++) {
            $expl = explode($s, $filesIn[$i]);
            if ($expl[1] == "php")
                array_push($filesListReturn, $expl[0]);
        }
        return $filesListReturn;
    }

    public static function GetTemplatesPacks() {
        $filesIn         = scandir("./site/templates/");
        $filesListReturn = [];
        foreach ($filesIn as $f) {
            if (is_dir("./site/templates/" . $f) && (!in_array($f, [".", ".."]))) array_push($filesListReturn, $f);
        }
        return $filesListReturn;
    }

    public static function GetReportReasons() {
        $reasons = file_get_contents(CONFIG_ROOT . "represes.sfc", FILE_USE_INCLUDE_PATH);
        return $reasons;
    }

    public static function SaveReportReasons($text) {
        $reasons = file_put_contents(CONFIG_ROOT . "represes.sfc", $text, FILE_USE_INCLUDE_PATH);
        return $reasons;
    }

    public static function GetCensoredWords() {
        $censors = file_get_contents(CONFIG_ROOT . "censore.sfc", FILE_USE_INCLUDE_PATH);
        return $censors;
    }

    public static function SaveCensoredWords($text) {
        $censored = file_put_contents(CONFIG_ROOT . "censore.sfc", $text, FILE_USE_INCLUDE_PATH);
        return $censored;
    }

    public static function GetSiteTime() {
        return time() - date("Z") + 60 * 60 * Engine::GetEngineInfo("srt");
    }

    public static function SaveAnalyticScript($text) {
        return file_put_contents(CONFIG_ROOT . "analytic.js", $text, FILE_USE_INCLUDE_PATH);
    }

    public static function GetAnalyticScript() {
        return file_get_contents(CONFIG_ROOT . "analytic.js", FILE_USE_INCLUDE_PATH);
    }

    public static function ChatFilter($text) {
        $stext    = $text;
        $censored = self::GetCensoredWords();
        $censored = explode(",", $censored);
        switch (Engine::GetEngineInfo("cfm")) {
            case 0:
                $censoredWord = "[" . LanguageManager::GetTranslation("censored") . "]";
                break;
            case 1:
                $censoredWord = "!@#$%";
                break;
            case 2:
                $censoredWord = "[" . Engine::GetEngineInfo("cfrf") . "]";
                break;
        }
        foreach ($censored as $word) {
            if (empty($word)) {
                continue;
            }

            $stext = preg_replace('/\b' . preg_quote($word, '/') . '\b/u', "$censoredWord", $stext);
        }
        $stext = str_replace("{", '&#123;', $stext);
        $stext = str_replace("}", '&#125;', $stext);
        return $stext;
    }

    public static function MakeUnactiveCodeWords(string $string) {
        $string = str_replace("{", '&#123;', $string);
        $string = str_replace("}", '&#125;', $string);
        return $string;
    }

    public static function StripScriptTags(string $string) {
        $string = str_replace("<script", "&lt;script", $string);
        $string = str_replace("</script>", "&lt;/script&gt;", $string);
        return $string;
    }

    /**
     * Changing service tags for necessary text.
     *
     * @param $search  string Replacing text
     * @param $replace string New text instead searching.
     * @param $text    string Text of page.
     * @return string Output buffer content with replaced service tag.
     */
    public static function replaceFirst($search, $replace, $text) : string {
        //Find position of first mention...
        $pos = strpos($text, $search);
        //If mention exists, we replace its content.
        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }
}