<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
if (!$user->UserGroup()->getPermission("change_engine_settings")) {
    header("Location: ../../adminpanel.php?res=1");
    exit;
} else {
    if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
        header("Location: banned.php");
        exit;
    }

    if (isset($_POST["save_cfg_button"])) {
        if ($_POST["emailconnecttype"] == "tls")
            $type = "tls";
        else
            $type = "ssl";

        if (\Engine\Engine::GetEngineInfo("ecp") != $_POST["emailconnecttype"]){
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.site_mail_connection_type_log")
                . "[". (\Engine\Engine::GetEngineInfo("ecp") ?: "(пусто)") . " -> " . $_POST["emailconnecttype"] . "]");
        }

        // Set multiaccount setting. If it is 1 as string or 0 as string...
        $multiAcc = $_POST["multiaccount"] == "1" ? "y" : "n";
        if ($multiAcc != \Engine\Engine::GetEngineInfo("map")) {
            if ($_POST["multiaccount"] == "1") {
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.site_denied_multiacc_log"));
                $multiAcc = "y";
            } else {
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.site_allowed_multiacc_log"));
                $multiAcc = "n";
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
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.domain_site_log") . "[" . (\Engine\Engine::GetEngineInfo("dm") ?: "пусто") . " -> " . $_POST["domain"] . "]");
        if ($_POST["sitename"] != \Engine\Engine::GetEngineInfo("sn"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_name_log") ."[" . (\Engine\Engine::GetEngineInfo("sn") ?: "пусто"). " -> " . $_POST["sitename"] . "]");
        if ($_POST["sitetagline"] != \Engine\Engine::GetEngineInfo("stl"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_tagline_log") . "[" . (\Engine\Engine::GetEngineInfo("stl") ?: "пусто"). " -> " . $_POST["sitetagline"] . "]");
        if ($_POST["sitestatus"] != \Engine\Engine::GetEngineInfo("ss")) {
            $siteStatusFrom = (\Engine\Engine::GetEngineInfo("ss") == 0) ? \Engine\LanguageManager::GetTranslation("off") : \Engine\LanguageManager::GetTranslation("on");
            $siteStatusTo = ($_POST["sitestatus"] == 0) ? \Engine\LanguageManager::GetTranslation("off") : \Engine\LanguageManager::GetTranslation("on");
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_status_log") ."[$siteStatusFrom -> $siteStatusTo]");
        }
        if ($_POST["sitesubscribe"] != \Engine\Engine::GetEngineInfo("ssc"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_description_log") ."[" . (\Engine\Engine::GetEngineInfo("ssc") ?: "пусто"). " -> " . $_POST["sitesubscribe"] . "]");
        if ($_POST["sitehashtags"] != \Engine\Engine::GetEngineInfo("sh"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_hashtags_log") ."[" . (\Engine\Engine::GetEngineInfo("sh") ?: "пусто"). " -> " . $_POST["sitehashtags"] . "]");
        if ($_POST["sitelang"] != \Engine\Engine::GetEngineInfo("sl"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_lang_log") ."[" . (\Engine\Engine::GetEngineInfo("sl") ?: "пусто"). " -> " . $_POST["sitelang"] . "]");
        if ($_POST["sitetemplate"] != \Engine\Engine::GetEngineInfo("stp"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_template_log") ."[" . (\Engine\Engine::GetEngineInfo("stp") ?: "пусто"). " -> " . $_POST["sitetemplate"] . "]");
        if ($_POST["siteregiontime"] != \Engine\Engine::GetEngineInfo("srt"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_timezone_log") ."[" . (\Engine\Engine::GetEngineInfo("srt") ?: "пусто"). " -> " . $_POST["siteregiontime"] . "]");
        if ($_POST["emaillogin"] != \Engine\Engine::GetEngineInfo("el"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.site_mail_login_log") ."[" . (\Engine\Engine::GetEngineInfo("el") ?: "пусто") . " -> " . $_POST["emaillogin"] . "]");
        if ($_POST["emailpassword"] != \Engine\Engine::GetEngineInfo("ep"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.site_mail_password_log"));
        if ($_POST["emailhost"] != \Engine\Engine::GetEngineInfo("eh"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.site_mail_address_log") ."[" . (\Engine\Engine::GetEngineInfo("eh") ?: "пусто") . " -> " . $_POST["emailhost"] . "]");
        if ($_POST["emailport"] != \Engine\Engine::GetEngineInfo("ept"))
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.site_mail_port_log") ."[" . (\Engine\Engine::GetEngineInfo("ept") ?: "пусто") . " -> " . $_POST["emailport"] . "]");
        if ($_POST["needactivate"] != \Engine\Engine::GetEngineInfo("na")) {
            $needActivation = (\Engine\Engine::GetEngineInfo("na") == 0) ? \Engine\LanguageManager::GetTranslation("off") : \Engine\LanguageManager::GetTranslation("on");
            $needActivationTo = ($_POST["needactivate"] == 0) ? \Engine\LanguageManager::GetTranslation("off") : \Engine\LanguageManager::GetTranslation("on");
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.site_need_user_activation_log") . "[$needActivation -> $needActivationTo]");
        }
        if ($_POST["standartgroup"] != \Engine\Engine::GetEngineInfo("sg")) {
            $from = \Users\GroupAgent::GetGroupNameById(\Engine\Engine::GetEngineInfo("sg"));
            $to = \Users\GroupAgent::GetGroupNameById($_POST["standartgroup"]);
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.site_group_for_newbies_log") ." [$from -> $to]");
        }
        if (\Engine\Engine::GetEngineInfo("aw") != $_POST["avatarmaxwidth"]){
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.site_avatar_width_log") ."[" .
                (\Engine\Engine::GetEngineInfo("aw") ?: "пусто") . " -> " . $_POST["avatarmaxwidth"] . "]");
        }
        if (\Engine\Engine::GetEngineInfo("ah") != $_POST["avatarmaxheight"]){
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.site_avatar_height_log") ."[" .
                (\Engine\Engine::GetEngineInfo("ah") ?: "пусто") . " -> " . $_POST["avatarmaxheight"] . "]");
        }
        if (\Engine\Engine::GetEngineInfo("ups") != $_POST["maxfilesize"]){
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.site_max_file_size_log") ."[" .
                (\Engine\Engine::GetEngineInfo("ups") ?: "пусто") . " -> " . $_POST["maxfilesize"] . "]");
        }
        if (\Engine\Engine::GetEngineInfo("upf") != $_POST["uploadformats"]){
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.site_allowed_file_for_upload_log") ."[" .
                (\Engine\Engine::GetEngineInfo("upf") ?: "пусто") . " -> " . $_POST["uploadformats"] . "]");
        }
        $guestSeeProfileCond = (isset($_POST["guest_see_profiles"])) ? 1 : 0;
        if (\Engine\Engine::GetEngineInfo("gsp") != $guestSeeProfileCond){
            $guestSeeProfilePerm = (isset($_POST["guest_see_profiles"])) ? \Engine\LanguageManager::GetTranslation("on") : \Engine\LanguageManager::GetTranslation("off");
            $guestSeeProfileNow = (\Engine\Engine::GetEngineInfo("gsp") == 1) ? \Engine\LanguageManager::GetTranslation("on") : \Engine\LanguageManager::GetTranslation("off");
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.site_allow_guest_see_users") . "[" .
                $guestSeeProfileNow . " -> " . $guestSeeProfilePerm . "]");
        }
        $multiVoteRepCond = (isset($_POST["multivote_rep"])) ? 1 : 0;
        if (\Engine\Engine::GetEngineInfo("vmr") != $multiVoteRepCond){
            $multiVoteRepNow = (\Engine\Engine::GetEngineInfo("vmr") == 1) ? \Engine\LanguageManager::GetTranslation("on") : \Engine\LanguageManager::GetTranslation("off");
            $multiVoteRepPerm = (isset($_POST["multivote_rep"])) ? \Engine\LanguageManager::GetTranslation("on") : \Engine\LanguageManager::GetTranslation("off");
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.site_allowed_multivote_rep_log") .
            "[$multiVoteRepNow -> $multiVoteRepPerm]");
        }

        $metricStatusPass = !is_null($_POST["metric-lever-btn"]) ? 1 : 0;
        if (\Engine\Engine::GetEngineInfo("sms") != $metricStatusPass){
            $metricStatusParam = (\Engine\Engine::GetEngineInfo("sms") == 1)
                ? \Engine\LanguageManager::GetTranslation("on")
                : \Engine\LanguageManager::GetTranslation("off");
            $metricStatusNow = ($metricStatusPass == 1)
                ? \Engine\LanguageManager::GetTranslation("on")
                : \Engine\LanguageManager::GetTranslation("off");
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.statistic_panel.site_metric_log") . "[" . $metricStatusParam . " -> " . $metricStatusNow . "]");
        }

        if (\Engine\Engine::GetEngineInfo("cfrf") != $_POST["chat-filter-replace-container"]) {
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.replace_expression_changed_log") . "[\"" . \Engine\Engine::GetEngineInfo("cfrf") . "\" -> \"{$_POST["chat-filter-replace-container"]}\"]");
        }

        if (\Engine\Engine::GetEngineInfo("cfm") != $_POST["chat-filter-mechanism"]) {
            $oldMechanism = \Engine\Engine::GetEngineInfo("cfm");
            switch ($oldMechanism) {
                case 0:
                    $oldMechanism = \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.replace_mechanism_type.standard");
                    break;
                case 1:
                    $oldMechanism = \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.replace_mechanism_type.symbols");
                    break;
                case 2:
                    $oldMechanism = \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.replace_mechanism_type.sentence");
                    break;
            }
            $newMechanism = $_POST["chat-filter-mechanism"];
            switch ($newMechanism) {
                case 0:
                    $newMechanism = \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.replace_mechanism_type.standard");
                    break;
                case 1:
                    $newMechanism = \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.replace_mechanism_type.symbols");
                    break;
                case 2:
                    $newMechanism = \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.replace_mechanism_type.sentence");
                    break;
            }
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.replace_mechanism_changed_log") . "[$oldMechanism -> $newMechanism]");
        }

        if (\Engine\Engine::SettingsSave($_POST["domain"], $_POST["sitename"], $_POST["sitetagline"],
            $_POST["sitestatus"], $_POST["sitesubscribe"],
            $_POST["sitehashtags"], $_POST["sitelang"], $_POST["sitetemplate"], $_POST["siteregiontime"],
            $_POST["emaillogin"], $_POST["emailpassword"], $_POST["emailhost"], $_POST["emailport"], $type,
            $_POST["needactivate"], $multiAcc, $_POST["standartgroup"],
            $_POST["chat-filter-mechanism"], $_POST["chat-filter-replace-container"], $_POST["avatarmaxwidth"], $_POST["avatarmaxheight"], $_POST["maxfilesize"], $_POST["uploadformats"], (isset($_POST["guest_see_profiles"])) ? 1 : 0, (isset($_POST["multivote_rep"])) ? 1 : 0,
            $metricStatusPass)
        ) {
            if (\Engine\Engine::GetCensoredWords() != $_POST["chat-filter-words"]){
                \Engine\Engine::SaveCensoredWords($_POST["chat-filter-words"]);
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.site_filter_change_log"));
            }
            if (\Engine\Engine::GetReportReasons() != $_POST["reports-reasons"]) {
                \Engine\Engine::SaveReportReasons($_POST["reports-reasons"]);
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.site_reasons_report_change_log"));
            }
            if (\Engine\Engine::GetAnalyticScript() != $_POST["metric-script-text"]) {
                \Engine\Engine::SaveAnalyticScript($_POST["metric-script-text"]);
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("settings_panel.statistic_panel.site_metric_text_log"));
            }
                $engineSettings = true;
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


header("Location: ../../adminpanel.php?p=forbidden");
exit;

