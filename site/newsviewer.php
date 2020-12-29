<?php
define("TT_Uploader", true);
$topic = new \Forum\Topic($_GET["topic"]);
$author = $topic->getAuthor();
include_once "./site/uploader.php";

if (!isset($_GET["edit"]) && !isset($_GET["cedit"])) {
    $page = (isset($_GET["p"]) ? $_GET["p"] : 1);
    $pageName = $topic->getName();

    if ($page == 1){
        include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/new.html";
        $new = getBrick();
        $info = "";
        if (isset($_GET["res"])) {
            switch ($_GET["res"]) {
                case "3set":
                    $info = "<div class=\"alert alert-success\"><span class='glyphicon glyphicon-ok'></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.topic_edited_success") . "</div>";
                    break;
                case "3sdc":
                    $info = "<div class=\"alert alert-success\"><span class='glyphicon glyphicon-ok'></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.comment_removed_success") . "</div>";
                    break;
                case "3ndc":
                    $info = "<div class=\"alert alert-danger\"><span class='glyphicon glyphicon-remove'></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.comment_removed_failed") . "</div>";
                    break;
                case "3scc":
                    $info = "<div class=\"alert alert-success\"><span class='glyphicon glyphicon-ok'></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.comment_created_success") . "</div>";
                    break;
                case "3sec":
                    $info = "<div class=\"alert alert-success\"><span class='glyphicon glyphicon-ok'></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.comment_edited_success") . "</div>";
                    break;
                case "3npec":
                    $info = "<div class=\"alert alert-danger\"><span class='glyphicon glyphicon-remove'></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.comment_edited_not_permitted") . "</div>";
                    break;
                case "3ntlc":
                    $info = "<div class=\"alert alert-danger\"><span class='glyphicon glyphicon-remove'></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.too_short_comment") . "</div>";
                    break;
            }
        }
        $new = str_replace_once("{TOPIC_DELETE_ERROR}", $info, $new);
//Quize block
        if (\Forum\ForumAgent::IsExistQuizeInTopic($topic->getId())) {
            include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/quizeframe.html";
            $quizeFrame = getBrick();
            $quize = new \Forum\Quize(\Forum\ForumAgent::GetQuizeByTopic($topic->getId()));
            if ($user !== false && \Forum\ForumAgent::IsVoted($user->getId(), $quize->getId())) {
                $quizeFrame = str_replace_once("{QUIZE_QUIZER_HIDDEN}", "hidden", $quizeFrame);
                $quizeFrame = str_replace_once("{QUIZE_RESULTS_HIDDEN}", "", $quizeFrame);
            } elseif ($user === false) {
                $quizeFrame = str_replace_once("{QUIZE_QUIZER_HIDDEN}", "hidden", $quizeFrame);
                $quizeFrame = str_replace_once("{QUIZE_RESULTS_HIDDEN}", "", $quizeFrame);
            } else {
                $quizeFrame = str_replace_once("{QUIZE_QUIZER_HIDDEN}", "", $quizeFrame);
                $quizeFrame = str_replace_once("{QUIZE_RESULTS_HIDDEN}", "hidden", $quizeFrame);
            }

            $quizeFrame = str_replace("{QUIZE_QUESTION}", \Engine\Engine::MakeUnactiveCodeWords($quize->getQuestion()), $quizeFrame);
            $quizeAnswersForInput = "";
            $quizeAnswers = $quize->getVars();
            for ($i = 0; $i < count($quizeAnswers); $i++) {
                $color = $i + 1;
                $id = $quizeAnswers[$i][0];
                $var = $quizeAnswers[$i][1];
                $quizeAnswersForInput .= "<p><span class=\"quize-label quize-label-$color\">$var</span> "
                    . "<span data-var-id=\"$id\">" . $quize->getProcentAnswer($quizeAnswers[$i][0]) . "</span></p>";
            }
            $quizeFrame = str_replace_once("{QUIZE_VOTES}", \Engine\Engine::MakeUnactiveCodeWords($quizeAnswersForInput), $quizeFrame);
            $quizeAnswersForInput = "";
            $quizeAnswers = $quize->getVars();
            for ($i = 0; $i < count($quizeAnswers); $i++) {
                $id = $quizeAnswers[$i][0];
                $var = $quizeAnswers[$i][1];
                $quizeAnswersForInput .= "<button class=\"btn btn-default\" type=\"button\" id=\"quize-answer-$id\" 
                data-var-id=\"$id\">$var</button>";
            }
            $quizeFrame = str_replace_once("{QUIZE_ANSWERS}",\Engine\Engine::MakeUnactiveCodeWords($quizeAnswersForInput), $quizeFrame);
            $quizeFrame = str_replace("{QUIZE_ANSWERED_COUNT}", $quize->getTotalAnswers(), $quizeFrame);
            if ($user !== false)
                $quizeFrame = str_replace_once("{USER_ID}", $user->getId(), $quizeFrame);
            else
                $quizeFrame = str_replace_once("{USER_ID}", 0, $quizeFrame);
            $quizeFrame = str_replace("{QUIZE_ID}", $quize->getId(), $quizeFrame);
            $new = str_replace_once("{TOPIC_QUIZE}", $quizeFrame, $new);
        } else {
            $new = str_replace_once("{TOPIC_QUIZE}", "", $new);
        }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $new = str_replace("{TOPIC_ID}", $topic->getId(), $new);
        $new = str_replace_once("{TOPIC_HEADER}", (($topic->getStatus() == 0) ? "<span class=\"glyphicons glyphicons-lock\"></span> " : "" ) . $topic->getName(), $new);
        $new = str_replace_once("{TOPIC_CATEGORY_ID}", $topic->getCategoryId(), $new);
        $new = str_replace_once("{TOPIC_CATEGORY}", $topic->getCategory()->getName(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_AVATAR}", $author->getAvatar(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_NICKNAME}", $author->getNickname(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_GROUP_COLOR}", $author->UserGroup()->getColor(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_GROUP}", $author->UserGroup()->getName(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_GROUP_ID}", $author->UserGroup()->getId(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_ID}", $topic->getAuthorId(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_LAST_ONLINE}", ((\Engine\Engine::GetSiteTime() > $author->getLastTime() + 15 * 60) ? "заходил" . (($author->getSex() == 2) ? "а" : "")
            . " в " . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $author->getLastTime())) : "<span style=\"color: #00dd00;\">онлайн</span>"), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_SEX}", \Engine\LanguageManager::GetTranslation("newsviewer.sex") . " " . (($author->getSex() == 2) ? "<span class=\"glyphicons glyphicons-gender-female\"></span> " . \Engine\LanguageManager::GetTranslation("gender_female")
                : ($author->getSex() == 1)
                    ? "<span class=\"glyphicons glyphicons-gender-male\"></span> " . \Engine\LanguageManager::GetTranslation("gender_male") : "") . "<br>", $new);
        $new = str_replace_once("{TOPIC_AUTHOR_REGDATE}", (($author->getSex() == 2) ? \Engine\LanguageManager::GetTranslation("newsviewer.she_registered") . " " :  \Engine\LanguageManager::GetTranslation("newsviewer.he_registered") . " ") . \Engine\Engine::DateFormatToRead($author->getRegDate()), $new);
        $new = str_replace_once("{TOPICS_AUTHOR_COUNT}", \Forum\ForumAgent::GetCountTopicOfAuthor($topic->getAuthorId()), $new);
        $new = str_replace_once("{TOPICS_AUTHOR_RATE}", $author->getReputation()->getReputationPoints(), $new);
        $new = str_replace_once("{TOPIC_CREATE_DATETIME}", \Engine\Engine::DatetimeFormatToRead($topic->getCreateDate()), $new);
        if ($topic->getLastEditor() != "")
            $new = str_replace_once("{TOPIC_EDIT_INFO}", \Engine\LanguageManager::GetTranslation("newsviewer.last_edited") . " by <a href=\"profile.php?uid=" . $topic->getLastEditor() . "\">" . \Users\UserAgent::GetUserNick($topic->getLastEditor()) . "</a> ".
                \Engine\LanguageManager::GetTranslation("in") . " " . \Engine\Engine::DatetimeFormatToRead($topic->getLastEditDateTime()), $new);
        else
            $new = str_replace_once("{TOPIC_EDIT_INFO}", "", $new);
        $new = str_replace_once("{TOPIC_AUTHOR_SKYPE}", (($author->IsSkypePublic()) ? "Skype: <a href=\"skype:" . $author->getSkype() . "?chat\">" . htmlentities($author->getSkype()) . "</a><br>" : ""), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_EMAIL}", (($author->IsEmailPublic()) ? "Email: <a href=\"mailto:" . $author->getEmail() . "\">" . htmlentities(\Engine\LanguageManager::GetTranslation("newsviewer.write_to")) . "</a><br>" : ""), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_VK}", (($author->IsVKPublic()) ? "VK: <a href=\"https://vk.com/" . $author->getVK() . "\">" . htmlentities($author->getVK()) . "</a><br>" : ""), $new);
        $new = str_replace_once("{TOPIC_CONTENT}", Engine\Engine::ChatFilter(\Engine\Engine::CompileMentions(html_entity_decode(\Engine\Engine::CompileBBCode($topic->getText())))), $new);
        $new = str_replace_once("{TOPIC_FOOTER_LIKE_CLASS}", (($topic->getLikes() > $topic->getDislikes()) ? "positive" : (($topic->getDislikes() > $topic->getLikes()) ? "negative" : "")), $new);
    //First condition:
        $isAuthorized = ($user !== FALSE);
    //Second condition
        $isUserIsAuthor = ($isAuthorized && $user->getId() == $author->getId());
    //Third condition
        $permToComment = $isAuthorized && (($user->UserGroup()->getPermission("comment_create") && $topic->getCategory()->CanCreateComments()) ||
                ($user->UserGroup()->getPermission("comment_create") && !$topic->getCategory()->CanCreateComments() && $user->UserGroup()->getPermission("category_params_ignore")));
    //Other
        $hasPermToEdit = ($isAuthorized && (($isUserIsAuthor && $user->UserGroup()->getPermission("topic_edit")) || $user->UserGroup()->getPermission("topic_foreign_edit")));
        $hasPermToDelete = ($isAuthorized && (($isUserIsAuthor && $user->UserGroup()->getPermission("topic_delete")) || $user->UserGroup()->getPermission("topic_foreign_delete")));
        $hasPermToComment = ($isAuthorized && ($isUserIsAuthor || $permToComment));

        if ($hasPermToEdit) {
            $new = str_replace_once("{TOPIC_EDIT}", "<a class=\"btn btn-default\" href=\"?topic=" . $topic->getId() . "&edit\"><span class=\"glyphicons glyphicons-edit\"></span> " . \Engine\LanguageManager::GetTranslation("edit") . "</a>", $new);
        } else {
            $new = str_replace_once("{TOPIC_EDIT}", "", $new);
        }
        if ($hasPermToComment) {
            $new = str_replace_once("{TOPIC_ADD_COMMENT}", "<a class=\"btn btn-default\" href=\"#comment-content-textarea\"><span class=\"glyphicons glyphicons-comments\"></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.to_comment") . "</a>", $new);
        } else
            $new = str_replace_once("{TOPIC_ADD_COMMENT}", "", $new);
        if ($hasPermToDelete) {
            $new = str_replace_once("{TOPIC_DELETE}", "<button class=\"btn btn-default\" type=\"submit\" name=\"topic-remove-btn\"><span class=\"glyphicons glyphicons-erase\"></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.remove_topic") . "</button>", $new);
        } else
            $new = str_replace_once("{TOPIC_DELETE}", "", $new);
        $new = str_replace_once("{TOPIC_LIKES_COUNT}", $topic->getLikes(), $new);
        $new = str_replace_once("{TOPIC_MARKS_COUNT}", $topic->getMarksCount(), $new);
        $new = str_replace_once("{TOPIC_DISLIKES_COUNT}", $topic->getDislikes(), $new);
        $new = str_replace_once("{TOPIC_CONTENT}", \Engine\Engine::CompileBBCode($topic->getText()), $new);
        if ($user !== false && $topic->getStatus() == 1) {
            include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/newcomment.html";
            $newCommentForm = getBrick();
            $newCommentForm = str_replace_once("{TOPIC_ID}", $topic->getId(), $newCommentForm);
            $new = str_replace_once("{TOPIC_CREATE_COMMENT}", $newCommentForm, $new);
        } else {
            $new = str_replace_once("{TOPIC_CREATE_COMMENT}", "", $new);
        }
    }
    else {
        include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/singlecomments.html";
        $new = getBrick();
        if ($user !== false) {
            include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/newcomment.html";
            $newCommentForm = getBrick();
            $new = str_replace_once("{TOPIC_CREATE_COMMENT}", $newCommentForm, $new);
        } else {
            $new = str_replace_once("{TOPIC_CREATE_COMMENT}", "", $new);
        }
    }
    $comments = array();
    $topicComments = \Forum\ForumAgent::GetCommentsOfTopic($topic->getId(), isset($_GET["p"]) ? $_GET["p"] : 1);
    for ($i = 0; $i < count($topicComments); $i++){
        include "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/comment.html";
        $currentComment = getBrick();
        $comment = new \Forum\TopicComment($topicComments[$i]);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_NICKNAME}", $comment->author()->getNickname(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_ID}", $comment->author()->getId(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_GROUP_NAME}", $comment->author()->UserGroup()->getName(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_GROUP_ID}", $comment->author()->UserGroup()->getId(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_GROUP_COLOR}", $comment->author()->UserGroup()->getColor(), $currentComment);
        switch ($comment->author()->getSex()){
            case 1:
                $sexAuthorComment = \Engine\LanguageManager::GetTranslation("gender_male");
                break;
            case 2:
                $sexAuthorComment = \Engine\LanguageManager::GetTranslation("gender_female");
                break;
            case 0:
                $sexAuthorComment = \Engine\LanguageManager::GetTranslation("not_setted");
                break;
        }
        $currentComment = str_replace_once("{COMMENT_AUTHOR_SEX}", $sexAuthorComment, $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_REPUTATION}", $comment->author()->getReputation()->getReputationPoints(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_COMMENTS}", \Forum\ForumAgent::GetCountOfCommentOfUser($comment->getAuthorId()), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_TO_PM}", "<a class=\"pm-href\" href=\"http://tonisfeltavern.com/profile.php?page=wm&sendTo=" . $comment->author()->getNickname() . "\">" . \Engine\LanguageManager::GetTranslation("newsviewer.write_upper_case") . "</a>", $currentComment);
        $currentComment = str_replace("{COMMENT_ID}", $comment->getId(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_AVATAR}", $comment->author()->getAvatar(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_TEXT}", \Engine\Engine::CompileMentions(html_entity_decode(Engine\Engine::ChatFilter(Engine\Engine::CompileBBCode($comment->getText())))), $currentComment);
        $currentComment = str_replace_once("{COMMENT_BB_TEXT}", $comment->getText(), $currentComment);
        if ($author->getSignature() == ""){
            $signature = \Engine\LanguageManager::GetTranslation("not_setted");
        } else {
            $signature = nl2br(html_entity_decode(\Engine\Engine::ChatFilter(\Engine\Engine::CompileBBCode($author->getSignature()))));
        }
        $currentComment = str_replace_once("{COMMENT_AUTHOR_SIGNATURE}", $signature, $currentComment);
        $currentComment = str_replace_once("{COMMENT_DATETIME_CREATED}", \Engine\Engine::DatetimeFormatToRead(
            date("Y-m-d H:i:s", $comment->getCreateDatetime())
        ), $currentComment);
        if ($user !== false) {
            if (($user->UserGroup()->getPermission("comment_edit") && $comment->getAuthorId() == $user->getId()) || $user->UserGroup()->getPermission("comment_foreign_edit")) {
                $currentComment = str_replace_once("{COMMENT_EDIT_BUTTON}", "<a class=\"btn btn-default btn-comment\" href=\"index.php?topic=" . $topic->getId() . "&cedit=". $comment->getId() . "\">
                <span class=\"glyphicon glyphicon-edit\"></span> " . \Engine\LanguageManager::GetTranslation("edit") . "</a>", $currentComment);
            } else {
                $currentComment = str_replace_once("{COMMENT_EDIT_BUTTON}", "", $currentComment);
            }
            if (($user->UserGroup()->getPermission("comment_delete") && $comment->getAuthorId() == $user->getId()) || $user->UserGroup()->getPermission("comment_foreign_delete")) {
                $currentComment = str_replace_once("{COMMENT_DELETE_BUTTON}", "<button class=\"btn btn-default btn-delete\" type=\"submit\" name=\"comment-delete-btn\">
                <span class=\"glyphicon glyphicon-erase\"></span> " . \Engine\LanguageManager::GetTranslation("remove") . "</button>", $currentComment);
            } else {
                $currentComment = str_replace_once("{COMMENT_DELETE_BUTTON}", "", $currentComment);
            }
            $currentComment = str_replace_once("{COMMENT_QUOTE_BUTTON}", "<a class=\"btn btn-default btn-quote\" href=\"#comment-content-textarea\" data-comment-id=\"". $comment->getId() . "\">
                <span class=\"glyphicons glyphicons-quote\"></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.quote") . "</a>", $currentComment);
        } else {
            $currentComment = str_replace_once("{COMMENT_EDIT_BUTTON}", "", $currentComment);
            $currentComment = str_replace_once("{COMMENT_QUOTE_BUTTON}", "", $currentComment);
            $currentComment = str_replace_once("{COMMENT_DELETE_BUTTON}", "", $currentComment);
        }
        if ($comment->getChangeInfo()["editorId"] != "") {
            $userEditor = $comment->getChangeInfo()["editorId"];
            $reasonEdit = $comment->getChangeInfo()["editReason"];
            $reasonEdit = htmlentities($reasonEdit);
            $dateEdit = $comment->getChangeInfo()["editDate"];
            $currentComment = str_replace_once("{COMMENT_EDIT_INFO}", \Engine\LanguageManager::GetTranslation("newsviewer.last_edited_comment") . " by " . \Users\UserAgent::GetUserNick($userEditor) . " " .
                    \Engine\LanguageManager::GetTranslation("in") . " " . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $dateEdit)) .
                ((strlen($reasonEdit) > 0) ? \Engine\LanguageManager::GetTranslation("newsviewer.last_edited_comment_reason") . " : " . htmlentities($reasonEdit) : ""), $currentComment) . "";
            } else
                $currentComment = str_replace_once("{COMMENT_EDIT_INFO}", "", $currentComment);
        array_push($comments, $currentComment);
    }
    $new = str_replace_once("{TOPIC_COMMENTS}", implode($comments), $new);
    //Create pagination.
    $paginationButtons = "";
    $countPages = \Forum\ForumAgent::GetTotalCommentsOfTopic($topic->getId())/ 10;

    if ($countPages > 1){
        for ($i = 1; $i < $countPages; $i++) {
            $paginationButtons .= "<a class=\"btn btn-default" . (($page == $i) ? " active\"" : "\"") . " href=\"index.php?topic=" . $topic->getId() . "&p=$i\">$i</a>";
        }
        $new = str_replace_once("{TOPIC_PAGINATION}", $paginationButtons, $new);
    }
    else {
        $new = str_replace_once("{TOPIC_PAGINATION}", "", $new);
    }
    echo $new;
}
elseif (isset($_GET["edit"])) {
    if (($user->getId() == $author->getId() && $user->UserGroup()->getPermission("topic_edit")) || $user->UserGroup()->getPermission("topic_foreign_edit")) {
        $pageName = \Engine\LanguageManager::GetTranslation("newsviewer.editor_topic");

        include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/newedit.html";
        $editor = getBrick();

        $editor = str_replace("{TOPIC_NAME}", $topic->getName(), $editor);
        $editor = str_replace_once("{TOPIC_ID}", $topic->getId(), $editor);

        switch ($_GET["res"]) {
            case "3nsc":
                $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.not_setted_category") . "</div>";
                break;
            case "3np":
                $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.not_permitted_category") . "</div>";
                break;
            case "3ntltn":
                $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.invalid_topic_name") . "</div>";
                break;
            case "3ntlm":
                $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.invalid_topic_text") . "</div>";
                break;
            case "3ncct":
                $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.topic_edit_error") . "</div>";
                break;
            default:
                $creatorResponse = "";
                break;
        }

        $editor = str_replace_once("{TOPIC_ERRORS}", $editorResponse, $editor);

        $categoriesList = "";
        foreach ($categories as $c) {
            $category = new \Forum\Category($c);
            if ($topic->getCategoryId() == $category->getId())
                $atr = " selected";
            else
                $atr = "";
            if ($category->isPublic() || (!$category->isPublic() && $user->UserGroup()->getPermission("category_see_unpublic")))
                $categoriesList .= "<option value=\"" . $category->getId() . "\"$atr>" . $category->getName() . "</option>";
        }
        $editor = str_replace_once("{TOPIC_PAGE:CATEGORIES_OPTION}", $categoriesList, $editor);
        $selectorAtr = "";
        if (!$user->UserGroup()->getPermission("topic_manage"))
            $selectorAtr = "disabled";
        $editor = str_replace_once("{TOPIC_DISABLED_PROPERTY}", $selectorAtr, $editor);
        $editor = str_replace_once("{TOPIC_DISABLED_STATUS_PROPERTY}", $selectorAtr, $editor);
        $editor = str_replace_once("{TOPIC_PREVIEW_TEXT}", \Engine\Engine::MakeUnactiveCodeWords($topic->getPretext()), $editor);
        $editor = str_replace_once("{TOPIC_CONTENT_TEXT}", \Engine\Engine::MakeUnactiveCodeWords($topic->getText()), $editor);
        $isNotClosed = "<option value=\"1\"" . (($topic->getStatus() == 1) ? " selected" : "") . ">" . \Engine\LanguageManager::GetTranslation("newsviewer.open") . "</option>";
        $isClosed = "<option value=\"0\"" . (($topic->getStatus() == 0) ? " selected" : "") . ">" . \Engine\LanguageManager::GetTranslation("newsviewer.close") . "</option>";
        $editor = str_replace_once("{TOPIC_PAGE:STATUS_OPTIONS}", $isNotClosed . $isClosed, $editor);
        echo $editor;

    }
}
elseif (isset($_GET["cedit"])) {
    $comment = new \Forum\TopicComment($_GET["cedit"]);
    if (($user->getId() == $comment->getAuthorId() && $user->UserGroup()->getPermission("comment_edit")) || $user->UserGroup()->getPermission("comment_foreign_edit")) {
        $pageName = \Engine\LanguageManager::GetTranslation("newsviewer.editor_comment");
        include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/commentedit.html";
        $editor = getBrick();

        $editor = str_replace_once("{COMMENT_ID}", $comment->getId(), $editor);
        $editor = str_replace_once("{COMMENT_TEXT}", \Engine\Engine::MakeUnactiveCodeWords($comment->getText()), $editor);

        $errors = "";
        if ($_GET["res"] == "3ntlc") {
            $errors = "<div class=\"alert alert-danger\"><span class='glyphicons glyphicons-remove'></span> " . \Engine\LanguageManager::GetTranslation("newsviewer.comment_is_too_short") . "</div>";
        }
        $editor = str_replace_once("{COMMENT_ERRORS}", $errors, $editor);

        echo $editor;
    }
}