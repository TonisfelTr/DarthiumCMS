<?php

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Builder\Controllers\BannerAgent;
use Engine\Engine;
use Engine\RouteAgent;
use Forum\ForumAgent;
use Forum\Models\Topic;
use Forum\StaticPagesAgent;
use Users\Models\User;
use Users\Services\FlashSession;
use Users\Services\Session;
use Users\UserAgent;

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

/** @var $user User Variable of current user. It has been included from index.php in public folder. */

define("CURRENT_MODULE", "main");

$main = BuildManager::includeContentFromTemplate("main.html");
echo BuildManager::createHashAndDrop($main);
exit(0);

$lastTopics = \Forum\ForumAgent::GetTopicList(1, true);
if (empty($lastTopics)) {
    $ltText = \Engine\LanguageManager::GetTranslation("empty_news_list");
} else {
    $ltText = "<ul>";
    foreach ($lastTopics as $topicId) {
        $topic  = new \Forum\Models\Topic($topicId["id"]);
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
    $main = str_replace("{PROFILE_UPLOADER:STYLESHEET}", "<link rel=\"stylesheet\" href=\"{SITE_DOMAIN}/css/main/uploader-style.css\">", $main);
    $main = str_replace("{PROFILE_UPLOADER:JS}", $uploaderBlock ?? "", $main);
    $main = str_replace("{PROFILE_UPLOADER_BLOCK}", "", $main);
}

include_once HOME_ROOT . "site/scripts/SpoilerController.js";
$spoilerManager = getBrick();

$main = str_replace_once("{SPOILER_CONTROLLER:JS}", $spoilerManager, $main);

if (\Engine\Engine::GetEngineInfo("sms") == 0) {
    $main = str_replace_once("{METRIC_JS}", null, $main);
} else {
    $main = str_replace_once("{METRIC_JS}", \Engine\Engine::GetAnalyticScript(), $main);
}

$main = str_replace("{SITE_DOMAIN}", Engine::GetEngineInfo("sd"), $main);

$main = \Engine\PluginManager::Integration($main);

\Guards\Logger::addVisitLog("I was in index page.");

?>