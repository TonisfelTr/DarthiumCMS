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
else {
    foreach($topicList as $topic){
        $topic = new \Forum\Topic($topic);
        include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/preview.html";
        $newBody = getBrick();
        $newBody = str_replace_once("{TOPIC_AUTHOR_AVATAR}", $topic->getAuthor()->getAvatar(), $newBody);
        $newBody = str_replace_once("{TOPIC_AUTHOR_ID}", $topic->getAuthor()->getId(), $newBody);
        $newBody = str_replace("{TOPIC_ID}", $topic->getId(), $newBody);
        $newBody = str_replace_once("{TOPIC_AUTHOR_NICKNAME}", $topic->getAuthor()->getNickname(), $newBody);
        $newBody = str_replace_once("{TOPIC_LIKES_COUNT}", $topic->getLikes(), $newBody);
        $newBody = str_replace_once("{TOPIC_MARKS_COUNT}", $topic->getMarksCount(), $newBody);
        $newBody = str_replace_once("{TOPIC_DISLIKES_COUNT}", $topic->getDislikes(), $newBody);
        $newBody = str_replace_once("{TOPIC_NAME}", $topic->getName(), $newBody);
        $newBody = str_replace_once("{TOPIC_BODY}", \Engine\Engine::CompileBBCode($topic->getPretext()), $newBody);
        echo $newBody;
    }
}
?>

