<?php

    namespace Engine {

        use Users\User;
        use Users\UserAgent;

        class Engine
        {
            static private $DBName;
            static private $DBPass;
            static private $DBHost;
            static private $DBLogin;

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
            static private $UploadPermittedSize = 10*1024*1024;
            static private $UploadPermittedFormats = "gif,png,img,tif,zip,rar,txt,doc";
            static private $CanGuestsSeeProfiles = false;
            static private $CanUsersReputationVoteManyTimes;

            static private $SiteMetricType;
            static private $SiteMetricStatus;

            public static function ConstructTemplatePath($loadingPage,$module = "", $ext = "php"){
                return $_SERVER["DOCUMENT_ROOT"] . "/site/templates/" . Engine::$SiteTemplate . "/$module/$module$loadingPage.$ext";
            }

            public static function GetDBInfo($code)
            {
                switch($code) {
                    case 1:
                        return self::$DBLogin;
                    case 2:
                        return self::$DBPass;
                    case 0:
                        return self::$DBHost;
                    case 3:
                        return self::$DBName;
                }

                return false;

            }
            public static function DateFormatToRead($string){
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

                $exploded = explode("-",$string);
                $result = $exploded[2]." ".$month[$exploded[1]]. " " . $exploded[0] . " года";
                return $result;
            }
            public static function DatetimeFormatToRead($string){
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
                $result = $parts[1] . " " . $date[2] . " " . $month[$date[1]] . " " . $date[0] . " года";
                return $result;
            }
            public static function BooleanToWords($bool){
                return ($bool) ? "Да" : "Нет";
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
                if (file_put_contents($_SERVER["DOCUMENT_ROOT"]."/engine/config/config.sfc", serialize($settingsArray))) return True;
                else { ErrorManager::GenerateError(14); return ErrorManager::GetError(); }
            }
            public static function GetEngineInfo($code){
                switch($code){
                    case "na": return self::$NeedActivate;
                    case "map": return self::$MultiAccPermitted;
                    case "sg": return self::$StandartGroup;
                    case "sn": return self::$SiteName;
                    case "stl": return self::$SiteTagline;
                    case "sh": return self::$SiteHashtags;
                    case "sl": return self::$SiteLang;
                    case "srt": return self::$SiteRegionTime;
                    case "dm": return self::$DomainSite;
                    case "ss": return self::$SiteStatus;
                    case "ssc": return self::$SiteSubscribe;
                    case "el": return self::$EmailAcc;
                    case "ep": return self::$EmailPass;
                    case "eh": return self::$EmailHost;
                    case "ept": return self::$EmailPort;
                    case "ecp": return self::$EmailCP;
                    case "aw": return self::$AvatarWidth;
                    case "ah": return self::$AvatarHeight;
                    case "ups": return self::$UploadPermittedSize;
                    case "upf": return self::$UploadPermittedFormats;
                    case "gsp": return self::$CanGuestsSeeProfiles;
                    case "vmr": return self::$CanUsersReputationVoteManyTimes;
                    case "stp": return self::$SiteTemplate;
                    case "smt": return self::$SiteMetricType;
                    case "sms": return self::$SiteMetricStatus;
                }

                return false;
            }
            public static function RandomGen($lenght = 8){

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
                for($i = 0; $i < $lenght; $i++){

                    $result .= $letters[rand(0, 59)];

                }

                return $result;

            }
            public static function CompileBBCode($stext){
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
                $text = str_ireplace("[quote]", "<p class=\"message-quote-author-sign\">Неизвестный автор сказал:</p><div class=\"message-quote-block\"><span style=\"font-size: 50px; display: inline-block;\">“</span>", $text);
                $text = str_ireplace("[/quote]", "</div>", $text);
                $text = str_ireplace("[/align]", "</p>", $text);
                $text = str_ireplace("[spoiler]", "<div class=\"col-12 div-spoiler\"><span class=\"glyphicons glyphicons-alert\"></span> Спойлер<div hidden>", $text);
                $text = str_ireplace("[/spoiler]", "</div></div>", $text);
                $text = preg_replace("/\[size\=(\d+)\]/", "<p style=\"font-size: $1px;\">",$text);
                $text = preg_replace("/\[youtube\=https:\/\/youtu\.be\/(.+)\]/", "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>",$text);
                while (preg_match("/\[img\=(.+?)\]/", $text, $results) != false) {
                    $sizes = getimagesize($results[1]);
                    $text = preg_replace("/\[img\=(.+?)\]/", "<div class=\"img-container\"><img class=\"img-for-frame\" src=\"$1\"><p>$sizes[0]x$sizes[1]</p></div>", $text, 1);
                }
                $text = preg_replace("/\[align\=(.+?)\]/", "<p style=\"text-align: $1;\">",$text);
                $text = preg_replace("/\[color\=(.+?)\]/", "<span style=\"color: $1;\">",$text);
                $text = preg_replace("/\[\*\](.*)/", "<li>$1</li>", $text);
                $text = preg_replace("/\[quote\=(.+?)\]/", "<p class=\"message-quote-author-sign\">$1 сказал(а):</p><div class=\"message-quote-block\"><span style=\"font-size: 50px; display: inline-block;\">“</span>",$text);
                $text = preg_replace("/\[link\=(.+?)\](.*)\[\/link\]/", "<a href=\"$1\" class=\"profile-link\">$2</a>", $text);


                return $text;
            }
            public static function CompileMentions($stext)
            {
                $text = $stext;

                //Searching for mentions.
                preg_match_all("/@([A-Za-z0-9\-_]+)/", $text, $matches);
                //
                //print_r($matches[1]); =>
                //Array ( [0] => Admin,
                //       [1] => 7584847575 )
                //
                for ($i = 0; $i < count($matches[1]); $i++) {
                    if ($mentionUserId = UserAgent::GetUserId($matches[1][$i])) {
                        $mentionUserNickname = UserAgent::GetUserNick($mentionUserId);
                        $text = preg_replace("/(@$mentionUserNickname)/", "<a href=\"profile.php?uid=$mentionUserId\" class=\"mention mention-success\">$1</a>", $text);
                    } else {
                        $mentionUserNickname = $matches[1][$i];
                        $text = preg_replace("/(@$mentionUserNickname)/", "<span class=\"mention mention-fail\" title=\"Такого пользователя не существует.\">$1</a>", $text);
                    }
                }
                return $text;
            }
            public static function GetLanguagePacks(){
                $filesIn = scandir("./languages/");
                $filesListReturn = array();
                $s = ".";
                for ($i = 2; $i < count($filesIn); $i++){
                    $expl = explode($s, $filesIn[$i]);
                    if ($expl[1] == "php")
                        array_push($filesListReturn, $expl[0]);
                }
                return $filesListReturn;
            }
            public static function GetTemplatesPacks(){
                $filesIn = scandir("./site/templates/");
                $filesListReturn = array();
                foreach ($filesIn as $f){
                    if (is_dir("./site/templates/" . $f) && (!in_array($f, [".", ".."]))) array_push($filesListReturn, $f);
                }
                return $filesListReturn;
            }
            public static function GetReportReasons(){
                $reasons = file_get_contents("config/represes.sfc", FILE_USE_INCLUDE_PATH);
                return $reasons;
            }
            public static function SaveReportReasons($text){
                $reasons = file_put_contents("config/represes.sfc", $text, FILE_USE_INCLUDE_PATH);
                return $reasons;
            }
            public static function GetCensoredWords(){
                $censors = file_get_contents("config/censore.sfc", FILE_USE_INCLUDE_PATH);
                return $censors;
            }
            public static function SaveCensoredWords($text){
                $censored = file_put_contents("config/censore.sfc", $text, FILE_USE_INCLUDE_PATH);
                return $censored;
            }
            public static function GetSiteTime(){
                return time()-date("Z")+60*60*Engine::GetEngineInfo("srt");
            }
            public static function SaveAnalyticScript($text){
               return file_put_contents("config/analytic.js", $text, FILE_USE_INCLUDE_PATH);
            }
            public static function GetAnalyticScript(){
                return file_get_contents("config/analytic.js", FILE_USE_INCLUDE_PATH);
            }
            public static function ChatFilter($text){
                $stext = $text;
                $censored = self::GetCensoredWords();
                $censored = explode(",", $censored);
                foreach($censored as $word){
                    $stext = str_ireplace($word, "[цензура]", $stext);
                }
                return $stext;
            }
         }

        class ErrorManager{

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
                36 => "This IP address is registered"
            );

            static public function GenerateError($errorCode){

                ErrorManager::$lastError = $errorCode;

            }
            static public function GetError(){

                return self::$lastError;
            }
            static public function GetErrorCode($error){
                if (self::$lastError == 999) return False;
                return array_search($error, self::$errors);
            }

            public static function PretendToBeDied($lastText, $exception){
                $itisjoke = false;
                if (!isset($lastText)){
                    $lastText = "This is just a joke! Don't be boring, site at the maintenance.";
                    $itisjoke = true;
                }

                include_once __DIR__ . "/../error.php";
            }
        }

        class Uploader
        {
            public static function ExtractType($path){

                $tmp = explode(".", $path);
                return end($tmp);

            }
            public static function UploadFile($idUser, $file){
                if ($file['name'] == ''){
                    ErrorManager::GenerateError(28);
                    return ErrorManager::GetError();
                }

                if (!UserAgent::IsUserExist($idUser)){
                    ErrorManager::GenerateError(7);
                    return ErrorManager::GetError();
                }

                $types = Engine::GetEngineInfo("upf");
                $maxsize = Engine::GetEngineInfo("ups");

                if (!strstr($types, self::ExtractType($file['name']))){
                    ErrorManager::GenerateError(13);
                    return ErrorManager::GetError();
                }

                if ($file['size'] >= $maxsize){
                    ErrorManager::GenerateError(27);
                    return ErrorManager::GetError();
                }

                $images = array();
                $docs = array();
                $zips = array();
                $other = array();

                $types = explode(",", $types);
                for ($i = 0; $i < count($types); $i++){
                    if (in_array($types[$i], ['gif', 'png', 'bmp', 'tiff', 'tif', 'jpeg', 'jpg'])) array_push($images, $types[$i]);
                    elseif (in_array($types[$i], ['doc', 'txt', 'xls', 'ppt', 'pptx', 'docx'])) array_push($docs, $types[$i]);
                    elseif (in_array($types[$i], ['zip', 'rar', 'tar', 'gzip', '7z', 'gz'])) array_push($zips, $types[$i]);
                    else array_push($other, $types[$i]);
                }

                $uploadPath = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";
                $filePath = '';

                if (in_array(self::ExtractType($file['name']), $images)){ $uploadPath .= "images/"; $filePath = "uploads/images/"; }
                if (in_array(self::ExtractType($file['name']), $docs)){ $uploadPath .= "docs/"; $filePath = "uploads/docs/"; }
                if (in_array(self::ExtractType($file['name']), $zips)){ $uploadPath .= "zips/"; $filePath = "uploads/zips/"; }
                if (in_array(self::ExtractType($file['name']), $other)){ $uploadPath .= "others/"; $filePath = "uploads/others/"; }

                $newName = Engine::RandomGen() .  "." . self::ExtractType($file['name']);

                if(move_uploaded_file($file['tmp_name'], $uploadPath. $newName)){
                    $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                    if ($mysqli->errno){
                        ErrorManager::GenerateError(2);
                        return ErrorManager::GetError();
                    }

                    if ($stmt = $mysqli->prepare("INSERT INTO `tt_uploads` (`file_path` , `name` , `author`, `upload_date` ) VALUES (?,?,?,?)")){
                        $date = date("Y-m-d", time());
                        $stmt->bind_param("ssis", $filePath, $newName, $idUser, $date);
                        $stmt->execute();
                        if ($stmt->errno){
                            ErrorManager::GenerateError(9);
                            return ErrorManager::GetError();
                        }
                        return true;
                    }
                } else {
                    ErrorManager::GenerateError(12);
                    return ErrorManager::GetError();
                }
                return false;
            }
            public static function GetUploadList($idUser){
                $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                if ($mysqli->errno){
                    ErrorManager::GenerateError(2);
                    return ErrorManager::GetError();
                }

                if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_uploads` WHERE `author`=?")){
                    $stmt->bind_param("i", $idUser);
                    $stmt->execute();
                    if ($stmt->errno){
                        ErrorManager::GenerateError(9);
                        return ErrorManager::GetError();
                    } else {
                        $stmt->bind_result($id);
                        $res = array();
                        while ($stmt->fetch()) array_push($res, $id);
                        return $res;
                    }
                }
                return false;
            }
            public static function GetUploadedFilesList(int $page){
                $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                if ($mysqli->errno){
                    ErrorManager::GenerateError(2);
                    return ErrorManager::GetError();
                }

                $lowBorder = $page * 50 - 50;
                $highBorder = 50;

                if ($stmt = $mysqli->prepare("SELECT * FROM tt_uploads ORDER BY id DESC LIMIT $lowBorder,$highBorder")){
                    $stmt->execute();
                    $files = [];
                    $stmt->bind_result($id, $file_path, $upload_date, $name, $authorId);
                    while($stmt->fetch()){
                        array_push($files, ["id" => $id,
                                   "file_path" => $file_path,
                                   "upload_date" => $upload_date,
                                   "name" => $name,
                                   "author" => $authorId]);
                    }
                    return $files;
                }

            }
            public static function GetUploadedFilesListByAuthor(string $nickname, int $page){
                $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                if ($mysqli->errno){
                    ErrorManager::GenerateError(2);
                    return ErrorManager::GetError();
                }

                $lowBorder = $page * 50 - 50;
                $highBorder = 50;

                if ($stmt = $mysqli->prepare("SELECT * FROM tt_uploads 
                                                     WHERE author IN 
                                                     (SELECT id FROM tt_users WHERE nickname LIKE ?)
                                                     ORDER BY id DESC LIMIT $lowBorder,$highBorder")){
                    $stmt->bind_param("s", $nickname);
                    $stmt->execute();
                    $files = [];
                    $stmt->bind_result($id, $file_path, $upload_date, $name, $authorId);
                    while($stmt->fetch()) {
                        array_push($files, ["id" => $id,
                            "file_path" => $file_path,
                            "upload_date" => $upload_date,
                            "name" => $name,
                            "author" => $authorId]);

                    }
                    return $files;
                }
                return false;
            }
            public static function GetUploadedFilesListByReference(string $ref, int $page){
                $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                if ($mysqli->errno){
                    ErrorManager::GenerateError(2);
                    return ErrorManager::GetError();
                }

                $lowBorder = $page * 50 - 50;
                $highBorder = 50;

                if ($stmt = $mysqli->prepare("SELECT * FROM tt_uploads 
                                                     WHERE name LIKE ?
                                                     ORDER BY id DESC                                                       
                                                     LIMIT $lowBorder,$highBorder")){
                    $paramFormat = "%.$ref";
                    $stmt->bind_param("s", $paramFormat);
                    $stmt->execute();
                    $files = [];
                    $stmt->bind_result($id, $file_path, $upload_date, $name, $authorId);
                    while($stmt->fetch()) {
                        array_push($files, ["id" => $id,
                            "file_path" => $file_path,
                            "upload_date" => $upload_date,
                            "name" => $name,
                            "author" => $authorId]);

                    }
                    return $files;
                }
                return false;
            }
            public static function GetUploadInfo($fId, $param){
                $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                if ($mysqli->errno){
                    ErrorManager::GenerateError(2);
                    return ErrorManager::GetError();
                }

                if ($stmt = $mysqli->prepare("SELECT $param FROM `tt_uploads` WHERE `id`=?")){
                    $stmt->bind_param("i", $fId);
                    $stmt->execute();
                    if ($stmt->errno){
                        ErrorManager::GenerateError(9);
                        return ErrorManager::GetError();
                    } else {
                        $stmt->bind_result($result);
                        $stmt->fetch();
                        return $result;
                    }
                }
                $stmt->close();
                $mysqli->close();
                return false;
            }
            public static function DeleteFile($fId){
                if (!unlink($_SERVER["DOCUMENT_ROOT"] . "/" . self::GetUploadInfo($fId, "file_path") . self::GetUploadInfo($fId, "name"))) return false;
                $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                if ($mysqli->errno){
                    ErrorManager::GenerateError(2);
                    return ErrorManager::GetError();
                }

                if ($stmt = $mysqli->prepare("DELETE FROM `tt_uploads` WHERE `id`=?")){
                    $stmt->bind_param("i", $fId);
                    $stmt->execute();
                    if ($stmt->errno){
                        ErrorManager::GenerateError(9);
                        return ErrorManager::GetError();
                    } else return true;
                }
                $stmt->close();
                $mysqli->close();
                return false;
            }
            public static function DeleteFilesOfUser(int $userId){
                $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                if ($mysqli->errno){
                    ErrorManager::GenerateError(2);
                    return ErrorManager::GetError();
                }

                if ($stmt = $mysqli->prepare("SELECT file_path, name FROM tt_uploads WHERE `authorId` = ?")){
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    if ($stmt->affected_rows == 0)
                        exit;
                    $stmt->bind_result($path, $name);
                    while ($stmt->fetch()){
                        unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $path . "/" . $name);
                    }
                }
                $stmt = null;

                if ($stmt = $mysqli->prepare("DELETE FROM ttavern.tt_uploads WHERE id IN (SELECT id FROM tt_uploads WHERE authorId=?)")){
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    return $stmt->affected_rows;
                }
                return false;
            }

        }

        class LanguageManager{
            private static $languageArray = [];
            /**
             * Include language file to project.
             *
             * @return mixed
             */
            public static function load(){
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
            public static function GetTranslation(string $path, ...$vars){
                if (isset(self::$languageArray[$path]) && !is_array(self::$languageArray[$path]))
                    return self::$languageArray[$path];

                $exploded = explode(".", $path);

                if (end($exploded) == "")
                    return $path;

                $think = null;
                for ($i = 0; $i < count($exploded); $i++){
                    if (empty($think)){
                        //If $think is empty set it into var.
                        $think = self::$languageArray[$exploded[$i]];
                    } else {
                        if (is_array($think)){
                            $think = $think[$exploded[$i]];
                        } else {
                            return $think;
                        }
                    }
                }
                if (!empty($think)) {
                    $time = 0;
                    foreach($vars as $var){
                        $time++;
                        $param = "{" . $time . "}";
                        $think = str_ireplace($param, $var, $think);
                    }
                    return $think;
                }
                else
                    return $path;
            }

        }

        class Mailer{
            public static function SendMail($text, $sendTo, $subject){
                if ($text == "") return false;
                require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/mailer/autoload.php";

                // Create the Transport
                $transport = (new \Swift_SmtpTransport(Engine::GetEngineInfo("ecp") . "://" . Engine::GetEngineInfo("eh"), Engine::GetEngineInfo("ept")))
                    ->setUsername(Engine::GetEngineInfo("el"))
                    ->setPassword(Engine::GetEngineInfo("ep"))
                ;

                // Create the Mailer using your created Transport
                $mailer = new \Swift_Mailer($transport);

                // Create a message
                $message = (new \Swift_Message($subject))
                    ->setFrom([Engine::GetEngineInfo("el") => 'Администрация "' . Engine::GetEngineInfo("sn") . '"'])
                    ->setTo([$sendTo])
                    ->setBody($text, "text/html")
                ;
                $message->addPart(strip_tags($text), "text/plain");

                // Send the message
                $result = $mailer->send($message);
                return $result;
            }
        }

        class DataKeeper
        {
            private static function connect(){
                $dsn = "mysql:dbname=" . Engine::GetDBInfo(3) . ";host=" . Engine::GetDBInfo(0) . ";";
                $dblogin = Engine::GetDBInfo(1);
                $dbpass = Engine::GetDBInfo(2);
                try {
                    $pdo = new \PDO($dsn, $dblogin, $dbpass);
                    return $pdo;
                } catch (\PDOException $pdoExcp){
                    ErrorManager::GenerateError(2);
                    ErrorManager::PretendToBeDied(ErrorManager::GetErrorCode(2), $pdoExcp);
                }
                return false;
            }

            public static function getMax($table, $column){
                $pdo = self::connect();
                $preparedQuery = $pdo->prepare("SELECT MAX(`$column`) FROM $table");
                $preparedQuery->execute();
                $result = $preparedQuery->fetch();
                return $result[0];
            }

            public static function isExistsIn($table, $column, $content){
                $pdo = self::connect();
                $query = "SELECT count(*) FROM `$table` WHERE `$column`=?";
                $preparedQuery = $pdo->prepare($query);
                $preparedQuery->execute([$content]);
                $result = $preparedQuery->fetch($pdo::FETCH_ASSOC);
                if ($result["count(*)"] > 0){
                    return true;
                }
                else {
                    return false;
                }

            }

            public static function _isExistsIn($table, array $content){
                $values = [];
                $keys = "";
                foreach ($content as $key => $value){
                    $keys .= "`$key`=? AND";
                    $values[] = $value;
                }
                $keys = rtrim($keys, "AND");
                $pdo = self::connect();
                $query = "SELECT count(*) FROM `$table` WHERE $keys";
                $prepared = $pdo->prepare($query);
                $prepared->execute($values);
                $result = $prepared->fetch($pdo::FETCH_ASSOC);
                if ($result["count(*)"] === "0") return false;
                else return true;
            }

            public static function InsertTo($table, array $varsArr){
                $pdo = self::connect();
                $keys = "";
                $values = "";
                $varsArrToSend = [];
                foreach ($varsArr as $key => $value){
                    $keys .= "`$key`,";
                    $values .= "?,";
                    $varsArrToSend[] = $value;
                }
                $keys = rtrim($keys, ",");
                $values = rtrim($values, ",");
                $query = "INSERT INTO $table ($keys) VALUE ($values)";
                $preparedQuery = $pdo->prepare($query);
                $execute = $preparedQuery->execute($varsArrToSend);
                if ($execute){
                    return $pdo->lastInsertId();
                } else {
                    return false;
                }
            }

            public static function Update($table, array $varsArr, array $whereArr){
                $pdo = self::connect();
                $keys = "";
                $whereKeys = "";
                $varsArrToSend = [];
                foreach ($varsArr as $key => $value){
                    $keys .= "`$key`=?,";
                    $varsArrToSend[] = $value;
                }
                foreach ($whereArr as $whereKey => $whereValue){
                    $whereKeys .= "`$whereKey`=? AND";
                    $varsArrToSend[] = $whereValue;
                }

                $keys = rtrim($keys, ", ");
                $whereKeys = rtrim($whereKeys, "AND");
                $query = "UPDATE $table SET $keys WHERE $whereKeys";
                $preparedQuery = $pdo->prepare($query);
                if ($preparedQuery->execute($varsArrToSend))
                    return true;
                else{
                    ErrorManager::GenerateError(33);
                    ErrorManager::PretendToBeDied("Cannot execute UPDATE query: " . $preparedQuery->errorInfo()[2], new \PDOException($preparedQuery->errorInfo()[2]));
                    return false;
                }
            }

            public static function Delete($table, array $whereArr){
                $pdo = self::connect();
                $whereStr = "";
                $varsArrToSend = [];
                foreach ($whereArr as $key => $value){
                    $whereStr .= "`$key`=? AND";
                    $varsArrToSend[] = $value;
                }
                $whereStr = rtrim($whereStr, "AND");
                $query = "DELETE FROM `$table` WHERE $whereStr";
                $preparedQuery = $pdo->prepare($query);
                return $preparedQuery->execute($varsArrToSend);
            }

            public static function Get($table, array $whatArr, array $whereArr = null){
                $pdo = self::connect();
                $varsArrToSend = [];
                if ($whereArr != null) {
                    $whereStr = "";
                    foreach ($whereArr as $key => $value) {
                        $whereStr .= "`$key`=?,";
                        $varsArrToSend[] = $value;
                    }
                }
                $whatStr = "";
                foreach ($whatArr as $key => $value){
                    if ($value != "*")
                        $whatStr .= "`$value`,";
                    else {
                        $whatStr .= "$value,";
                        break;
                    }
                }
                $whatStr = rtrim($whatStr, ",");
                $whereStr = rtrim(@$whereStr, ",");

                if ($whereArr != null) {
                    $query = "SELECT $whatStr FROM `$table` WHERE $whereStr";
                    $preparedQuery = $pdo->prepare($query);
                    $preparedQuery->execute($varsArrToSend);
                } else {
                    $query = "SELECT $whatStr FROM `$table`";
                    $preparedQuery = $pdo->prepare($query);
                    $preparedQuery->execute();
                }
                return $preparedQuery->fetchAll($pdo::FETCH_ASSOC);
            }

            public static function MakeQuery($query, array $whereArr = null){
                $pdo = self::connect();

                $preparedQuery = $pdo->prepare($query);
                if ($whereArr !== null)
                    $result = $preparedQuery->execute($whereArr);
                else
                    $result = $preparedQuery->execute();
                if (!$result){
                    echo $query;
                    ErrorManager::GenerateError(33);
                    ErrorManager::PretendToBeDied("Cannot make special SQL query: [" . $preparedQuery->errorInfo()[0] . "] " . $preparedQuery->errorInfo()[2], new \PDOException("Cannot make special SQL query."));
                    echo 1;
                    return false;
                }
                return $preparedQuery->fetch($pdo::FETCH_ASSOC);
            }
        }

        class PluginManager{

        }
    }


?>