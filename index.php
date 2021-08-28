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

function getBrick()
{
    $e = ob_get_contents();
    ob_clean();
    return $e;
}

function str_replace_once($search, $replace, $text)
{
    $pos = strpos($text, $search);
    return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

define("TT_Index", true);
include "./engine/main.php";
\Engine\Engine::LoadEngine();

$user = false;
$sessionRes = \Users\UserAgent::SessionContinue();
if ($sessionRes == 1) $user = new \Users\User($_SESSION["uid"]);
if ((\Engine\Engine::GetEngineInfo("ss") == 0 && !$user) ||
    (\Engine\Engine::GetEngineInfo("ss") == 0 && $user->UserGroup()->getPermission("offline_visiter") != 1)) header("Location: offline.php");
if ($user !== false) if ($user->isBanned()) {
    header("Location: banned.php");
    exit;
}
if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)) {
    header("Location: banned.php");
    exit;
}
#Build category list. ################################################
if (!$user) {
    $categories = \Forum\ForumAgent::GetCategoryList(true);
} elseif ($user->UserGroup()->getPermission("category_see_unpublic")) {
    $categories = \Forum\ForumAgent::GetCategoryList(false);
} else
    $categories = \Forum\ForumAgent::GetCategoryList(true);
###############################################################################
# Build profile menu.
if ($user !== false) {
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
if (count($categories) == 0) {
    $categoryMenu = "<li class=\"dropdown-header\">" . \Engine\LanguageManager::GetTranslation("list_is_empty") . "</li>";
} else {
    foreach ($categories as $c) {
        $c = new \Forum\Category($c["id"]);
        $categoryMenu .= "<li><a href=\"?category=" . $c->getId() . "\" title=\"" . $c->getDescription() . "\">" . $c->getName() . "</a></li>" . PHP_EOL;
    }
}
################################################################################
#Build statistic block.

$onlineUsers = \Users\UserAgent::Get10OnlineUsers();
$onlineUserStatistic = "<ul>";
if (count($onlineUsers) > 0) {
    foreach ($onlineUsers as $person) {
        $onlineUserStatistic .= "<li><a class=\"alert-link\" href=\"./profile.php?uid=" . $person . "\">" . \Users\UserAgent::GetUserNick($person) . "</a></li>";
    }
} else {
    $onlineUserStatistic .= "<li>" . \Engine\LanguageManager::GetTranslation("no_online_users") . "</li>";
}
$onlineUserStatistic .= "</ul>";
################################################################################
ob_start();

include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/main.html";
$main = getBrick();

if (!empty($_GET["page"])) {
    if (file_exists("./site/" . $_GET["page"] . ".php")) {
        include_once "./site/" . $_GET["page"] . ".php";
    } else include_once "./site/errors/notfound.php";
} elseif (!empty($_GET["sp"])) {
    if (\Forum\StaticPagesAgent::isPageExists($_GET["sp"])) {
        //Here load keywords for site
        $keywords = \Forum\StaticPagesAgent::GetPageKeyWords($_GET["sp"]);
        $main = str_replace_once("{ENGINE_META:KEYWORDS}", $keywords, $main);
        /////////////////////////////
        echo nl2br(\Engine\Engine::CompileBBCode(file_get_contents("./site/statics/" . $_GET["sp"] . ".txt", FILE_USE_INCLUDE_PATH)));
        $pageName = \Forum\StaticPagesAgent::GetPage($_GET["sp"])->getPageName();
    } else {
        include "./site/errors/notfound.php";
    }
} elseif (!empty($_GET["topic"])) {
    if (\Forum\ForumAgent::isTopicExists($_GET["topic"])) {
        $topic = new \Forum\Topic($_GET["topic"]);
        if ($topic->getCategory()->isPublic() || (!$topic->getCategory()->isPublic() && $user !== false && $user->UserGroup()->getPermission("category_see_unpublic")))
            include_once "./site/newsviewer.php";
        else
            include_once "./site/errors/forbidden.php";
    } else include_once "./site/errors/notfound.php";
} elseif (!empty($_GET["search"])) {
    include_once "./site/search.php";
} elseif (!empty($_GET["group"])) {
    include_once "./site/grouplist.php";
} elseif (!empty($_GET["plp"])) {
    if (\Engine\PluginManager::IsTurnOn($_GET["plp"])) {
        include_once "addons/" . $_GET["plp"] . "/bin/main.php";
    } else {
        include_once "./site/errors/notfound.php";
    }
} else
    include_once "./site/news.php";
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
if ($user === false) {
    include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/pam_unauth.html";
} else {
    include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/pam_auth.html";
}
$authMenu = getBrick();
/******************************************************************************************************
 * Banner constructor
 ******************************************************************************************************/
$bigBanners = \SiteBuilders\BannerAgent::GetBanners("banner");
$bigBannersCount = count($bigBanners);
if ($bigBannersCount > 0) {
    $firstBigBanner = $bigBanners[rand(0, $bigBannersCount - 1)]["content"];
    $secondBigBanner = $bigBanners[rand(0, $bigBannersCount - 1)]["content"];
    $firstBigBanner = "<img class=\"img-bgbanner\" src=\"$firstBigBanner\">";
    $secondBigBanner = "<img class=\"img-bgbanner\" src=\"$secondBigBanner\">";
} else {
    $firstBigBanner = "<img class=\"img-bgbanner\" src=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/bigbanner.png\" title=\"" . \Engine\LanguageManager::GetTranslation("ad_is_free") . "\">";
    $secondBigBanner = "<img class=\"img-bgbanner\" src=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/bigbanner.png\" title=\"" . \Engine\LanguageManager::GetTranslation("ad_is_free") . "\">";
}
$main = str_replace_once("{MAIN_PAGE:FIRST_BIG_BANNER}", $firstBigBanner, $main);
$footer = str_replace_once("{MAIN_PAGE:SECOND_BIG_BANNER}", $secondBigBanner, $footer);

$firstBanner = @\SiteBuilders\BannerAgent::GetBannersByName("firstbanner")[0]["content"];
$secondBanner = @\SiteBuilders\BannerAgent::GetBannersByName("secondbanner")[0]["content"];
if (empty($firstBanner))
    $firstBanner = "<img class=\"img-smbanner\" src=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/smallbanner.png\" title=\"" . \Engine\LanguageManager::GetTranslation("ad_is_free") . "\">";
else
    $firstBanner = "<div class=\"img-smbanner\">" . $firstBanner . "</div>";
if (empty($secondBanner))
    $secondBanner = "<img class=\"img-smbanner\" src=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/smallbanner.png\" title=\"" . \Engine\LanguageManager::GetTranslation("ad_is_free") . "\">";
else
    $secondBanner = "<div class=\"img-smbanner\">" . $secondBanner . "</div>";
$footer = str_replace_once("{MAIN_PAGE:FOOTER_FIRST_SMALL_BANNER}", $firstBanner, $footer);
$footer = str_replace_once("{MAIN_PAGE:FOOTER_SECOND_SMALL_BANNER}", $secondBanner, $footer);

$panels = \SiteBuilders\SidePanelsAgent::GetPanelsList();
$rightPanels = "";
$leftPanels = "";
foreach ($panels as $panel) {
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
if (isset($_GET["res"])) {
    if ($_GET["res"] == "3sdt") {
        $info = "<div class='alert alert-success'><span class='glyphicon glyphicon-ok'></span> " . \Engine\LanguageManager::GetTranslation("topic_has_been_removed") . "</div>";
    }
}

$navbtns = array_merge(\SiteBuilders\NavbarAgent::GetElements(), \Engine\PluginManager::GetNavbarBtns());
$ul = "";
$ulEnd = "";
foreach ($navbtns as $navbtn) {
    if (isset($navbtn["ofPlugin"]) && !\Engine\PluginManager::IsTurnOn(intval($navbtn["ofPlugin"]))){
        continue;
    }

    switch ($navbtn["type"]) {
        case "nav-btn":
            $data_href = $navbtn["action"];
            $content = $navbtn["content"];
            $ul .= "<li><a href=\"$data_href\">$content</a></li>";
            break;
        case "nav-list":
            $children = \SiteBuilders\NavbarAgent::GetElementsOfList($navbtn["id"]);
            $data_content = $navbtn["action"];
            $content = $navbtn["content"];
            $id = $navbtn["id"];
            $ul .= "<li class=\"dropdown\"><a aria-expanded=\"false\" aria-haspopup=\"true\" role=\"button\" data-toggle=\"dropdown\" class=\"dropdown-toggle\" href=\"#\">$content <span class=\"caret\"></span></a>";
            if ($data_content != "")
                $ul .= "<ul class=\"dropdown-menu\">$data_content</ul>";
            else {
                $ul .= "<ul class=\"dropdown-menu\">";
                foreach ($children as $child) {
                    $text = $child[1];
                    $href = $child[2];
                    $ul .= "<li><a href=\"$href\">$text</a></li>";
                }
                $ul .= "</ul>";
            }
            $ul .= "</li>";
            break;
        case "end-nav-btn":
            $data_href = $navbtn["action"];
            $content = $navbtn["content"];
            $ulEnd .= "<li><a href=\"$data_href\">$content</a></li>";
            break;
        case "end-nav-list":
            $children = \Engine\PluginManager::GetNavbarListBtns($navbtn["id"]);
            $data_href = $navbtn["action"];
            $content = $navbtn["content"];
            $ulEnd .= "<li class=\"dropdown\"><a aria-expanded=\"false\" aria-haspopup=\"true\" role=\"button\" data-toggle=\"dropdown\" class=\"dropdown-toggle\" href=\"#\">$content <span class=\"caret\"></span></a>";
            if ($data_content != "")
                $ulEnd .= "<ul class=\"dropdown-menu\">$data_content</ul>";
            else {
                $ulEnd .= "<ul class=\"dropdown-menu\">";
                foreach ($children as $child) {
                    $text = $child[1];
                    $href = $child[2];
                    $ulEnd .= "<li><a href=\"$href\">$text</a></li>";
                }
                $ulEnd .= "</ul>";
            }
            $ulEnd .= "</li>";
            break;
    }
}
$navbar = str_replace_once("{INDEX_NAVBAR_BTNS}", $ul, $navbar);
$navbar = str_replace_once("{END_NAVBAR_BTNS}", $ulEnd, $navbar);

$main = str_replace_once("{INDEX_PAGE_NAVBAR}", $navbar, $main);
$main = str_replace_once("{INDEX_PAGE_HEADER}", $header, $main);
$main = str_replace_once("{INDEX_PAGE_OFFLINE}", $offline, $main);
$main = str_replace_once("{INDEX_PAGE_INFORMATOR}", $info, $main);
$main = str_replace_once("{INDEX_PAGE_LEFT}", $leftPanels, $main);

if (isset($_GET["category"]) && $_GET["category"] != "") {
    include_once \Engine\Engine::ConstructTemplatePath("category_hint", "", "phtml");
    $categoryHint = getBrick();
    $main = str_replace_once("{INDEX_CATEGORY_HINT}", $categoryHint, $main);
    $keywords = \Forum\ForumAgent::GetCategoryParam($_GET["category"], "keywords");
    $main = str_replace_once("{ENGINE_META:KEYWORDS}", $keywords, $main);
} else
    $main = str_replace_once("{INDEX_CATEGORY_HINT}", "", $main);

$main = str_replace_once("{INDEX_PAGE_NEWSPAPER}", $newsPaper, $main);

if ($_GET["category"] == "" || isset($_GET["search"])) {
    include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/searchpanel.html";
    $searchBlock = getBrick();
    if (isset($_GET["search"]) && $_GET["search"] != "") {
        $searchBlock = str_replace("{INDEX_SEARCHING_TEXT}", $_GET["search"], $searchBlock);
        if (isset($_GET["param"]) && $_GET["param"] != "") {
            if ($_GET["param"] == "author")
                $searchBlock = str_replace_once("{INDEX_SEARCHING_TYPE}", "$(\"button#search-by-author\").click()", $searchBlock);
            if ($_GET["param"] == "quize")
                $searchBlock = str_replace_once("{INDEX_SEARCHING_TYPE}", "$(\"button#search-by-quize\").click()", $searchBlock);
        }

    } else
        $searchBlock = str_replace("{INDEX_SEARCHING_TEXT}", "", $searchBlock);
    $main = str_replace_once("{INDEX_SEARCHING}", $searchBlock, $main);
} else {
    $main = str_replace_once("{INDEX_SEARCHING}", "", $main);
}
$main = str_replace_once("{INDEX_SEARCHING_TYPE}", "", $main);
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
        $main = str_replace_once("{INDEX_PROFILE_MENU:ADMPANEL_BUTTON}", "<li><a href=\"adminpanel.php\">" . \Engine\LanguageManager::GetTranslation("adminpanel-link") . "</a>", $main);
    else
        $main = str_replace_once("{INDEX_PROFILE_MENU:ADMPANEL_BUTTON}", "", $main);
}

$lastTopics = \Forum\ForumAgent::GetTopicList(1, true);
if (empty($lastTopics)) {
    $ltText = \Engine\LanguageManager::GetTranslation("empty_news_list");
} else {
    $ltText = "<ul>";
    foreach ($lastTopics as $topicId) {
        $topic = new \Forum\Topic($topicId["id"]);
        $ltText .= "<li><a class=\"alert-link\" href=\"?topic=" . $topicId["id"] . "\">" . $topic->getName() . "</a></li>";
    }
    $ltText .= "</ul>";
}

$main = str_replace_once("{LAST_SITE_TOPICS}", $ltText, $main);
$main = str_replace_once("{STATISTIC:NOW_AVAILABLE}", $onlineUserStatistic, $main);
$main = str_replace_once("{INDEX_CATEGORY_LIST}", $categoryMenu, $main);
$main = str_replace("{ENGINE_META:SITE_NAME}", \Engine\Engine::GetEngineInfo("sn"), $main);
$main = str_replace("{ENGINE_META:SITE_TAGLINE}", \Engine\Engine::GetEngineInfo("stl"), $main);
$main = str_replace("{REPORT_PAGE:JS}", null, $main);

if (!defined("TT_Uploader")) {
    $main = str_replace("{PROFILE_UPLOADER:STYLESHEET}", "", $main);
    $main = str_replace("{PROFILE_UPLOADER:JS}", "", $main);
    $main = str_replace("{PROFILE_UPLOADER_BLOCK}", "", $main);
} else {
    $main = str_replace("{PROFILE_UPLOADER:STYLESHEET}", "<link rel=\"stylesheet\" href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/uploader-style.css\">", $main);
    $main = str_replace("{PROFILE_UPLOADER:JS}", $uploaderBlock, $main);
    $main = str_replace("{PROFILE_UPLOADER_BLOCK}", "", $main);
}

include_once "./site/scripts/SpoilerController.js";
$spoilerManager = getBrick();

$main = str_replace_once("{SPOILER_CONTROLLER:JS}", $spoilerManager, $main);

if (\Engine\Engine::GetEngineInfo("smt")) {
    if (\Engine\Engine::GetEngineInfo("sms") == 0) {
        $main = str_replace_once("{METRIC_JS}", null, $main);
    } else {
        $main = str_replace_once("{METRIC_JS}", \Engine\Engine::GetAnalyticScript(), $main);
    }
} else {
    $main = str_replace_once("{METRIC_JS}", null, $main);
}

$main = \Engine\PluginManager::IntegrateCSS($main);
$main = \Engine\PluginManager::IntegrateHeaderJS($main);
$main = \Engine\PluginManager::IntegrateFooterJS($main);
$main = \Engine\PluginManager::Integration($main);

echo $main;


?>