<?php

if (!isset($user) || $user == false){
    header("Location: ../?page=errors/nonauth");
    exit;
}
define("TT_Uploader", 1);
$pageName = "Создание новой темы";
include_once "site/uploader.php";

$categoriesList = "";
foreach ($categories as $c){
    $category = new \Forum\Category($c);
    $categoriesList .= "<option value=\"" . $category->getId() . "\">" . $category->getName() . "</option>";
}

$lastAuthorsTopics = \Forum\ForumAgent::GetTopicsOfAuthor($user->getId(), true);
if (empty($lastAuthorsTopics)){
    $lastAuthorsTopicsText = "Нет ни одной созданной Вами темы.";
} else {
    $lastAuthorsTopicsText = "<ol>";
    foreach ($lastAuthorsTopics as $topicId){
        $topic = new \Forum\Topic($topicId);
        $lastAuthorsTopicsText .= "<li><a href=\"?topic=$topicId\">" . $topic->getName() . "</a></li>";
    }
    $lastAuthorsTopicsText .= "</ol>";
}

$lastTopics = \Forum\ForumAgent::GetTopicList(1, true);
if (empty($lastTopics)){
    $lastTopicsText = "Ещё не создано не одной темы. Вы будете первым!";
} else {
    $lastTopicsText = "<ol>";
    foreach ($lastTopics as $topicId){
        $topic = new \Forum\Topic($topicId);
        $lastTopicsText .= "<li><a href=\"?topic=$topicId\">" . $topic->getName() . "</a> [<a href=\"profile.php?uid=" . $topic->getAuthorId() . "\">" .$topic->getAuthor()->getNickname() . "</a>]</li>";
    }
    $lastTopicsText .= "</ol>";
}

include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/newtopic.html";
$newtopic = getBrick();

switch($_GET["res"]){
    case "3nsc":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Вы не выбрали категорию.</div>";
        break;
    case "3np":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> У Вас недостаточно прав для взаимодействия с данной категорией.</div>";
        break;
    case "3ntltn":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Название темы неправильной длины. Оно должно быть больше 4 символов и меньше 100.</div>";
        break;
    case "3ntlm":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Текст темы слишком короткий. Он должен быть длиннее 15 символов и нести в себе смысловую нагрузку.</div>";
        break;
    case "3ncct":
        $creatorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Не удалось создать тему. Обратитесь к Администратору.</div>";
        break;
    default:
        $creatorResponse = "";
        break;
}

$newtopic = str_replace_once("{NEWTOPIC_ERRORS}", $creatorResponse, $newtopic);
$newtopic = str_replace_once("{TOPICS:LAST_CREATED_AUTHOR_TOPICS}", $lastAuthorsTopicsText, $newtopic);
$newtopic = str_replace_once("{TOPICS:LAST_CREATED_TOPICS}", $lastTopicsText, $newtopic);
$newtopic = str_replace_once("{NEWTOPIC_PAGE:CATEGORIES_OPTION}", $categoriesList, $newtopic);

echo $newtopic;