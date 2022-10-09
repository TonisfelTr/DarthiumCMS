<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/1/16
 * Time: 5:50 AM
 */
include "engine/classes/engine/Engine.php";
define("TT_AP", true);
define("ADMINPANEL_TEMPLATES", "adminpanel/templates/Default/");
define("ADMINPANEL_ADDONS", "addons/");
ob_start();
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) {
    $user = new \Users\Models\User($_SESSION["uid"]);
} else {
    header("Location: profile.php");
    exit;
}
//Проверка на наличие доступа в АП.
if (!isset($user) || !$user->UserGroup()->getPermission("enterpanel")) {
    \Guards\Logger::addAccessLog("I tried visit adminpanel but I do not have the permission for that.");
    header("Location: index.php?page=errors/forbidden");
    exit;
}
if (isset($user)) {
    if ($user->isBanned()) {
        header("Location: banned.php");
        exit;
    }
}
if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)) {
    header("Location: banned.php");
    exit;
}
if (!function_exists("getBrick")) {
    function getBrick() {
        $e = ob_get_contents();
        ob_clean();
        return $e;
    }
}

if (!function_exists("str_replace_once")) {
    function str_replace_once($search, $replace, $text) {
        $pos = strpos($text, $search);
        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }
}

function getOption(string $flag) {
    if (isset($_GET[$flag]) && !empty($_GET[$flag])) {
        return $_GET[$flag];
    }

    return false;
}

include_once ADMINPANEL_TEMPLATES . "header.html";
$mainHeader = getBrick();
$mainHeader = str_replace_once("{ADMINPANEL_TITLE}", \Engine\LanguageManager::GetTranslation("header") . " - " . \Engine\Engine::GetEngineInfo("sn"), $mainHeader);

include_once ADMINPANEL_TEMPLATES . "main.html";
$obMain = getBrick();
$obMain = str_replace_once("{ADMINPANEL_HEADER}", \Engine\LanguageManager::GetTranslation("header"), $obMain);
$obMain = str_replace_once("{ADMINPANEL_HEADER_INFO}", \Engine\LanguageManager::GetTranslation("header_info"), $obMain);

include_once ADMINPANEL_TEMPLATES . "subpanels/uploader.phtml";
$mainUploader = getBrick();

if (($rc = \Guards\ReportAgent::GetUnreadedReportsCount()) > 0) {
    $counter = "<span class=\"adminpanel-reports-inc\"><span class=\"glyphicons glyphicons-bell\"></span>$rc</span>";
}

include_once ADMINPANEL_TEMPLATES . "navbar.html";
$mainNavbar = getBrick();
$mainNavbar = str_replace_once("{ADMINPANEL_NAVIGATION_TEXT}", \Engine\LanguageManager::GetTranslation("navigation"), $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_BTN_HOME_ACTIVE}", !getOption("p") && !getOption("plp") ? "active" : "", $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_HOME_BTN_TEXT}", \Engine\LanguageManager::GetTranslation("home"), $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_BTN_SETTINGS_ACTIVE}", getOption("p") == "settings" ? "active" : "", $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_SETTINGS_BTN_TEXT}", \Engine\LanguageManager::GetTranslation("settings"), $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_BTN_REPORTS_ACTIVE}", getOption("p") == "reports" ? "active" : "", $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_REPORTS_BTN_TEXT}", \Engine\LanguageManager::GetTranslation("reports"), $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_REPORTS_COUNTER_BLOCK}", $counter ?? "", $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_BTN_LOGS_ACTIVE}", getOption("p") == "logs" ? "active" : "", $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_LOGS_BTN_TEXT}", \Engine\LanguageManager::GetTranslation("logs"), $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_RIGHT_TO_SITE_TEXT}", \Engine\LanguageManager::GetTranslation("to_site_home"), $mainNavbar);
$mainNavbar = str_replace_once("{ADMINPANEL_NAV_RIGHT_NICKNAME}", $user->getNickname(), $mainNavbar);

include_once ADMINPANEL_TEMPLATES . "errors.phtml";
$mainErrors = getBrick();

if (getOption("p") === false && getOption("plp") === false) {
    include_once ADMINPANEL_TEMPLATES . "home.phtml";
    $mainFrame = getBrick();
} elseif (getOption("p") !== false) {
    include_once ADMINPANEL_TEMPLATES . "panels/" . getOption("p") . ".phtml";
    $mainFrame = getBrick();
} elseif (getOption("plp") !== false) {
    include_once ADMINPANEL_ADDONS . getOption("plp") . "/pages/adminpanel.html";
    $mainFrame = getBrick();
}

include_once ADMINPANEL_TEMPLATES . "footer.html";
$mainFooter = getBrick();

$obMain = str_replace_once("{ADMINPANEL_HEAD}", $mainHeader, $obMain);
$obMain = str_replace_once("{ADMINPANEL_UPLOADER}", $mainUploader, $obMain);
$obMain = str_replace_once("{ADMINPANEL_NAVBAR}", $mainNavbar, $obMain);
$obMain = str_replace_once("{ADMINPANEL_ERRORS}", $mainErrors, $obMain);
$obMain = str_replace_once("{ADMINPANEL_CONTENT}", $mainFrame, $obMain);
$obMain = str_replace_once("{ADMINPANEL_FOOTER}", $mainFooter, $obMain);

if (getOption("p") == "staticc") {
    $obMain = str_replace_once("{ADMINPANEL_STATICC_STYLESHEET}",
        "<link href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/sp-style.css\" rel=\"stylesheet\">",
        $obMain);
} else {
    $obMain = str_replace_once("{ADMINPANEL_STATICC_STYLESHEET}", "", $obMain);
}

$obMain = \Engine\PluginManager::IntegrateCSS($obMain);
$obMain = \Engine\PluginManager::IntegrateFooterJS($obMain);
$obMain = \Engine\PluginManager::IntegrateHeaderJS($obMain);
echo $obMain;
\Guards\Logger::addVisitLog("I was in adminpanel page.");

