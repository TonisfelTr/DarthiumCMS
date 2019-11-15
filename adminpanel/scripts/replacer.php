<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
if (!$user->UserGroup()->getPermission("change_engine_settings")){
    header("Location: ../../adminpanel.php?res=1");
    exit;
}
else {
        if (isset($_REQUEST["save_cfg_button"])) {
            if ($_REQUEST["emailconnecttype"] == 1) $type = "tls"; else $type = "ssl";
            if ($_REQUEST["multiacc"] == "on") $multiAcc = 1; else $multiAcc = 0;
            $lookStatistic = ($user->UserGroup()->getPermission("look_statistic")) ? true : false;
            if (!$lookStatistic){
                $metricType = \Engine\Engine::GetEngineInfo("smt");
                $metricStatus = \Engine\Engine::GetEngineInfo("sms");
            } else {
                $metricStatus = (isset($_REQUEST["metric-lever-btn"])) ? true : false;
                $metricType = $_REQUEST["metric-service-select"];
            }
            if (\Engine\Engine::SettingsSave($_REQUEST["domain"], $_REQUEST["sitename"], $_REQUEST["sitetagline"],
                $_REQUEST["sitestatus"], $_REQUEST["sitesubscribe"],
                $_REQUEST["sitehashtags"], $_REQUEST["sitelang"], $_REQUEST["sitetemplate"], $_REQUEST["siteregiontime"],
                $_REQUEST["emaillogin"], $_REQUEST["emailpassword"], $_REQUEST["emailhost"], $_REQUEST["emailport"], $type,
                $_REQUEST["needactivate"], $multiAcc, $_REQUEST["standartgroup"],
                $_REQUEST["avatarmaxwidth"], $_REQUEST["avatarmaxheight"], $_REQUEST["maxfilesize"], $_REQUEST["uploadformats"], (isset($_REQUEST["guest_see_profiles"])) ? 1 : 0,
                $metricStatus, $metricType)
            ) {
                if (\Engine\Engine::SaveCensoredWords($_POST["chat-filter-words"]))
                if (\Engine\Engine::SaveReportReasons($_REQUEST["reports-reasons"])) {
                    $engineSettings = true;
                    if (file_put_contents("../.todolist", $_REQUEST["todo_texter"], FILE_USE_INCLUDE_PATH)) {
                        $engineSettings = true;
                        if (!\Engine\Engine::SaveAnalyticScript($_REQUEST["metric-script-text"]))
                            $engineSettings = false;
                    } else $engineSettings = False;
                } else  $engineSettings = False;
            }
        }
        if ($engineSettings){
        header("Location: ../../adminpanel.php?p=settings&res=2s");
        exit;
    }
    else {
        header("Location: ../../adminpanel.php?p=settings&res=2n");
        exit;
    }
}

header("Location: ../../adminpanel.php?p=forbidden");
exit;

?>