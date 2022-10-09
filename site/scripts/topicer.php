<?php

include_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

$session = \Users\UserAgent::SessionContinue();
if ($session === TRUE) {
    $user = new \Users\Models\User($_SESSION["uid"]);

    if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
        header("Location: banned.php");
        exit;
    }

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

                $category = new \Forum\Models\Category($category);
                if (!$category->CanCreateTopic() && !$user->UserGroup()->getPermission("category_params_ignore")) {
                    header("Location: ../../?page=newtopic&res=3np");
                    exit;
                }

                if (strlen($_POST["topic-name-input"]) < 5 || strlen($_POST["topic-name-input"]) > 100) {
                    header("Location: ../../?page=newtopic&res=3ntltn");
                    exit;
                }

                if (strlen($_POST["topic-content-textarea"]) <= 15) {
                    header("Location: ../../?page=newtopic&res=3ntlm");
                    exit;
                }

                $newTopicId = \Forum\ForumAgent::CreateTopic($user->getId(), $_POST["topic-name-input"], $category->getId(), $_POST["topic-descript-textarea"], $_POST["topic-content-textarea"]);
                if ($newTopicId !== false) {
                    //Quize construct
                    if ($_POST["quizer-status"] == 1){
                        if (isset($_POST["quizer-answers"]) && $_POST["quizer-answers"] != ""){
                            $answers = [];
                            $answersLink = explode(",", $_POST["quizer-answers"]);
                            for ($i = 1; $i <= count($answersLink); $i++){
                                if (in_array($i, $answersLink)){
                                    if (strlen($_POST["quize-answer-$i"]) < 4){
                                        header("Location: ../../?page=newtopic&res=3nqa");
                                        exit;
                                    }
                                    array_push($answers, $_POST["quize-answer-$i"]);
                                } else
                                    continue;
                            }
                            \Forum\ForumAgent::CreateQuize($newTopicId, $_POST["quize-question-input"], $answers);
                        } else {
                            header("Location: ../../?page=newtopic&res=3nqt");
                            exit;
                        }
                    }

                    \Forum\ForumAgent::CreateMentionNotification('c', $user->getId(), $newTopicId, $_POST["topic-content-textarea"]);
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
        $topic = new \Forum\Models\Topic($_POST["topic-edit-id"]);
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
        if ($topic->getStatus() != $_POST["topic-status"] && !$user->UserGroup()->getPermission("topic_manage")){
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

            if (strlen($_POST["topic-name-input"]) < 5 || strlen($_POST["topic-name-input"]) > 100) {
                header("Location: ../../?topic=$id&edit&res=3ntltn");
                exit;
            }

            if (strlen($_POST["topic-content-textarea"]) <= 15) {
                header("Location: ../../?topic=$id&edit&res=3ntlm");
                exit;
            }


            if ($topic->getCategoryId() != $category && $user->getId() != $topic->getAuthorId()){
                $topic->getAuthor()->Notifications()->createNotify(8, $user->getId(), $topic->getId());
            }
            if ($user->getId() != $topic->getAuthorId()){
                $topic->getAuthor()->Notifications()->createNotify(12, $user->getId(), $topic->getId());
            }

            $status = $topic->getStatus();
            $result = \Forum\ForumAgent::ChangeTopic($topic->getId(), ["name" => $_POST["topic-name-input"],
                                                                        "categoryId" => $category,
                                                                        "text" => $_POST["topic-content-textarea"],
                                                                        "preview" => $_POST["topic-descript-textarea"],
                                                                        "lastEditor" => $user->getId(),
                                                                        "lastEditDateTime" => date("Y-m-d H:i:s", \Engine\Engine::GetSiteTime()),
                                                                        "status" => $_POST["topic-status"]]);
            if ($result !== false) {
                if ($status != $_POST["topic-status"] && $topic->getAuthorId() != $user->getId())
                    $topic->getAuthor()->Notifications()->createNotify(13, $user->getId(), $topic->getId());
                \Forum\ForumAgent::CreateMentionNotification('c', $user->getId(), $topic->getId(), $_POST["topic-content-textarea"]);
                header("Location: ../../?topic=$id&res=3set");
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
        $topic = new \Forum\Models\Topic($_POST["topic-id"]);
        if (($user->getId() == $topic->getAuthorId() && $user->UserGroup()->getPermission("topic_delete")) || $user->UserGroup()->getPermission("topic_foreign_delete")){
            $author = $topic->getAuthorId();
            if (\Forum\ForumAgent::DeleteTopic($_POST["topic-id"])){
                if ($author != $user->getId())
                    $topic->getAuthor()->Notifications()->createNotify(9, $user->getId());
                header("Location: ../../index.php?res=3std");
                exit;
            } else {
                header("Location: ../../index.php?topic=".$_POST["topic-id"]."&res=3ndt");
                exit;
            }
        }
    }

    if (isset($_POST["topic-add-comment-btn"])){

        $topic = new \Forum\Models\Topic($_POST["topic-id"]);
        if ($topic->getStatus() == 0) {
            header("Location: index.php?topic=" . $topic->getId());
            exit;
        }
        $id = $topic->getId();
        if (!isset($_POST["comment-content-textarea"])){
            header("Location: ../../index.php?topic=$id&res=3ntc");
            exit;
        } else {
            if (strlen($_POST["comment-content-textarea"]) < 4){
                header("Location: ../../index.php?topic=$id&res=3ntlc");
                exit;
            }
        }
        $result = \Forum\ForumAgent::CreateComment($user->getId(), $id, $_POST["comment-content-textarea"]);
        if ($user->getId() != $topic->getAuthorId()){
            \Forum\ForumAgent::CreateMentionNotification('c', $user->getId(), $result, $_POST["comment-content-textarea"]);
            $user->Notifications()->createNotify(6, $user->getId(), $topic->getId());
        }
        header("Location: ../../index.php?topic=$id&res=3scc");
        exit;
    }

    if (isset($_POST["comment-delete-btn"])){
        $commentId = $_POST["comment-id"];

        $comment = new \Forum\Models\TopicComment($commentId);
        if (($user->getId() == $comment->getAuthorId() && $user->UserGroup()->getPermission("comment_delete")) ||
            $user->UserGroup()->getPermission("comment_foreign_delete")){
                \Forum\ForumAgent::DeleteComment($commentId);
                header("Location: ../../index.php?topic=" . $comment->getTopicParentId() . "&res=3sdc");
                exit;
            }
        else {
            header("Location: ../../index.php?topic=" . $comment->getTopicParentId() . "&res=3ndc");
            exit;
        }
    }

    if (isset($_POST["comment-edit-btn"])){
        $comment = new \Forum\Models\TopicComment($_POST["comment-edit-id"]);
        if (($user->UserGroup()->getPermission("comment_edit") && $comment->getAuthorId() == $user->getId())
            || $user->UserGroup()->getPermission("comment_foreign_edit")) {
            if (strlen($_POST["comment-content-textarea"]) < 15){
                header("Location: ../../index.php?topic=" . $comment->getTopicParentId() . "&cedit=" . $comment->getId() . "&res=3ntlc");
                exit;
            }
            \Forum\TopicComment::EditComment($comment->getId(), $user->getId(), $_POST["comment-edit-reason"],
                \Engine\Engine::GetSiteTime(), $_POST["comment-content-textarea"]);
            \Forum\ForumAgent::CreateMentionNotification('c', $user->getId(), $comment->getId(), $_POST["comment-content-textarea"]);
            header("Location: ../../index.php?topic=" . $comment->getTopicParentId() . "&res=3sec");
            exit;
        }
        else {
            header("Location: ../../index.php?topic=" . $comment->getTopicParentId() . "&res=3npec");
            exit;
        }
    }

}
header("Location: ../../index.php?page=errors/notperm");
exit;