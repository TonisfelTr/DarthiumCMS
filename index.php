<?php
/*****************************************************************************
 * TONISFEL TAVERN CMS.
 *
 * Author: Bagdanov Ilya.
 *
 * This page works by output buffering. Every part of template builds
 * high-grade system that we see for the request index.php.
 *
 *  Parts have code names that should replaced by HTML elements in html files
 *  in /site/templates/<template_name> directory.
 * */

function getBrick(){
    $e = ob_get_contents();
    ob_clean();
    return $e;
}

function str_replace_once($search, $replace, $text){
    $pos = strpos($text, $search);
    return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

define("TT_Index", true);
include "./engine/main.php";
\Engine\Engine::LoadEngine();
$user = false;
$sessionRes = \Users\UserAgent::SessionContinue();
if ($sessionRes == 1) $user = new \Users\User($_SESSION["uid"]);
if ((\Engine\Engine::GetEngineInfo("ss") == 0 && !$user) ||
    (\Engine\Engine::GetEngineInfo("ss") == 0 && $user->UserGroup()->getPermission("offline_visiter") != 1)) header("Location: offline.php");
if ($user !== false) if ($user->isBanned()) { header("Location: banned.php"); exit; }
if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){ header("Location: banned.php"); exit; }
#Build category list. ################################################
if (!$user){
    $categories = \Forum\ForumAgent::GetCategoryList(true);
} elseif ($user->UserGroup()->getPermission("category_see_unpublic")){
    $categories = \Forum\ForumAgent::GetCategoryList(false);
} else
    $categories = \Forum\ForumAgent::GetCategoryList(true);
###############################################################################
# Build profile menu.
if ($user !== false){
    $mailSpanClass = "nav-btn-" . (($user->MessageManager()->getNotReadCount() > 0) ? "new-" : "") . "counter";
    $mailSpanCount = ($user->MessageManager()->getNotReadCount() > 10) ? "10+" : $user->MessageManager()->getNotReadCount();
    $notificationSpanClass = "nav-btn-" . (($user->Notifications()->getNotificationsUnreadCount()) ? "new-" : "") . "counter";
    $notificationSpanCount = ($user->Notifications()->getNotificationsUnreadCount() > 10) ? "10+" : $user->Notifications()->getNotificationsUnreadCount();
    if ($user->UserGroup()->getPermission("enterpanel")) $canEnterToAP = true;
    else $canEnterToAP = false;
}
################################################################################
# Build categories menu.
$categoryMenu = "";
if (count($categories) == 0){
    $categoryMenu = "<li class=\"dropdown-header\">Список пуст :c</li>";
}
else {
    foreach($categories as $c){
        $c = new \Forum\Category($c);
        $categoryMenu .= "<li><a href=\"?category=". $c->getId() . "\" title=\"". $c->getDescription()."\">". $c->getName() ."</a></li>" . PHP_EOL;
    }
}
################################################################################
#Build statistic block.

$onlineUsers = \Users\UserAgent::Get10OnlineUsers();
$onlineUserStatistic = "<ul>";
if (count($onlineUsers) > 0) {
    for ($i = 0; $i < count($onlineUsers); $i++){
        $onlineUserStatistic .= "<li><a class=\"alert-link\" href=\"./profile.php?uid=" . $onlineUsers[$i] . "\">" . \Users\UserAgent::GetUserNick($onlineUsers[$i]) . "</a></li>";
    }
} else {
    $onlineUserStatistic .= "<li>Нет онлайн пользователей</li>";
}
$onlineUserStatistic .= "</ul>";
################################################################################
ob_start();

include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/main.html";
$main = getBrick();

if (!empty($_GET["page"])){
    if (file_exists("./site/". $_GET["page"] . ".php"))
        include_once "./site/".$_GET["page"] . ".php";
    else include_once "./site/errors/notfound.php"; }
elseif (!empty($_GET["sp"])){
        echo nl2br(\Engine\Engine::CompileBBCode(file_get_contents("./site/statics/" . $_GET["sp"] . ".txt", FILE_USE_INCLUDE_PATH)));
        $pageName = \Forum\StaticPagesAgent::GetPage($_GET["sp"])->getPageName();
    }
elseif (!empty($_GET["topic"])){
        if (\Forum\ForumAgent::isTopicExists($_GET["topic"]))
            include_once "./site/newsviewer.php";
        else include_once "./site/errors/notfound.php";
    }
elseif (!empty($_GET["search"])){

}
else include_once "./site/news.php";
$newsPaper = getBrick();
include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/footer.html";
$footer = getBrick();
include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/navbar.html";
$navbar = getBrick();
include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/leftside.html";
$leftSide = getBrick();
include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/rightside.html";
$rightSide = getBrick();
include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/header.html";
$header = getBrick();
if (\Engine\Engine::GetEngineInfo("ss") == 0) {
    include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/offline.html";
    $offline = getBrick();
} else $offline = "";
if ($user === false){
    include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/pam_unauth.html";
} else {
    include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/pam_auth.html";
}
$authMenu = getBrick();

$firstBanner = \SiteBuilders\BannerAgent::GetBannersByName("firstbanner")[0]["content"];
$secondBanner = \SiteBuilders\BannerAgent::GetBannersByName("secondbanner")[0]["content"];
if (empty($firstBanner))
    $firstBanner = "<img class=\"img-smbanner\" src=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp").  "/smallbanner.png\" title=\"Это место свободно\">";
else
    $firstBanner = "<div class=\"img-smbanner\">" . $firstBanner . "</div>";
if (empty($secondBanner))
    $secondBanner = "<img class=\"img-smbanner\" src=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp").  "/smallbanner.png\" title=\"Это место свободно\">";
else
    $secondBanner = "<div class=\"img-smbanner\">" . $secondBanner . "</div>";
$footer = str_replace_once("{MAIN_PAGE:FOOTER_FIRST_SMALL_BANNER}", $firstBanner, $footer);
$footer = str_replace_once("{MAIN_PAGE:FOOTER_SECOND_SMALL_BANNER}", $secondBanner, $footer);

$panels = \SiteBuilders\SidePanelsAgent::GetPanelsList();
$rightPanels = "";
$leftPanels = "";
foreach ($panels as $panel){
    $panel = new \SiteBuilders\SidePanel($panel["id"]);
    if ($panel->getVisibility()) {
        if ($panel->getType() == "leftside") {
            $leftPanel = str_replace_once("{PANEL_TITLE}", $panel->getName(), $leftSide);
            $leftPanel = str_replace_once("{PANEL_CONTENT}", $panel->getContent(), $leftPanel);
            $leftPanels .= $leftPanel;
        } elseif ($panel->getType() == "rightside") {
            $rightPanel = str_replace_once("{PANEL_TITLE}", $panel->getName(), $rightSide);
            $rightPanel = str_replace_once("{PANEL_CONTENT}", $panel->getContent(), $rightPanel);
            $rightPanels .= $rightPanel;
        }
    } else continue;
}

$info = "";
if (isset($_GET["res"])){
    if ($_GET["res"] == "3sdt"){
        $info = "<div class='alert alert-success'><span class='glyphicon glyphicon-ok'></span> Тема была успешно удалена!";
    }
}

$main = str_replace_once("{INDEX_PAGE_NAVBAR}", $navbar, $main);
$main = str_replace_once("{INDEX_PAGE_HEADER}", $header, $main);
$main = str_replace_once("{INDEX_PAGE_OFFLINE}", $offline, $main);
$main = str_replace_once("{INDEX_PAGE_INFORMATOR}", $info, $main);
$main = str_replace_once("{INDEX_PAGE_LEFT}", $leftPanels, $main);
$main = str_replace_once("{INDEX_PAGE_NEWSPAPER}", $newsPaper, $main);
$main = str_replace_once("{INDEX_PAGE_RIGHT}", $rightPanels, $main);
$main = str_replace_once("{INDEX_PAGE_FOOTER}", $footer, $main);
$main = str_replace_once("{ENGINE_META:DESCRIPTION}", \Engine\Engine::GetEngineInfo("ssc"), $main);
$main = str_replace_once("{ENGINE_META:KEYWORDS}", \Engine\Engine::GetEngineInfo("sh"), $main);
$main = str_replace_once("{INDEX_PAGE_TITLE}", $pageName . " - " . \Engine\Engine::GetEngineInfo("sn"), $main);
$main = str_replace_once("{INDEX_PROFILE_MENU}", $authMenu, $main);
if ($user !== false) {
    $main = str_replace("{INDEX_PROFILE_MENU:MAIL_SPAN_CLASS}", $mailSpanClass, $main);
    $main = str_replace("{INDEX_PROFILE_MENU:MAIL_SPAN_COUNT}", $mailSpanCount, $main);
    $main = str_replace("{INDEX_PROFILE_MENU:NOTIF_SPAN_CLASS}", $notificationSpanClass, $main);
    $main = str_replace("{INDEX_PROFILE_MENU:NOTIF_SPAN_COUNT}", $notificationSpanCount, $main);
    $main = str_replace("{INDEX_PROFILE_MENU:USER_NICKNAME}", $user->getNickname(), $main);
    if ($canEnterToAP)
        $main = str_replace_once("{INDEX_PROFILE_MENU:ADMPANEL_BUTTON}", "<li><a href=\"adminpanel.php\">Админ-панель</a>", $main);
    else
        $main = str_replace_once("{INDEX_PROFILE_MENU:ADMPANEL_BUTTON}", "", $main);
}

$lastTopics = \Forum\ForumAgent::GetTopicList(1, true);
if (empty($lastTopics)){
    $ltText = "Ещё не создано не одной темы. Вы будете первым!";
} else {
    $lastAuthorsTopicsText = "<ol>";
    foreach ($lastTopics as $topicId){
        $topic = new \Forum\Topic($topicId);
        $ltText .= "<li><a class=\"alert-link\" href=\"?topic=$topicId\">" . $topic->getName() . "</a></li>";
    }
    $ltText .= "</ol>";
}

$main = str_replace_once("{LAST_SITE_TOPICS}", $ltText, $main);
$main = str_replace_once("{STATISTIC:NOW_AVAILABLE}", $onlineUserStatistic, $main);
$main = str_replace_once("{INDEX_CATEGORY_LIST}", $categoryMenu, $main);
$main = str_replace("{ENGINE_META:SITE_NAME}", \Engine\Engine::GetEngineInfo("sn"), $main);
$main = str_replace("{ENGINE_META:SITE_TAGLINE}", \Engine\Engine::GetEngineInfo("stl"), $main);
$main = str_replace("{REPORT_PAGE:JS}", null, $main);

if (!defined("TT_Uploader")) {
    $main = str_replace("{PROFILE_UPLOADER:STYLESHEET}", null, $main);
    $main = str_replace("{PROFILE_UPLOADER:JS}", null, $main);
    $main = str_replace("{PROFILE_UPLOADER_BLOCK}", null, $main);
}
else {
    $main = str_replace("{PROFILE_UPLOADER:STYLESHEET}", "<link rel=\"stylesheet\" href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/uploader-style.css\">", $main);
    $main = str_replace("{PROFILE_UPLOADER:JS}", $uploaderBlock, $main);
}
if (\Engine\Engine::GetEngineInfo("smt")){
    if (\Engine\Engine::GetEngineInfo("sms") == 0) {
        $main = str_replace_once("{METRIC_JS}", null, $main);
    } else {
        $main = str_replace_once("{METRIC_JS}", \Engine\Engine::GetAnalyticScript(), $main);
    }
} else {
    $main = str_replace_once("{METRIC_JS}", null, $main);
}
ob_end_clean();


echo $main;
?>