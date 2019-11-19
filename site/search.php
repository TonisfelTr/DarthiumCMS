<?php
if (!defined("TT_Index")){ header("index.php?page=errors/forbidden"); exit; }
$pageName = "Поиск";

$results = \Forum\ForumAgent::SearchByTopicName($_GET["search"]);
if (empty($results)){
   echo "<h3><span class=\"glyphicons glyphicons-ice-cream-no\"></span> Ничего не найдено :(</h3>";
} else {
    echo "Найдено результатов: " . count($results);
    foreach ($results as $result){
        include "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/searchform.html";
        $form = getBrick();
        $author = new \Users\User($result[0]);
        $topic = new \Forum\Topic(\Forum\ForumAgent::GetTopicId($result[1])[0]["id"]);
        $form = str_replace_once("{AUTHOR_TOPIC_AVATAR}", $author->getAvatar(), $form);
        $form = str_replace_once("{AUTHOR_TOPIC_ID}", $author->getId(), $form);
        $form = str_replace_once("{AUTHOR_TOPIC_NICKNAME}", $author->getNickname(), $form);
        $form = str_replace_once("{TOPIC_NAME}", $topic->getName(), $form);
        $form = str_replace_once("{TOPIC_ID}", $topic->getId(), $form);
        $form = str_replace_once("{TOPIC_CATEGORY_NAME}", $topic->getCategory()->getName(), $form);
        $form = str_replace_once("{TOPIC_CATEGORY_ID}", $topic->getCategory()->getId(), $form);
        echo $form;
    }

}