<?php

include_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

$session = \Users\UserAgent::SessionContinue();
if ($session === TRUE) {
    $user = new \Users\User($_SESSION["uid"]);

    if (isset($_POST["topic-create-btn"])) {
        if ($user->UserGroup()->getPermission("topic_create")) {
            $category = $_POST["topic-category-select"];

            if ($category != "none") {
                if (!\Forum\ForumAgent::isCategoryExists($category)) {
                    header("Location: ../../?page=newtopic&res=3nsc");
                    exit;
                }
                if (\Forum\ForumAgent::GetCategoryParam($category, "public") !== 1 &&
                    (!$user->UserGroup()->getPermission("category_see_unpublic") || !$user->UserGroup()->getPermission("category_params_ignore"))) {
                    header("Location: ../../?page=newtopic&res=3np");
                    exit;
                }

                $category = new \Forum\Category($category);
                if (!$category->CanCreateTopic() && !$user->UserGroup()->getPermission("category_params_ignore")) {
                    header("Location: ../../?page=newtopic&res=3np");
                    exit;
                }

                if (strlen($_POST["topic-name-input"]) <= 4 || strlen($_POST["topic-name-input"]) > 100) {
                    header("Location: ../../?page=newtopic&res=3ntltn");
                    exit;
                }

                if (strlen($_POST["topic-content-textarea"]) <= 15) {
                    header("Location: ../../?page=newtopic&res=3ntlm");
                    exit;
                }
                $newTopicId = \Forum\ForumAgent::CreateTopic($user->getId(), $_POST["topic-name-input"], $category->getId(), $_POST["topic-descript-textarea"], $_POST["topic-content-textarea"]);
                if ($newTopicId !== false) {
                    header("Location: ../../?topic=$newTopicId");
                    exit;
                } else {
                    header("Location: ../../?page=newtopic&res=3ncct");
                    exit;
                }
            } else {
                header("Location: ../../?page=newtopic&res=3nsc");
                exit;
            }
        }
    }

    if (isset($_POST["topic-edit-btn"])) {
        $topic = new \Forum\Topic($_POST["topic-edit-id"]);
        $id = $topic->getId();
        if ($user->getId() == $topic->getAuthorId()) {
            if (!$user->UserGroup()->getPermission("topic_edit")) {
                header("Location: ../../?topic=$id&edit&res=3np");
                exit;
            }
        } else {
            if (!$user->UserGroup()->getPermission("topic_foreign_edit")){
                header("Location: ../../?topic=$id&edit&res=3np");
                exit;
            }
        }
        if ($topic->getCategoryId() != $_POST["topic-category-select"] && !$user->UserGroup()->getPermission("topic_manage")){
            header("Location: ../../?topic=$id&edit&res=3np");
            exit;
        }
        $category = $_POST["topic-category-select"];
        if ($category != "none") {
            if (!\Forum\ForumAgent::isCategoryExists($category)) {
                header("Location: ../../?topic=$id&edit&res=3nsc");
                exit;
            }
            if (\Forum\ForumAgent::GetCategoryParam($category, "public") !== 1 &&
                (!$user->UserGroup()->getPermission("category_see_unpublic"))) {
                header("Location: ../../?topic=$id&edit&res=3np");
                exit;
            }

            if (strlen($_POST["topic-name-input"]) <= 4 || strlen($_POST["topic-name-input"]) > 100) {
                header("Location: ../../?topic=$id&edit&res=3ntltn");
                exit;
            }

            if (strlen($_POST["topic-content-textarea"]) <= 15) {
                header("Location: ../../?topic=$id&edit&res=3ntlm");
                exit;
            }
            $result = \Forum\ForumAgent::ChangeTopic($topic->getId(), ["name" => $_POST["topic-name-input"],
                                                                        "categoryId" => $category,
                                                                        "text" => $_POST["topic-content-textarea"],
                                                                        "preview" => $_POST["topic-descript-textarea"],
                                                                        "lastEditor" => $user->getId(),
                                                                        "lastEditDateTime" => date("Y-m-d H:i:s", \Engine\Engine::GetSiteTime())]);
            if ($result !== false) {
                header("Location: ../../?topic=$id");
                exit;
            } else {
                header("Location: ../../?topic=$id&edit&res=3ncct");
                exit;
            }
        } else {
            header("Location: ../../?topic=$id&edit&res=3nsc");
            exit;
        }
    }

    if (isset($_POST["topic-remove-btn"])){
        if (!\Forum\ForumAgent::isTopicExists($_POST["topic-id"])){
            header("Location: ../../index.php");
            exit;
        }
        $topic = new \Forum\Topic($_POST["topic-id"]);
        if (($user->getId() == $topic->getAuthorId() && $user->UserGroup()->getPermission("topic_delete")) || $user->UserGroup()->getPermission("topic_foreign_delete")){
            if (\Forum\ForumAgent::DeleteTopic($_POST["topic-id"])){
                header("Location: ../../index.php");
                exit;
            } else {
                header("Location: ../../index.php?topic=".$_POST["topic-id"]."&res=3ndt");
                exit;
            }
        }
    }
}
header("Location: ../../index.php?page=errors/notperm");
exit;