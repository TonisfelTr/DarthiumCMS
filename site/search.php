<?php
if (!defined("TT_Index")){ header("index.php?page=errors/forbidden"); exit; }
$pageName = \Engine\LanguageManager::GetTranslation("search.page_name");
$count = 0;
if ($_GET["param"] == "author") {
    $results = \Forum\ForumAgent::SearchByTopicAuthorNickname($_GET["search"], (!empty($_GET["p"])) ? $_GET["p"] : 1);
    $count = \Forum\ForumAgent::GetCountTopicsOfAuthor($_GET["search"]);
}
elseif ($_GET["param"] == "quize") {
    $results = \Forum\ForumAgent::SearchByQuizeQuestion($_GET["search"], (!empty($_GET["p"])) ? $_GET["p"] : 1);
    $count = \Forum\ForumAgent::GetCountQuizesByQuestion($_GET["search"]);
}
else{
    $results = \Forum\ForumAgent::SearchByTopicName($_GET["search"], (!empty($_GET["p"])) ? $_GET["p"] : 1);
    $count = \Forum\ForumAgent::GetCountTopicsByName($_GET["search"]);
}

if (empty($results)){
   echo "<h3><span class=\"glyphicons glyphicons-ice-cream-no\"></span> " . \Engine\LanguageManager::GetTranslation("search.nothing_finded") . "</h3>";
} else {
    echo \Engine\LanguageManager::GetTranslation("search.finded_results") . $count;
    foreach ($results as $result){
        include "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/searchform.html";
        $form = getBrick();
        $author = new \Users\Models\User($result["authorId"]);
        $topic = new \Forum\Models\Topic(\Forum\ForumAgent::GetTopicId($result["name"]));
        $form = str_replace_once("{AUTHOR_TOPIC_AVATAR}", $author->getAvatar(), $form);
        $form = str_replace_once("{AUTHOR_TOPIC_ID}", $author->getId(), $form);
        $form = str_replace_once("{AUTHOR_TOPIC_NICKNAME}", $author->getNickname(), $form);
        $form = str_replace_once("{TOPIC_NAME}", $topic->getName(), $form);
        $form = str_replace_once("{TOPIC_ID}", $topic->getId(), $form);
        $form = str_replace_once("{TOPIC_CATEGORY_NAME}", $topic->getCategory()->getName(), $form);
        $form = str_replace_once("{TOPIC_CATEGORY_ID}", $topic->getCategory()->getId(), $form);
        echo $form;
    }
    $pagesCount = $count / 15;
    if ($pagesCount > 1){
        $pagination = "<div class=\"btn-group\">";
        for ($i = 1; $i <= $pagesCount; $i++){
            $pagination .= "<a class=\"btn btn-default\" href=\"?search=" . $_GET["search"] .
                "&param=" . $_GET["param"] . "&p=$i\">$i</a>";
        }
        $pagination .= "</div>";
        echo $pagination;
    }
}