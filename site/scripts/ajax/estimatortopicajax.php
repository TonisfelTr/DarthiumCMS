<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";
\Engine\Engine::LoadEngine();

if (isset($_POST["topicId"]) && isset($_POST["mark"])){
    if (\Users\UserAgent::SessionContinue() === true) $user = new \Users\Models\User($_SESSION["uid"]);
    else {
        echo "not allowed.";
        exit;
    }
    if (!\Forum\ForumAgent::isTopicExists($_POST["topicId"])){
        echo "not exists";
        exit;
    }
    if (\Forum\ForumAgent::EstimateTopic($_POST["topicId"], $user->getId(), $_POST["mark"])) {
        if ($_POST["mark"] == 1) {
            $topic = new \Forum\Models\Topic($_POST["topicId"]);
            $author = new \Users\Models\User($topic->getAuthorId());
            $author->Notifications()->createNotify(7, $user->getId(), $topic->getId());
        }
        echo "okey";
        exit;
    } else {
        echo "fail!";
        exit;
    }
}

if (isset($_POST["topicId"]) && isset($_POST["info"])){
    if (!\Forum\ForumAgent::isTopicExists($_POST["topicId"])){
        echo "not exists";
        exit;
    }
    $topic = new \Forum\Models\Topic($_POST["topicId"]);
    $var = ["likes" => $topic->getLikes(),
            "dislikes" => $topic->getDislikes(),
            "summa" => $topic->getMarksCount()];
    echo json_encode($var);
    exit;
}