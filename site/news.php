<?php
if (!defined("TT_Index")){ header("index.php?page=errors/forbidden"); exit; }
if (empty($_REQUEST["category"])) {
    $pageName = "Главная";
    $_REQUEST["category"] = null;
}
else
    $pageName = \Forum\ForumAgent::GetCategoryParam($_REQUEST["category"], "name");

$topicList = \Forum\ForumAgent::GetTopicList((!empty($_REQUEST["p"])) ? $_REQUEST["p"] : 1, $_REQUEST["category"]);
$topicCount = \Forum\ForumAgent::GetTopicCount($_REQUEST["category"]);

if ($topicCount == 0)
    include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news_empty.html";

?>

