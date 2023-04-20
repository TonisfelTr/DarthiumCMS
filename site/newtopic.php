<?php

if (!isset($user) || $user == false){
    header("Location: ../?page=errors/nonauth");
    exit;
}
define("TT_Uploader", 1);
$pageName = \Engine\LanguageManager::GetTranslation("newtopic.panel_name");
include_once "site/uploader.php";

$categoriesList = "";
foreach ($categories as $c){
    $category = new \Forum\Models\Category($c["id"]);
    if ($category->isPublic() || (!$category->isPublic() && $user->getUserGroup()->getPermission("category_see_unpublic")))
        $categoriesList .= "<option value=\"" . $category->getId() . "\">" . $category->getName() . "</option>";
}

$lastAuthorsTopics = \Forum\ForumAgent::GetTopicsOfAuthor($user->getId());
if (empty($lastAuthorsTopics)){
    $lastAuthorsTopicsText = \Engine\LanguageManager::GetTranslation("newtopic.no_topics_from_you");
} else {
    $lastAuthorsTopicsText = "<ol>";
    foreach ($lastAuthorsTopics as $topicId){
        $topic = new ForumModels$1
        $lastAuthorsTopicsText .= "<li><a href=\"?topic=$topicId[id]\">" . $topic->getName() . "</a></li>";
    }
    $lastAuthorsTopicsText .= "</ol>";
}

$lastTopics = \Forum\ForumAgent::GetTopicList(1, true);
if (empty($lastTopics)){
    $lastTopicsText = \Engine\LanguageManager::GetTranslation("newtopic.no_topics_on_site");
} else {
    $lastTopicsText = "<ol>";
    foreach ($lastTopics as $topicId){
        $topic = new ForumModels$1
        $lastTopicsText .= "<li><a href=\"?topic=$topicId\">" . $topic->getName() . "</a> [<a href=\"profile.php?uid=" . $topic->getAuthorId() . "\">" .$topic->getAuthor()->getNickname() . "</a>]</li>";
    }
    $lastTopicsText .= "</ol>";
}



include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/newtopic.html";
$newtopic = getBrick();

switch($_GET["res"]){
    case "3nsc":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " .  \Engine\LanguageManager::GetTranslation("newtopic.category_not_setted") ."</div>";
        break;
    case "3np":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " .  \Engine\LanguageManager::GetTranslation("newtopic.category_not_permitted") ."</div>";
        break;
    case "3ntltn":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " .  \Engine\LanguageManager::GetTranslation("newtopic.invalid_topic_name") ."</div>";
        break;
    case "3ntlm":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " .  \Engine\LanguageManager::GetTranslation("newtopic.invalid_topic_text") ."</div>";
        break;
    case "3ncct":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " .  \Engine\LanguageManager::GetTranslation("newtopic.topic_create_error") ."</div>";
        break;
    case "3nqa":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " .  \Engine\LanguageManager::GetTranslation("newtopic.quize_empty_answer") ."</div>";
        break;
    case "3nqt":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " .  \Engine\LanguageManager::GetTranslation("newtopic.quize_invalid_question") ."</div>";
        break;
    default:
        $creatorResponse = "";
        break;
}

$newtopic = str_replace_once("{NEWTOPIC_ERRORS}", $creatorResponse, $newtopic);
$newtopic = str_replace_once("{TOPICS:LAST_CREATED_AUTHOR_TOPICS}", $lastAuthorsTopicsText, $newtopic);
$newtopic = str_replace_once("{TOPICS:LAST_CREATED_TOPICS}", $lastTopicsText, $newtopic);
$newtopic = str_replace_once("{NEWTOPIC_PAGE:CATEGORIES_OPTION}", $categoriesList, $newtopic);

include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/quizer.html";
$quizer = getBrick();
$quizer = str_replace_once("{QUIZE_QUESTION}", "", $quizer);
$quizer = str_replace_once("{QUIZE_ANSWERS}", "", $quizer);

$newtopic = str_replace_once("{NEWTOPIC_QUIZE}", $quizer, $newtopic);

echo $newtopic;