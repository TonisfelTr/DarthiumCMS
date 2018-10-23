<?php

include_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

$session = \Users\UserAgent::SessionContinue();
if ($session === TRUE) $user = new \Users\User($_SESSION["uid"]);

if (isset($_POST["topic-create-btn"])){
    if ($user->UserGroup()->getPermission("topic_create")){
        $category = $_POST["topic-category-select"];

        if ($category != "none"){
            if (!\Forum\ForumAgent::isCategoryExists($category)){
                header("Location: ../../?page=newtopic&res=3nsc");
                exit;
            }
            if (\Forum\ForumAgent::GetCategoryParam($category, "public") !== 1 &&
                (!$user->UserGroup()->getPermission("category_see_unpublic") || !$user->UserGroup()->getPermission("category_params_ignore"))){
                header("Location: ../../?page=newtopic&res=3np");
                exit;
            }

            $category = new \Forum\Category($category);
            if (!$category->CanCreateTopic() && !$user->UserGroup()->getPermission("category_params_ignore")){
                header("Location: ../../?page=newtopic&res=3np");
                exit;
            }

            if (strlen($_POST["topic-name-input"]) <= 4 || strlen($_POST["topic-name-input"]) > 100){
                header("Location: ../../?page=newtopic&res=3ntltn");
                exit;
            }

            if (strlen($_POST["topic-content-textarea"]) <= 15){
                header("Location: ../../?page=newtopic&res=3ntlm");
                exit;
            }
            $newTopicId = \Forum\ForumAgent::CreateTopic($user->getId(), $_POST["topic-name-input"], $category->getId(), $_POST["topic-descript-textarea"], $_POST["topic-content-textarea"]);
            if ($newTopicId !== false){
                header("Location: ../../?page=topic&tid=$newTopicId");
                exit;
            } else {
               // header("Location: ../../?page=newtopic&res=3ncct");
                exit;
            }
        } else {
            header("Location: ../../?page=newtopic&res=3nsc");
            exit;
        }
    }
}

header("Location: ../../index.php?page=errors/notperm");
exit;