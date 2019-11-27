<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
if (!$user->UserGroup()->getPermission("change_engine_settings")){
    header("Location: ../../adminpanel.php?res=1");
    exit;
}
else {
    if (isset($_POST["save_cfg_button"])) {
        if ($_POST["emailconnecttype"] == 1)
            $type = "tls";
        else
            $type = "ssl";

        if (\Engine\Engine::GetEngineInfo("ecp") != $_POST["emailconnecttype"]){
            \Guards\Logger::LogAction($user->getId(), " изменил(а) протокол соединения [".
                \Engine\Engine::GetEngineInfo("ecp") . " -> " . $_POST["emailconnecttype"] . "]");
        }

        if (\Engine\Engine::GetEngineInfo("map") != $_POST["multiacc"]){
            if ($_POST["multiacc"] == "1") {
                \Guards\Logger::LogAction($user->getId(), " запретил(а) мультиаккаунт.");
                $multiAcc = 1;
            } else {
                \Guards\Logger::LogAction($user->getId(), " разрешил(а) мультиаккаунт.");
                $multiAcc = 0;
            }
        }

        $metricType = \Engine\Engine::GetEngineInfo("smt");
        $metricStatus = \Engine\Engine::GetEngineInfo("sms");

        /**Logs for watching.
         *
         * case "na": return self::$NeedActivate;
        case "map": return self::$MultiAccPermited;
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
        case "stp": return self::$SiteTemplate;
        case "smt": return self::$SiteMetricType;
        case "sms": return self::$SiteMetricStatus;
         */
        if ($_POST["domain"] != \Engine\Engine::GetEngineInfo("dm"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) домен сайта [" . \Engine\Engine::GetEngineInfo("dm") . " -> " . $_POST["domain"] . "]");
        if ($_POST["sitename"] != \Engine\Engine::GetEngineInfo("sn"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) название сайта [" . \Engine\Engine::GetEngineInfo("sn") . " -> " . $_POST["sitename"] . "]");
        if ($_POST["sitetagline"] != \Engine\Engine::GetEngineInfo("stl"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) хештеги сайта [" . \Engine\Engine::GetEngineInfo("stl") . " -> " . $_POST["sitetagline"] . "]");
        if ($_POST["sitestatus"] != \Engine\Engine::GetEngineInfo("ss")) {
            $siteStatusFrom = (\Engine\Engine::GetEngineInfo("ss") == 0) ? "выключен" : "включен";
            $siteStatusTo = ($_POST["sitestatus"] == 0) ? "выключен" : "включен";
            \Guards\Logger::LogAction($user->getId(), " изменил(а) статус сайта [$siteStatusFrom -> $siteStatusTo]");
        }
        if ($_POST["sitesubscribe"] != \Engine\Engine::GetEngineInfo("ssc"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) описание сайта [" . \Engine\Engine::GetEngineInfo("ssc") . " -> " . $_POST["sitesubscribe"] . "]");
        if ($_POST["sitehashtags"] != \Engine\Engine::GetEngineInfo("sh"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) хештеги сайта [" . \Engine\Engine::GetEngineInfo("sh") . " -> " . $_POST["sitehashtags"] . "]");
        if ($_POST["sitelang"] != \Engine\Engine::GetEngineInfo("sl"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) язык сайта [" . \Engine\Engine::GetEngineInfo("sl") . " -> " . $_POST["sitelang"] . "]");
        if ($_POST["sitetemplate"] != \Engine\Engine::GetEngineInfo("stp"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) шаблон сайта [" . \Engine\Engine::GetEngineInfo("stp") . " -> " . $_POST["sitetemplate"] . "]");
        if ($_POST["siteregiontime"] != \Engine\Engine::GetEngineInfo("srt"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) часовой пояс сайта [" . \Engine\Engine::GetEngineInfo("srt") . " -> " . $_POST["siteregiontime"] . "]");
        if ($_POST["emaillogin"] != \Engine\Engine::GetEngineInfo("el"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) логин для бота рассылки сайта [" . \Engine\Engine::GetEngineInfo("el") . " -> " . $_POST["emaillogin"] . "]");
        if ($_POST["emailpassword"] != \Engine\Engine::GetEngineInfo("ep"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) пароль для бота рассылки сайта.");
        if ($_POST["emailhost"] != \Engine\Engine::GetEngineInfo("eh"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) адрес сервера для бота рассылки сайта [" . \Engine\Engine::GetEngineInfo("eh") . " -> " . $_POST["emailhost"] . "]");
        if ($_POST["emailport"] != \Engine\Engine::GetEngineInfo("ept"))
            \Guards\Logger::LogAction($user->getId(), " изменил(а) порт для бота рассылки сайта [" . \Engine\Engine::GetEngineInfo("ept") . " -> " . $_POST["emailport"] . "]");
        if ($_POST["needactivate"] != \Engine\Engine::GetEngineInfo("na")) {
            $needActivation = (\Engine\Engine::GetEngineInfo("na") == 0) ? "выключено" : "включено";
            $needActivationTo = ($_POST["needactivate"] == 0) ? "выключено" : "включено";
            \Guards\Logger::LogAction($user->getId(), " изменил(а) порт для бота рассылки сайта [$needActivation -> $needActivationTo]");
        }
        if ($_POST["standartgroup"] != \Engine\Engine::GetEngineInfo("sg")) {
            $from = \Users\GroupAgent::GetGroupNameById(\Engine\Engine::GetEngineInfo("sg"));
            $to = \Users\GroupAgent::GetGroupNameById($_POST["standartgroup"]);
            \Guards\Logger::LogAction($user->getId(), " изменил(а) группу для записи новичков [$from -> $to]");
        }
        if (\Engine\Engine::GetEngineInfo("aw") != $_POST["avatarmaxwidth"]){
            \Guards\Logger::LogAction($user->getId(), " изменил(а) ширину аватарки [" .
                \Engine\Engine::GetEngineInfo("aw") . " -> " . $_POST["avatarmaxwidth"] . "]");
        }
        if (\Engine\Engine::GetEngineInfo("ah") != $_POST["avatarmaxheight"]){
            \Guards\Logger::LogAction($user->getId(), " изменил(а) высоту аватарки [" .
                \Engine\Engine::GetEngineInfo("ah") . " -> " . $_POST["avatarmaxheight"] . "]");
        }
        if (\Engine\Engine::GetEngineInfo("ups") != $_POST["maxfilesize"]){
            \Guards\Logger::LogAction($user->getId(), " изменил(а) максимальный размер загружаемого файла [" .
                \Engine\Engine::GetEngineInfo("ups") . " -> " . $_POST["maxfilesize"] . "]");
        }
        if (\Engine\Engine::GetEngineInfo("upf") != $_POST["uploadformats"]){
            \Guards\Logger::LogAction($user->getId(), " изменил(а) разрешённые к загрузке файлы [" .
                \Engine\Engine::GetEngineInfo("upf") . " -> " . $_POST["uploadformats"] . "]");
        }
        $guestSeeProfileNow = (isset($_POST["guest_see_profiles"])) ? 1 : 0;
        if (\Engine\Engine::GetEngineInfo("gsp") != $guestSeeProfileNow){
            $guestSeeProfilePerm = (isset($_POST["guest_see_profiles"])) ? "включено" : "выключено";
            $guestSeeProfileNow = (\Engine\Engine::GetEngineInfo("gsp") == 1) ? "включено" : "выключено";
            \Guards\Logger::LogAction($user->getId(), " изменил(а) видимость профилей пользователей [" .
                $guestSeeProfileNow . " -> " . $guestSeeProfilePerm . "]");
        }

        $metricStatusPass = (isset($_POST["metric-lever-btn"])) ? 1 : 0;
        if (\Engine\Engine::GetEngineInfo("smt") != $metricStatusPass){
            $metricStatus = $metricStatusPass;
        }

        if (\Engine\Engine::SettingsSave($_POST["domain"], $_POST["sitename"], $_POST["sitetagline"],
            $_POST["sitestatus"], $_POST["sitesubscribe"],
            $_POST["sitehashtags"], $_POST["sitelang"], $_POST["sitetemplate"], $_POST["siteregiontime"],
            $_POST["emaillogin"], $_POST["emailpassword"], $_POST["emailhost"], $_POST["emailport"], $type,
            $_POST["needactivate"], $multiAcc, $_POST["standartgroup"],
            $_POST["avatarmaxwidth"], $_POST["avatarmaxheight"], $_POST["maxfilesize"], $_POST["uploadformats"], (isset($_POST["guest_see_profiles"])) ? 1 : 0,
            $metricStatus, $metricType)
        ) {
            if (\Engine\Engine::SaveCensoredWords($_POST["chat-filter-words"])) {
                if (\Engine\Engine::SaveReportReasons($_POST["reports-reasons"])) {
                    $engineSettings = true;
                    if (!\Engine\Engine::SaveAnalyticScript($_POST["metric-script-text"]))
                        $engineSettings = false;
                } else $engineSettings = False;
            } else  $engineSettings = False;
        }
    }
    if ($engineSettings) {
        header("Location: ../../adminpanel.php?p=settings&res=2s");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=settings&res=2n");
        exit;
    }
}

header("Location: ../../adminpanel.php?p=forbidden");
exit;

