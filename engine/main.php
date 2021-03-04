<?php

namespace Engine {

    use Users\GroupAgent;
    use Users\User;
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

        static private $NeedActivate = False;
        static private $StandartGroup = 1;
        static private $MultiAccPermitted;

        static private $AvatarHeight = 100;
        static private $AvatarWidth = 100;
        static private $UploadPermittedSize = 10 * 1024 * 1024;
        static private $UploadPermittedFormats = "gif,png,img,tif,zip,rar,txt,doc";
        static private $CanGuestsSeeProfiles = false;
        static private $CanUsersReputationVoteManyTimes;

        static private $SiteMetricType;
        static private $SiteMetricStatus;

        public static function ConstructTemplatePath($loadingPage, $module = "", $ext = "php")
        {
            return $_SERVER["DOCUMENT_ROOT"] . "/site/templates/" . Engine::$SiteTemplate . "/$module/$loadingPage.$ext";
        }

        public static function GetDBInfo($code)
        {
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

        public static function DateFormatToRead(string $string)
        {
            $month = array(
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
            );

            $exploded = explode("-", $string);
            $result = $exploded[2] . " " . $month[$exploded[1]] . " " . $exploded[0] . " " . LanguageManager::GetTranslation("year");
            return $result;
        }

        public static function DatetimeFormatToRead($string)
        {
            //Format: Y-m-d H:i:s
            $month = array(
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
            );

            $parts = explode(" ", $string);
            $date = explode("-", $parts[0]);
            $result = $parts[1] . " " . $date[2] . " " . $month[$date[1]] . " " . $date[0] . " " . LanguageManager::GetTranslation("year");
            return $result;
        }

        public static function BooleanToWords($bool)
        {
            return ($bool) ? LanguageManager::GetTranslation("yes") : LanguageManager::GetTranslation("no");
        }

        public static function LoadEngine()
        {
            $file = file_get_contents("config/config.sfc", FILE_USE_INCLUDE_PATH);
            $a = unserialize($file);

            $engConf = json_decode(file_get_contents("config/dbconf.sfc", FILE_USE_INCLUDE_PATH), true);

            self::$EmailAcc = $a["emailAcc"];
            self::$EmailPass = $a["emailPass"];
            self::$EmailHost = $a["emailHost"];
            self::$EmailPort = $a["emailPort"];
            self::$EmailCP = $a["emailCP"];

            self::$DBName = $engConf["dbName"];
            self::$DBPass = $engConf["dbPass"];
            self::$DBHost = $engConf["dbHost"];
            self::$DBLogin = $engConf["dbLogin"];
            self::$DBPort = $engConf["dbPort"];
            self::$DBDriver = $engConf["dbDriver"];

            self::$DomainSite = $a["domainSite"];
            self::$SiteName = $a["siteName"];
            self::$SiteTagline = $a["siteTagline"];
            self::$SiteStatus = $a["siteStatus"];
            self::$SiteSubscribe = $a["siteSubscribe"];
            self::$SiteHashtags = $a["siteHashtags"];
            self::$SiteLang = $a["siteLang"];
            self::$SiteTemplate = $a["siteTemplate"];
            self::$SiteRegionTime = $a["siteRegionTime"];

            self::$NeedActivate = $a["needActivate"];
            self::$MultiAccPermitted = $a["multiAccount"];
            self::$StandartGroup = $a["standartGroup"];

            self::$AvatarHeight = $a["avatarHeight"];
            self::$AvatarWidth = $a["avatarWidth"];
            self::$UploadPermittedSize = $a["uploadPermSize"];
            self::$UploadPermittedFormats = $a["uploadPermFormats"];
            self::$CanGuestsSeeProfiles = $a["guestsseeprofiles"];
            self::$CanUsersReputationVoteManyTimes = $a["multivoterep"];

            self::$SiteMetricType = $a["metricType"];
            self::$SiteMetricStatus = $a["metricStatus"];
            LanguageManager::load();

            include "guards.php";
            include "users.php";
            include "forum.php";
            include "decorator.php";
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

            //Definition constant for correct working.
            define("TT_ADMINPANEL", __DIR__ . "../adminpanel.php");
            define("TT_INDEX", __DIR__ . "../index.php");
            define("TT_PROFILE", __DIR__ . "../profile.php");
            define("TT_BAN", __DIR__ . "../banned.php");


            @$htaccessGlobal = file_get_contents("../../.htaccess", true);
            @$htaccessGlobal = preg_replace("/php_value upload_max_filesize [0-9]+/", "php_value upload_max_filesize " . self::GetEngineInfo("ups"), $htaccessGlobal);
            @file_put_contents("../../.htaccess", $htaccessGlobal);
        }

        public static function SettingsSave($DomainSite, $siteName, $siteTagline, $siteStatus,
                                            $siteSubscribe, $siteHashtags, $siteLang, $siteTemplate, $siteRegionTime,
                                            $emailAcc, $emailPass, $emailHost, $emailPort, $emailCP, $needActivate, $multiAccPermitted, $standartGroup,
                                            $avatarHeight, $avatarWidth, $uploadPermittedSize, $uploadPermittedFormats, $canGuestsSeeProfiles, $canMultiRepVote,
                                            $siteMetricStatus, $siteMetricType)
        {
            $settingsArray = array(
                'domainSite' => $DomainSite,
                'siteName' => $siteName,
                'siteTagline' => $siteTagline,
                'siteStatus' => $siteStatus,
                'siteSubscribe' => $siteSubscribe,
                'siteHashtags' => $siteHashtags,
                'siteLang' => $siteLang,
                'siteTemplate' => $siteTemplate,
                'siteRegionTime' => $siteRegionTime,
                'emailAcc' => $emailAcc,
                'emailPass' => $emailPass,
                'emailHost' => $emailHost,
                'emailPort' => $emailPort,
                'emailCP' => $emailCP,
                'needActivate' => $needActivate,
                'multiAccount' => $multiAccPermitted,
                'standartGroup' => $standartGroup,
                'avatarHeight' => $avatarHeight,
                'avatarWidth' => $avatarWidth,
                'uploadPermSize' => $uploadPermittedSize,
                'uploadPermFormats' => $uploadPermittedFormats,
                'guestsseeprofiles' => $canGuestsSeeProfiles,
                "multivoterep" => $canMultiRepVote,
                'metricType' => $siteMetricType,
                'metricStatus' => $siteMetricStatus
            );
            if (file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/engine/config/config.sfc", serialize($settingsArray))) return True;
            else {
                ErrorManager::GenerateError(14);
                return ErrorManager::GetError();
            }
        }

        public static function GetEngineInfo($code)
        {
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
                case "smt":
                    return self::$SiteMetricType;
                case "sms":
                    return self::$SiteMetricStatus;
            }

            return false;
        }

        public static function RandomGen($lenght = 8)
        {

            $letters = array(
                0 => 'a', 30 => 'A',
                1 => 'b', 31 => 'B',
                2 => 'c', 32 => 'C',
                3 => 'd', 33 => 'D',
                4 => 'e', 34 => 'E',
                5 => 'f', 35 => 'F',
                6 => 'g', 36 => 'G',
                7 => 'h', 37 => 'H',
                8 => 'j', 38 => 'J',
                9 => 'k', 39 => 'K',
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
                29 => '9', 59 => '0'
            );

            $result = "";
            for ($i = 0; $i < $lenght; $i++) {

                $result .= $letters[rand(0, 59)];

            }

            return $result;

        }

        public static function CompileBBCode($stext)
        {
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
            $text = str_ireplace("[quote]", "<p class=\"message-quote-author-sign\">" . LanguageManager::GetTranslation("quote_anonim_said") . ":</p><div class=\"message-quote-block\"><span style=\"font-size: 50px; display: inline-block;\">“</span>", $text);
            $text = str_ireplace("[/quote]", "</div>", $text);
            $text = str_ireplace("[/align]", "</p>", $text);
            $text = str_ireplace("[spoiler]", "<div class=\"col-12 div-spoiler\"><span class=\"glyphicons glyphicons-alert\"></span> " . LanguageManager::GetTranslation("spoiler") . "<div hidden>", $text);
            $text = str_ireplace("[/spoiler]", "</div></div>", $text);
            $text = preg_replace("/\[size\=(\d+)\]/", "<p style=\"font-size: $1px;\">", $text);
            $text = preg_replace("/\[youtube\=https:\/\/youtu\.be\/(.+)\]/", "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>", $text);
            while (preg_match("/\[img\=(.+?)\]/", $text, $results) != false) {
                $sizes = getimagesize($results[1]);
                $text = preg_replace("/\[img\=(.+?)\]/", "<div class=\"img-container\"><a href=\"$1\" data-lightbox=\"image\"><img class=\"img-for-frame\" src=\"$1\"></a><p>$sizes[0]x$sizes[1]</p></div>", $text, 1);
            }
            $text = preg_replace("/\[align\=(.+?)\]/", "<p style=\"text-align: $1;\">", $text);
            $text = preg_replace("/\[color\=(.+?)\]/", "<span style=\"color: $1;\">", $text);
            $text = preg_replace("/\[\*\](.*)/", "<li>$1</li>", $text);
            $text = preg_replace("/\[quote\=(.+?)\]/", "<p class=\"message-quote-author-sign\">$1 " . LanguageManager::GetTranslation("quote_said") . ":</p><div class=\"message-quote-block\"><span style=\"font-size: 50px; display: inline-block;\">“</span>", $text);
            $text = preg_replace("/\[link\=(.+?)\](.*)\[\/link\]/", "<a href=\"$1\" class=\"profile-link\">$2</a>", $text);


            return $text;
        }

        public static function CompileMentions($stext)
        {
            $text = $stext;

            //Searching for mentions.
            preg_match_all("/@([A-Za-z0-9\-_]+)/", $text, $matches);
            for ($i = 0; $i < count($matches[1]); $i++) {
                if ($mentionUserId = UserAgent::GetUserId($matches[1][$i])) {
                    $mentionUserNickname = UserAgent::GetUserNick($mentionUserId);
                    $text = preg_replace("/(@$mentionUserNickname)/", "<a href=\"profile.php?uid=$mentionUserId\" class=\"mention mention-success\">$1</a>", $text);
                } else {
                    $mentionUserNickname = $matches[1][$i];
                    $text = preg_replace("/(@$mentionUserNickname)/", "<span class=\"mention mention-fail\" title=\"" . LanguageManager::GetTranslation("this_user_does_not_exist") . "\">$1</a>", $text);
                }
            }
            return $text;
        }

        public static function GetLanguagePacks()
        {
            $filesIn = scandir("./languages/");
            $filesListReturn = array();
            $s = ".";
            for ($i = 2; $i < count($filesIn); $i++) {
                $expl = explode($s, $filesIn[$i]);
                if ($expl[1] == "php")
                    array_push($filesListReturn, $expl[0]);
            }
            return $filesListReturn;
        }

        public static function GetTemplatesPacks()
        {
            $filesIn = scandir("./site/templates/");
            $filesListReturn = array();
            foreach ($filesIn as $f) {
                if (is_dir("./site/templates/" . $f) && (!in_array($f, [".", ".."]))) array_push($filesListReturn, $f);
            }
            return $filesListReturn;
        }

        public static function GetReportReasons()
        {
            $reasons = file_get_contents("config/represes.sfc", FILE_USE_INCLUDE_PATH);
            return $reasons;
        }

        public static function SaveReportReasons($text)
        {
            $reasons = file_put_contents("config/represes.sfc", $text, FILE_USE_INCLUDE_PATH);
            return $reasons;
        }

        public static function GetCensoredWords()
        {
            $censors = file_get_contents("config/censore.sfc", FILE_USE_INCLUDE_PATH);
            return $censors;
        }

        public static function SaveCensoredWords($text)
        {
            $censored = file_put_contents("config/censore.sfc", $text, FILE_USE_INCLUDE_PATH);
            return $censored;
        }

        public static function GetSiteTime()
        {
            return time() - date("Z") + 60 * 60 * Engine::GetEngineInfo("srt");
        }

        public static function SaveAnalyticScript($text)
        {
            return file_put_contents("config/analytic.js", $text, FILE_USE_INCLUDE_PATH);
        }

        public static function GetAnalyticScript()
        {
            return file_get_contents("config/analytic.js", FILE_USE_INCLUDE_PATH);
        }

        public static function ChatFilter($text)
        {
            $stext = $text;
            $censored = self::GetCensoredWords();
            $censored = explode(",", $censored);
            foreach ($censored as $word) {
                $stext = str_ireplace($word, "[цензура]", $stext);
            }
            $stext = str_replace("{", '&#123;', $stext);
            $stext = str_replace("}", '&#125;', $stext);
            return $stext;
        }

        public static function MakeUnactiveCodeWords(string &$string){
            $string = str_replace("{", '&#123;', $string);
            $string = str_replace("}", '&#125;', $string);
            return $string;
        }

        public static function StripScriptTags(string $string){
            $string = str_replace("<script", "&lt;script", $string);
            $string = str_replace("</script>", "&lt;/script&gt;", $string);
            return $string;
        }
    }

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

            ob_end_flush();
            ob_start();
            include_once Engine::ConstructTemplatePath("main", "error", "html");

            $excCatcher = getBrick();
            ob_end_flush();
            $excCatcher = str_replace("{ERROR_CODE}", ErrorManager::GetError(), $excCatcher);
            $excCatcher = str_replace("{SITE_NAME}", Engine::GetEngineInfo("sn"), $excCatcher);
            $excCatcher = str_replace("{ERROR_MANAGER:EXCEPTION_FORMATED_TEXT}", nl2br($exception->getTraceAsString()), $excCatcher);
            $excCatcher = str_replace("{ERROR_MANAGER:MESSAGE}", $exception->getMessage(), $excCatcher);
            echo $excCatcher;

        }
    }

    class Uploader
    {
        public static function ExtractType($path)
        {

            $tmp = explode(".", $path);
            return end($tmp);

        }

        public static function UploadFile($idUser, $file)
        {
            if ($file['name'] == '') {
                ErrorManager::GenerateError(28);
                return ErrorManager::GetError();
            }

            if (!UserAgent::IsUserExist($idUser)) {
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            $types = Engine::GetEngineInfo("upf");
            $maxsize = Engine::GetEngineInfo("ups");

            if (!strstr($types, self::ExtractType($file['name']))) {
                ErrorManager::GenerateError(13);
                return ErrorManager::GetError();
            }

            if ($file['size'] >= $maxsize) {
                ErrorManager::GenerateError(27);
                return ErrorManager::GetError();
            }

            $images = array();
            $docs = array();
            $zips = array();
            $other = array();

            $types = explode(",", $types);
            for ($i = 0; $i < count($types); $i++) {
                if (in_array($types[$i], ['gif', 'png', 'bmp', 'tiff', 'tif', 'jpeg', 'jpg'])) array_push($images, $types[$i]);
                elseif (in_array($types[$i], ['doc', 'txt', 'xls', 'ppt', 'pptx', 'docx'])) array_push($docs, $types[$i]);
                elseif (in_array($types[$i], ['zip', 'rar', 'tar', 'gzip', '7z', 'gz'])) array_push($zips, $types[$i]);
                else array_push($other, $types[$i]);
            }

            $uploadPath = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";
            $filePath = '';

            if (in_array(self::ExtractType($file['name']), $images)) {
                $uploadPath .= "images/";
                $filePath = "uploads/images/";
            }
            if (in_array(self::ExtractType($file['name']), $docs)) {
                $uploadPath .= "docs/";
                $filePath = "uploads/docs/";
            }
            if (in_array(self::ExtractType($file['name']), $zips)) {
                $uploadPath .= "zips/";
                $filePath = "uploads/zips/";
            }
            if (in_array(self::ExtractType($file['name']), $other)) {
                $uploadPath .= "others/";
                $filePath = "uploads/others/";
            }

            $newName = Engine::RandomGen() . "." . self::ExtractType($file['name']);

            if (move_uploaded_file($file['tmp_name'], $uploadPath . $newName)) {
                return (bool) DataKeeper::InsertTo("tt_uploads", ["file_path" => $filePath, "name" => $newName, "author" => $idUser, "upload_date" => date("Y-m-d", Engine::GetSiteTime())]);
            } else {
                ErrorManager::GenerateError(12);
                return ErrorManager::GetError();
            }
        }

        public static function GetUploadList($idUser)
        {
            return DataKeeper::Get("tt_uploads", ["id"], ["author" => $idUser]);
        }

        public static function GetUploadedFilesList(int $page)
        {
            $lowBorder = $page * 50 - 50;
            $highBorder = 50;

            return DataKeeper::MakeQuery("SELECT * FROM `tt_uploads` ORDER BY id DESC LIMIT $lowBorder,$highBorder", null, true);
        }

        public static function GetUploadedFilesListByAuthor(string $nickname, int $page)
        {
            $lowBorder = $page * 50 - 50;
            $highBorder = 50;

            return DataKeeper::MakeQuery("SELECT *
                                                    FROM `tt_uploads`
                                                    WHERE `author` IN 
                                                    (SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?)
                                                    ORDER BY id DESC
                                                    LIMIT $lowBorder,$highBorder", ["%$nickname%"], true);
        }

        public static function GetUploadedFilesListByReference(string $ref, int $page)
        {
            $lowBorder = $page * 50 - 50;
            $highBorder = 50;

            $queryResponse = DataKeeper::MakeQuery("SELECT * FROM tt_uploads 
                                                     WHERE name LIKE ?
                                                     ORDER BY id DESC                                                       
                                                     LIMIT $lowBorder,$highBorder", ["%.$ref"], true);

            $files = [];
            foreach ($queryResponse as $response){
                $files[] = [
                    "id" => $response["id"],
                    "file_path" => $response["file_path"],
                    "upload_date" => $response["upload_date"],
                    "name" => $response["name"],
                    "author" => $response["author"]
                ];
            }
            return $files;
        }

        public static function GetUploadInfo($fId, $param)
        {
            return DataKeeper::Get("tt_uploads", [$param], ["id" => $fId])[0][$param];
        }

        public static function DeleteFile($fId)
        {
            if (!unlink($_SERVER["DOCUMENT_ROOT"] . "/" . self::GetUploadInfo($fId, "file_path")[0]["file_path"] . self::GetUploadInfo($fId, "name")[0]["name"]))
                return false;

            return DataKeeper::Delete("tt_uploads", ["id" => $fId]);

        }

        public static function DeleteFilesOfUser(int $userId)
        {
            return DataKeeper::Delete("tt_uploads", ["author" => $userId]);
        }

    }

    class LanguageManager
    {
        private static $languageArray = [];

        /**
         * Include language file to project.
         *
         * @return mixed
         */
        public static function load()
        {
            if (Engine::GetEngineInfo("sl") == "")
                $languageFile = $_SERVER["DOCUMENT_ROOT"] . "/languages/English.php";
            else
                $languageFile = $_SERVER["DOCUMENT_ROOT"] . "/languages/" . Engine::GetEngineInfo("sl") . ".php";
            if (!file_exists($languageFile))
                throw new \Error("Language file is not exist");
            require $languageFile;
            self::$languageArray = $languagePack;
        }


        /** Return translated value from language dictionary by path.
         * @param string $path
         * @return string
         */
        public static function GetTranslation(string $path, ...$vars)
        {
            if (isset(self::$languageArray[$path]) && !is_array(self::$languageArray[$path]))
                return self::$languageArray[$path];

            $path = trim($path);

            $exploded = explode(".", $path);

            if (end($exploded) == "")
                return $path;

            $think = null;
            for ($i = 0; $i < count($exploded); $i++) {
                if (empty($think)) {
                    //If $think is empty set it into var.
                    $think = self::$languageArray[$exploded[$i]];
                } else {
                    if (is_array($think)) {
                        $think = $think[$exploded[$i]];
                    } else {
                        return $think;
                    }
                }
            }
            if (!empty($think)) {
                $time = 0;
                foreach ($vars as $var) {
                    $time++;
                    $param = "{" . $time . "}";
                    $think = str_ireplace($param, $var, $think);
                }
                return $think;
            } else
                return $path;
        }

    }

    class Mailer
    {
        public static function SendMail($text, $sendTo, $subject)
        {
            if ($text == "") return false;
            require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/mailer/autoload.php";

            // Create the Transport
            $transport = (new \Swift_SmtpTransport(Engine::GetEngineInfo("ecp") . "://" . Engine::GetEngineInfo("eh"), Engine::GetEngineInfo("ept")))
                ->setUsername(Engine::GetEngineInfo("el"))
                ->setPassword(Engine::GetEngineInfo("ep"));

            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);

            // Create a message
            $message = (new \Swift_Message($subject))
                ->setFrom([Engine::GetEngineInfo("el") => LanguageManager::GetTranslation("postman.administration") . ' "' . Engine::GetEngineInfo("sn") . '"'])
                ->setTo([$sendTo])
                ->setBody($text, "text/html");
            $message->addPart(strip_tags($text), "text/plain");

            // Send the message
            $result = $mailer->send($message);
            return $result;
        }
    }

    class DataKeeper
    {
        private static $errMessage = "";

        private static function connect()
        {
            $dsn = Engine::GetDBInfo(5) . ":dbname=" . Engine::GetDBInfo(3) . ";host=" . Engine::GetDBInfo(0) . ";port=" . Engine::GetDBInfo(4);
            $dblogin = Engine::GetDBInfo(1);
            $dbpass = Engine::GetDBInfo(2);
            try {
                $pdo = new \PDO($dsn, $dblogin, $dbpass);
                return $pdo;
            } catch (\PDOException $pdoExcp) {
                ErrorManager::GenerateError(2);
                ErrorManager::PretendToBeDied(ErrorManager::GetErrorCode(2), $pdoExcp);
            }
            return false;
        }

        public static function getMax($table, $column)
        {
            $pdo = self::connect();
            $preparedQuery = $pdo->prepare("SELECT MAX(`$column`) FROM $table");
            $preparedQuery->execute();
            $result = $preparedQuery->fetch();
            return $result[0];
        }

        public static function isExistsIn($table, $column, $content)
        {
            $pdo = self::connect();
            $query = "SELECT count(*) FROM `$table` WHERE `$column`=?";
            $preparedQuery = $pdo->prepare($query);
            $preparedQuery->execute([$content]);
            $result = $preparedQuery->fetch($pdo::FETCH_ASSOC);
            if ($result["count(*)"] > 0) {
                return true;
            } else {
                return false;
            }

        }

        public static function _isExistsIn($table, array $content)
        {
            $keys = "";
            $values = [];
            foreach ($content as $key => $value) {
                $keys .= "`$key`=? AND";
                $values[] = $value;
            }
            $keys = rtrim($keys, "AND");
            $pdo = self::connect();
            $query = "SELECT `id` FROM `$table` WHERE $keys LIMIT 1";
            $prepared = $pdo->prepare($query);
            $prepared->execute($values);
            $result = $prepared->rowCount();
            if ($result == 0) return false;
            else return true;
        }

        /**Insert into table one record.
         *
         * @param string $table Name of table.
         * @param array $varsArr Associative array where key is column and value is content.
         * @return int
         */
        public static function InsertTo($table, array $varsArr)
        {
            $pdo = self::connect();
            $keys = "";
            $values = "";
            $varsArrToSend = [];
            foreach ($varsArr as $key => $value) {
                $keys .= "`$key`,";
                $values .= "?,";
                $varsArrToSend[] = $value;
            }
            $keys = rtrim($keys, ",");
            $values = rtrim($values, ",");
            $query = "INSERT INTO $table ($keys) VALUES ($values)";
            $preparedQuery = $pdo->prepare($query);
            $execute = $preparedQuery->execute($varsArrToSend);
            if ($execute) {
                return $pdo->lastInsertId();
            } else {
                self::$errMessage = $pdo->errorInfo();
                return 0;
            }
        }

        /** Update value in table of fields by filter.
         *
         * @param string $table Table that need to update.
         * @param array $varsArr Associative array with new value of field as key.
         * @param array $whereArr Associative array with values of field as key.
         * @return bool
         */
        public static function Update(string $table, array $varsArr, array $whereArr)
        {
            $pdo = self::connect();
            $keys = "";
            $whereKeys = "";
            $varsArrToSend = [];
            foreach ($varsArr as $key => $value) {
                $keys .= "`$key`=?,";
                $varsArrToSend[] = $value;
            }
            foreach ($whereArr as $whereKey => $whereValue) {
                $whereKeys .= "`$whereKey`=? AND";
                $varsArrToSend[] = $whereValue;
            }

            $keys = rtrim($keys, ", ");
            $whereKeys = rtrim($whereKeys, "AND");
            $query = "UPDATE $table SET $keys WHERE $whereKeys";
            $preparedQuery = $pdo->prepare($query);
            if ($preparedQuery->execute($varsArrToSend))
                return true;
            else {
                ErrorManager::GenerateError(33);
                ErrorManager::PretendToBeDied("Cannot execute UPDATE query: " . $preparedQuery->errorInfo()[2], new \PDOException($preparedQuery->errorInfo()[2]));
                return false;
            }
        }

        /** Delete record from table.
         * @param string $table Name of table
         * @param array $whereArr Associative array where key is name of column and value is value of column.
         * @return int Count of affected rows.
         */
        public static function Delete($table, array $whereArr)
        {
            $pdo = self::connect();
            $whereStr = "";
            $varsArrToSend = [];
            foreach ($whereArr as $key => $value) {
                $whereStr .= "`$key`=? AND";
                $varsArrToSend[] = $value;
            }
            $whereStr = rtrim($whereStr, "AND");
            $query = "DELETE FROM `$table` WHERE $whereStr";
            $preparedQuery = $pdo->prepare($query);
            $preparedQuery->execute($varsArrToSend);
            return $preparedQuery->rowCount();
        }

        /** Executes "SELECT" query to $table of database and returns
         *  associative array as result.
         *
         * @param string $table Name of table
         * @param array $whatArr Array with name of necessary row.
         * @param array $whereArr
         * @return array Array with results. First record has number 0 in resultative response.
         */
        public static function Get($table, array $whatArr, array $whereArr = null, int $limit = -1)
        {
            $pdo = self::connect();
            $varsArrToSend = [];
            if ($whereArr != null) {
                $whereStr = "";
                foreach ($whereArr as $key => $value) {
                    $whereStr .= "`$key`=? AND ";
                    $varsArrToSend[] = $value;
                }
            }
            $whatStr = "";
            foreach ($whatArr as $key => $value) {
                if ($value != "*")
                    $whatStr .= "`$value`,";
                else {
                    $whatStr .= "$value,";
                    break;
                }
            }
            $whatStr = rtrim($whatStr, ",");
            $whereStr = rtrim(@$whereStr, " AND ");

            if ($whereArr != null) {
                $query = "SELECT $whatStr FROM `$table` WHERE $whereStr" . (($limit > 0) ? " LIMIT $limit" : "");
                $preparedQuery = $pdo->prepare($query);
                $preparedQuery->execute($varsArrToSend);
            } else {
                $query = "SELECT $whatStr FROM `$table`" . (($limit > 0) ? " LIMIT $limit" : "");
                $preparedQuery = $pdo->prepare($query);
                $preparedQuery->execute();
            }
            if ($pdo->errorInfo()[3] != null){
                ErrorManager::GenerateError(39);
                ErrorManager::PretendToBeDied(ErrorManager::GetErrorCode(39), new \PDOException(LanguageManager::GetTranslation("sql_syntax_error")));
                exit;
            }
            return $preparedQuery->fetchAll($pdo::FETCH_ASSOC);
        }

        /** Executes SQL query.
         *
         * @param string $query SQL query
         * @param array|null $whereArr Associative array with values by order.
         * @param bool $multiResponse If true returns all fetches lines.
         * @return array|bool|mixed One fetched line or all fetched lines. If query is invalid returns false.
         */
        public static function MakeQuery($query, array $whereArr = null, bool $multiResponse = false)
        {
            $pdo = self::connect();

            $preparedQuery = $pdo->prepare($query);
            if ($whereArr !== null)
                $result = $preparedQuery->execute($whereArr);
            else
                $result = $preparedQuery->execute();
            if (!$result) {
                ErrorManager::GenerateError(33);
                ErrorManager::PretendToBeDied("Cannot make special SQL query: [" . $preparedQuery->errorInfo()[0] . "] " . $preparedQuery->errorInfo()[2], new \PDOException("Cannot make special SQL query."));
                return false;
            }
            if ($multiResponse)
                return $preparedQuery->fetchAll($pdo::FETCH_ASSOC);
            else
                return $preparedQuery->fetch($pdo::FETCH_ASSOC);
        }
    }

    /**
     * Class PluginManager
     * @package Engine
     *
     *
     */
    class PluginManager
    {
        private static $plugins = [];
        private static $installed = [];
        private static $cssLines = [];
        private static $jsHeadLines = [];
        private static $jsFooterLines = [];

        public static function CSSRegister(string $link){
            self::$cssLines[] = '<link href="'. $link . '" rel="stylesheet">' . PHP_EOL;
        }

        public static function JSHeadRegister(string $link){
            self::$jsHeadLines[] = '<script src="'. $link . '"></script>' . PHP_EOL;
        }

        public static function JSFooterRegister(string $link){
            self::$jsFooterLines[] = '<script src="'. $link . '"></script>' . PHP_EOL;
        }

        public static function GetPluginsList()
        {
            $root = scandir("addons/");
            $keymap = [];

            foreach ($root as $folder) {
                if ($folder == '.' && $folder == '..')
                    continue;
                //Кеймапы - это меню на разных языках.
                $keymap[] = $folder;

            }

            foreach ($keymap as $hostFolder) {
                if (is_file("addons/$hostFolder/bin/main.php")) {
                    $conf = include "addons/$hostFolder/config/config.php";
                    if (!$conf) {
                        ErrorManager::GenerateError(40);
                        ErrorManager::PretendToBeDied(ErrorManager::GetErrorCode(2), new \Exception("Configuration file doesn't exist."));
                        return 2;
                    }
                    self::$plugins[$hostFolder]["config"] = $conf;
                }
                continue;
            }
            return self::$plugins;
        }

        public static function InstallPlugin(string $name, string $codeName, string $description, int $status)
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $insertInto = DataKeeper::InsertTo("tt_plugins", ["name" => $name, "codename" => $codeName, "description" => $description, "status" => $status]);
            if ($insertInto > 0) {
                self::$installed[$codeName] = ["name" => $name,
                    "codeName" => $codeName,
                    "description" => $description,
                    "status" => $status];

                $lastId = $insertInto;
            }

            if (is_file( "../../../addons/$codeName/config/traces.php")) {
                $traces = include "../../../addons/$codeName/config/traces.php";

                foreach ($traces as $key => $value){
                    DataKeeper::InsertTo("tt_plugin_trace", ["ofPlugin" => $lastId,
                        "system_text" => $key,
                        "system_text_to" => $value["page"],
                        "strict" => $value["strict"] ]);

                }
            }

            if (is_file("../../../addons/$codeName/config/permissions.php")){
                $permissions = include "../../../addons/$codeName/config/permissions.php";

                foreach (GroupAgent::GetGroupList() as $group) {
                    foreach ($permissions as $permission => $value) {
                        DataKeeper::InsertTo("tt_plugin_permissions", ["codename" => $permission,
                            "value" => $value["default_value"],
                            "ofGroup" => $group,
                            "ofPlugin" => $lastId,
                            "translate_path" => $value["translate_path"]]);
                    }
                }
            }
            return false;
        }

        public static function GetInstalledPlugins(){
            self::$installed = [];
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `id`,`name`, `codename`, `description`, `status` FROM `tt_plugins`")) {
                $stmt->execute();
                //if ($stmt->affected_rows > 0){
                $stmt->bind_result($id,$name, $codeName, $description, $status);
                while ($stmt->fetch()){
                    self::$installed[$codeName] = [
                        "id" => $id,
                        "name" => $name,
                        "codeName" => $codeName,
                        "description" => $description,
                        "status" => $status];
                }
                //}
                return self::$installed;
            }
            return [];
        }

        public static function DeletePlugin(string $codeName){
            $id = DataKeeper::Get("tt_plugins", ["id"], ["codename" => $codeName]);
            DataKeeper::Delete("tt_plugins", ["id" => $id[0]["id"]]);
            DataKeeper::Delete("tt_plugin_trace", ["ofPlugin" => $id[0]["id"]]);
            DataKeeper::Delete("tt_plugin_permissions", ["ofPlugin" => $id[0]["id"]]);
        }

        public static function Integration(string $main){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $notStrict = [];
            $Strict    = [];

            if ($stmt = $mysqli->prepare("SELECT `trace`.`ofPlugin`, 
			                                                      `trace`.`system_text`, 
                                                                  `trace`.`system_text_to`,
                                                                  `trace`.`strict`,  
                                                                  `plugins`.`codename`
                                                           FROM `tt_plugin_trace` AS `trace`
                                                           LEFT JOIN `tt_plugins` AS `plugins` ON `trace`.ofPlugin = `plugins`.`id`
                                                           WHERE `plugins`.`status` = 1")) {
                $stmt->execute();
                $stmt->bind_result($ofPluginId, $system_text, $system_text_to, $strict, $codename);
                while ($stmt->fetch()){
                    if ($strict === 0)
                        array_push($notStrict, ["ofPlugin" => $ofPluginId,
                            "system_text" => $system_text,
                            "system_text_to" => $system_text_to,
                            "codename" => $codename]);
                    else {
                        array_push($Strict, ["ofPlugin" => $ofPluginId,
                            "system_text" => $system_text,
                            "system_text_to" => $system_text_to,
                            "codename" => $codename]);
                    }
                }
                $stmt->close();
            }

            $forPage = "";
            foreach($notStrict as $value){
                if (strstr($main, $value["system_text"]) !== false) {
                    if (file_exists("addons/" . $value["codename"] . "/bin/" . $value["system_text_to"])) {
                        include_once "addons/" . $value["codename"] . "/bin/" . $value["system_text_to"];
                        $forPage = getBrick();
                    } else {
                        $forPage = "Тhis file does not exist.";
                        $plugId = DataKeeper::Get("tt_plugins", ["id"], ["codename" => $codename]);
                        DataKeeper::Delete("tt_plugins", ["codename" => $codename]);
                        DataKeeper::Delete("tt_plugin_trace", ["ofPlugin" => $plugId]);
                    }

                    $main = str_replace($value["system_text"], $forPage, $main);
                }
            }

            foreach($Strict as $value) {
                if (strstr($main, $value["system_text"]) !== false) {
                    if (file_exists("addons/" . $value["codename"] . "/bin/" . $value["system_text_to"])) {
                        include_once "addons/" . $value["codename"] . "/bin/" . $value["system_text_to"];
                        $forPage = getBrick();
                    } else {
                        $forPage = "This file does not exist.";
                        $plugId = DataKeeper::Get("tt_plugins", ["id"], ["codename" => $codename]);
                        DataKeeper::Delete("tt_plugins", ["codename" => $codename]);
                        DataKeeper::Delete("tt_plugin_trace", ["ofPlugin" => $plugId]);
                    }

                    $main = str_replace_once($value["system_text"], $forPage, $main);
                }
            }

            $css = array_unique(self::$cssLines);
            $headJS = array_unique(self::$jsHeadLines);
            $footerJS = array_unique(self::$jsFooterLines);

            $main = str_replace_once("{PLUGIN_HEAD_JS}", $headJS, $main);
            $main = str_replace_once("{PLUGINS_STYLESHEETS}", $css, $main);
            $main = str_replace_once("{PLUGIN_FOOTER_JS}", $footerJS, $main);

            echo $main;
        }

        public static function IntegrateCSS(string $string){
            $css = array_unique(self::$cssLines);
            $string = str_replace_once("{PLUGINS_STYLESHEETS}", $css, $string);

            return $string;
        }

        public static function IntegrateFooterJS(string $string){
            $footerJS = array_unique(self::$jsFooterLines);
            $string = str_replace_once("{PLUGIN_FOOTER_JS}", $footerJS, $string);

            return $string;
        }

        public static function GetPluginId(string $codename){
            return DataKeeper::Get("tt_plugins", ["id"], ["codename" => $codename])[0]["id"];
        }
        /**
         * Return translation in dependence of site language.
         * If file with site language doesn't exist then return English version.
         *
         * @param string $varPath
         * @return array|mixed|string|null
         * @throws \Exception
         */
        public static function GetTranslation(string $varPath, ...$vars){
            $path       = explode(".", $varPath);
            $pluginName = reset($path);

            if (file_exists("addons/$pluginName/languages/" . Engine::GetEngineInfo("sl") . ".php")){
                $languageFile = Engine::GetEngineInfo("sl");
            } elseif (file_exists("addons/$pluginName/languages/English.php")){
                $languageFile = "English";
            } else {
                ErrorManager::GenerateError(38);
                ErrorManager::PretendToBeDied("Plugin's name is $pluginName", new \Exception("Plugin has no language files."));
            }

            if (!is_dir("addons/$pluginName"))
                throw new \Exception("Plugin with that name does not exist.");
            require "addons/$pluginName/languages/$languageFile.php";
            $language = $languagePack;

            unset($path[0]);

            $think = null;
            for ($i = 1; $i <= count($path); $i++) {
                if (empty($think)) {
                    //If $think is empty set it into var.
                    $think = $language[$path[$i]];
                } else {
                    if (is_array($think)) {
                        $think = $think[$path[$i]];
                    } else {
                        return $think;
                    }
                }
            }
            if (!empty($think)) {
                $time = 0;
                foreach ($vars as $var) {
                    $time++;
                    $param = "{" . $time . "}";
                    $think = str_ireplace($param, $var, $think);
                }
                return $think;
            } else
                return $path;
        }

        /** Return value of permission with name.
         *
         * @param integer $pluginId Plugin ID.
         * @param string $permissionName Permission name.
         * @param int $groupId ID of group for checking.
         * @return bool
         */
        public static function GetPermissionValue(int $pluginId, string $permissionName, int $groupId) : bool {
            $value = DataKeeper::Get("tt_plugin_permissions", ["value"], ["ofPlugin" => $pluginId, "codename" => $permissionName, "ofGroup" => $groupId])[0]["value"];
            if (!empty($value)){
                if ($value <= 0)
                    return false;
                else
                    return true;
            } return false;
        }

        /** Set value of permission with name.
         *
         * @param int $ofPlugin ID parent plugin for permission.
         * @param string $permissionName Permission name.
         * @param int $groupId ID of group for checking.
         * @param bool $value New value of permission.
         * @return bool
         */
        public static function SetPermissionValue(int $ofPlugin, string $permissionName, int $groupId, int $value) : bool {
            return DataKeeper::Update("tt_plugin_permissions",
                ["value" => $value],
                ["codename" => $permissionName,
                    "ofGroup" => $groupId,
                    "ofPlugin" => $ofPlugin]);
        }

        public static function IsTurnOn(string $codeName) : bool {
            $result = DataKeeper::Get("tt_plugins", ["status"], ["codename" => $codeName])[0];
            return (bool) $result["status"];
        }

        /** Return associative array with codename and value of permissions of group.
         * If $groupId is not null return permissions codename and value of that group.
         * If permissions doesn't exist return false.
         *
         * @param int $pluginId
         * @param int|null $groupId
         * @return array|bool
         */
        public static function GetPermissionsOfPlugin(int $pluginId, int $groupId = null) {
            if (is_null($groupId))
                $array = DataKeeper::Get("tt_plugin_permissions", ["codename", "translate_path", "value"], ["ofPlugin" => $pluginId]);
            else
                $array = DataKeeper::Get("tt_plugin_permissions", ["codename", "translate_path", "value"], ["ofPlugin" => $pluginId, "ofGroup" => $groupId]);

            if (!empty($array))
                return $array;
            else
                return false;
        }
    }
}
?>