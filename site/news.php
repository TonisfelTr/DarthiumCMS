<?php
/** TONISFEL TAVERN CMS
 *
 */
if (!defined("TT_Index")){ header("index.php?page=errors/forbidden"); exit; }
if (empty($_GET["category"])) {
    $pageName = \Engine\LanguageManager::GetTranslation("home");
    $_GET["category"] = null;
}
else
    $pageName = \Forum\ForumAgent::GetCategoryParam($_GET["category"], "name");

if (isset($_GET["category"])) {
    $topicList = \Forum\ForumAgent::GetTopicList((!empty($_GET["p"])) ? $_GET["p"] : 1, false, $_GET["category"]);
    $topicCount = \Forum\ForumAgent::GetTopicCount($_GET["category"]);
} else {
    $topicList = \Forum\ForumAgent::GetTopicList((!empty($_GET["p"])) ? $_GET["p"] : 1, false);
    $topicCount = \Forum\ForumAgent::GetTopicCount();
}

if ($topicCount == 0)
    include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news_empty.html";
else {
    for ($i = 0; $i < count($topicList); $i++){
        $topic = new \Forum\Topic($topicList[$i]);
        include "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/preview.html";
        $newBody = getBrick();
        $newBody = str_replace_once("{TOPIC_AUTHOR_AVATAR}", $topic->getAuthor()->getAvatar(), $newBody);
        $newBody = str_replace_once("{TOPIC_AUTHOR_ID}", $topic->getAuthor()->getId(), $newBody);
        $newBody = str_replace("{TOPIC_ID}", $topic->getId(), $newBody);
        $newBody = str_replace_once("{TOPIC_AUTHOR_NICKNAME}", $topic->getAuthor()->getNickname(), $newBody);
        $newBody = str_replace_once("{TOPIC_AUTHOR_GROUP_COLOR}", $topic->getAuthor()->UserGroup()->getColor(), $newBody);
        $newBody = str_replace_once("{TOPIC_AUTHOR_GROUP_NAME}", $topic->getAuthor()->UserGroup()->getName(), $newBody);
        $newBody = str_replace_once("{TOPIC_AUTHOR_GROUP_COLOR}", $topic->getAuthor()->UserGroup()->getColor(), $newBody);
        $newBody = str_replace_once("{TOPIC_AUTHOR_GROUP_ID}", $topic->getAuthor()->UserGroup()->getId(), $newBody);
        $newBody = str_replace_once("{TOPIC_LIKES_COUNT}", $topic->getLikes(), $newBody);
        $newBody = str_replace_once("{TOPIC_MARKS_COUNT}", $topic->getMarksCount(), $newBody);
        $newBody = str_replace_once("{TOPIC_DISLIKES_COUNT}", $topic->getDislikes(), $newBody);
        $newBody = str_replace_once("{TOPIC_NAME}", (($topic->getStatus() == 0) ? "<span class=\"glyphicons glyphicons-lock\"></span> " : "" ) . $topic->getName(), $newBody);
        $newBody = str_replace_once("{TOPIC_BODY}", Engine\Engine::ChatFilter(\Engine\Engine::CompileMentions(html_entity_decode(\Engine\Engine::CompileBBCode($topic->getPretext())))), $newBody);
        $newBody = str_replace_once("{TOPIC_STATUS_ICON}", (\Forum\ForumAgent::IsExistQuizeInTopic($topic->getId())) ? "<span class=\"glyphicons glyphicons-equalizer\"></span>" : "", $newBody);
        $topic = null;
        echo $newBody;
    }
    $pageBorder = $topicCount/14;
    $btns = "<div class=\"btn-group pagination\">";
    for ($j = 1; $j <= $pageBorder; $j++){
        $btns .= "<a href=\"?p=$j\" class=\"btn btn-default\">$j</a>";
    }
    $btns .= "</div>";
    echo $btns;
}
?>

