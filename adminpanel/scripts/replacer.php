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
        if ($_POST["emailconnecttype"] == 1) $type = "tls"; else $type = "ssl";
        if ($_POST["multiacc"] == "on") $multiAcc = 1; else $multiAcc = 0;
        $lookStatistic = ($user->UserGroup()->getPermission("look_statistic")) ? true : false;
        if (!$lookStatistic) {
            $metricType = \Engine\Engine::GetEngineInfo("smt");
            $metricStatus = \Engine\Engine::GetEngineInfo("sms");
        } else {
            $metricStatus = (isset($_POST["metric-lever-btn"])) ? true : false;
            $metricType = $_POST["metric-service-select"];
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

