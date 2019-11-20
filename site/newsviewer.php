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
                    $info = "<div class=\"alert alert-success\"><span class='glyphicon glyphicon-ok'></span> Тема успешно отредактированна.</div>";
                    break;
                case "3sdc":
                    $info = "<div class=\"alert alert-success\"><span class='glyphicon glyphicon-ok'></span> Комментариий был успешно удалён.</div>";
                    break;
                case "3ndc":
                    $info = "<div class=\"alert alert-danger\"><span class='glyphicon glyphicon-remove'></span> Не удалось удалить комментарий.</div>";
                    break;
                case "3scc":
                    $info = "<div class=\"alert alert-success\"><span class='glyphicon glyphicon-ok'></span> Комментариий был успешно создан.</div>";
                    break;
                case "3sec":
                    $info = "<div class=\"alert alert-success\"><span class='glyphicon glyphicon-ok'></span> Комментариий был успешно отредактирован!</div>";
                    break;
                case "3npec":
                    $info = "<div class=\"alert alert-danger\"><span class='glyphicon glyphicon-remove'></span> У Вас недостаточно прав для редактирования данного комментария.</div>";
                    break;
            }
        }
        $new = str_replace_once("{TOPIC_DELETE_ERROR}", $info, $new);
        $new = str_replace("{TOPIC_ID}", $topic->getId(), $new);
        $new = str_replace_once("{TOPIC_HEADER}", (($topic->getStatus() == 0) ? "<span class=\"glyphicons glyphicons-lock\"></span> " : "" ) . $topic->getName(), $new);
        $new = str_replace_once("{TOPIC_CATEGORY_ID}", $topic->getCategoryId(), $new);
        $new = str_replace_once("{TOPIC_CATEGORY}", $topic->getCategory()->getName(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_AVATAR}", $author->getAvatar(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_NICKNAME}", $author->getNickname(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_GROUP_COLOR}", $author->UserGroup()->getColor(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_GROUP}", $author->UserGroup()->getName(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_ID}", $topic->getAuthorId(), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_LAST_ONLINE}", ((\Engine\Engine::GetSiteTime() > $author->getLastTime() + 15 * 60) ? "заходил" . (($author->getSex() == 2) ? "а" : "")
            . " в " . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $author->getLastTime())) : "<span style=\"color: #00dd00;\">онлайн</span>"), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_SEX}", "Пол: " . (($author->getSex() == 2) ? "<span class=\"glyphicons glyphicons-gender-female\"></span> женский"
                : ($author->getSex() == 1)
                    ? "<span class=\"glyphicons glyphicons-gender-male\"></span> мужской" : "") . "<br>", $new);
        $new = str_replace_once("{TOPIC_AUTHOR_REGDATE}", (($author->getSex() == 2) ? "Зарегистрирована " : "Зарегистрирован ") . \Engine\Engine::DateFormatToRead($author->getRegDate()), $new);
        $new = str_replace_once("{TOPICS_AUTHOR_COUNT}", \Forum\ForumAgent::GetCountTopicOfAuthor($topic->getAuthorId()), $new);
        $new = str_replace_once("{TOPICS_AUTHOR_RATE}", $author->getReputation()->getReputationPoints(), $new);
        $new = str_replace_once("{TOPIC_CREATE_DATETIME}", \Engine\Engine::DatetimeFormatToRead($topic->getCreateDate()), $new);
        if ($topic->getLastEditor() != "")
            $new = str_replace_once("{TOPIC_EDIT_INFO}", "Последнее редактирование by <a href=\"profile.php?uid=" . $topic->getLastEditor() . "\">" . \Users\UserAgent::GetUserNick($topic->getLastEditor()) . "</a> в "
                . \Engine\Engine::DatetimeFormatToRead($topic->getLastEditDateTime()), $new);
        else
            $new = str_replace_once("{TOPIC_EDIT_INFO}", "", $new);
        $new = str_replace_once("{TOPIC_AUTHOR_SKYPE}", (($author->IsSkypePublic()) ? "Skype: <a href=\"skype:" . $author->getSkype() . "?chat\">" . $author->getSkype() . "</a><br>" : ""), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_EMAIL}", (($author->IsEmailPublic()) ? "Email: <a href=\"mailto:" . $author->getEmail() . "\">написать</a><br>" : ""), $new);
        $new = str_replace_once("{TOPIC_AUTHOR_VK}", (($author->IsVKPublic()) ? "VK: <a href=\"https://vk.com/" . $author->getVK() . "\">" . $author->getVK() . "</a><br>" : ""), $new);
        $new = str_replace_once("{TOPIC_CONTENT}", \Engine\Engine::CompileMentions(html_entity_decode(\Engine\Engine::CompileBBCode($topic->getText()))), $new);
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
            $new = str_replace_once("{TOPIC_EDIT}", "<a class=\"btn btn-default\" href=\"?topic=" . $topic->getId() . "&edit\"><span class=\"glyphicons glyphicons-edit\"></span> Редактировать</a>", $new);
        } else {
            $new = str_replace_once("{TOPIC_EDIT}", "", $new);
        }
        if ($hasPermToComment) {
            $new = str_replace_once("{TOPIC_ADD_COMMENT}", "<a class=\"btn btn-default\" href=\"#comment-content-textarea\"><span class=\"glyphicons glyphicons-comments\"></span> Комментировать</a>", $new);
        } else
            $new = str_replace_once("{TOPIC_ADD_COMMENT}", "", $new);
        if ($hasPermToDelete) {
            $new = str_replace_once("{TOPIC_DELETE}", "<button class=\"btn btn-default\" type=\"submit\" name=\"topic-remove-btn\"><span class=\"glyphicons glyphicons-erase\"></span> Удалить тему</button>", $new);
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
        $currentComment = str_replace_once("{COMMENT_AUTHOR_GROUP_COLOR}", $comment->author()->UserGroup()->getColor(), $currentComment);
        switch ($comment->author()->getSex()){
            case 1:
                $sexAuthorComment = "мужской";
                break;
            case 2:
                $sexAuthorComment = "женский";
                break;
            case 0:
                $sexAuthorComment = "не указан";
                break;
        }
        $currentComment = str_replace_once("{COMMENT_AUTHOR_SEX}", $sexAuthorComment, $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_REPUTATION}", $comment->author()->getReputation()->getReputationPoints(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_COMMENTS}", \Forum\ForumAgent::GetCountOfCommentOfUser($comment->getAuthorId()), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_TO_PM}", "<a class=\"pm-href\" href=\"http://tonisfeltavern.com/profile.php?page=wm&sendTo=" . $comment->author()->getNickname() . "\">Написать...</a>", $currentComment);
        $currentComment = str_replace("{COMMENT_ID}", $comment->getId(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_AVATAR}", $comment->author()->getAvatar(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_TEXT}", \Engine\Engine::CompileMentions(html_entity_decode(Engine\Engine::CompileBBCode($comment->getText()))), $currentComment);
        $currentComment = str_replace_once("{COMMENT_BB_TEXT}", $comment->getText(), $currentComment);
        $currentComment = str_replace_once("{COMMENT_AUTHOR_SIGNATURE}", html_entity_decode(\Engine\Engine::CompileBBCode($author->getSignature())), $currentComment);
        $currentComment = str_replace_once("{COMMENT_DATETIME_CREATED}", \Engine\Engine::DatetimeFormatToRead(
            date("Y-m-d H:i:s", $comment->getCreateDatetime())
        ), $currentComment);
        if ($user !== false) {
            if (($user->UserGroup()->getPermission("comment_edit") && $comment->getAuthorId() == $user->getId()) || $user->UserGroup()->getPermission("comment_foreign_edit")) {
                $currentComment = str_replace_once("{COMMENT_EDIT_BUTTON}", "<a class=\"btn btn-default btn-comment\" href=\"index.php?topic=" . $topic->getId() . "&cedit=". $comment->getId() . "\">
                <span class=\"glyphicon glyphicon-edit\"></span> Редактировать</a>", $currentComment);
            } else {
                $currentComment = str_replace_once("{COMMENT_EDIT_BUTTON}", "", $currentComment);
            }
            if (($user->UserGroup()->getPermission("comment_delete") && $comment->getAuthorId() == $user->getId()) || $user->UserGroup()->getPermission("comment_foreign_delete")) {
                $currentComment = str_replace_once("{COMMENT_DELETE_BUTTON}", "<button class=\"btn btn-default btn-delete\" type=\"submit\" name=\"comment-delete-btn\">
                <span class=\"glyphicon glyphicon-erase\"></span> Удалить</button>", $currentComment);
            } else {
                $currentComment = str_replace_once("{COMMENT_DELETE_BUTTON}", "", $currentComment);
            }
            $currentComment = str_replace_once("{COMMENT_QUOTE_BUTTON}", "<a class=\"btn btn-default btn-quote\" href=\"#comment-content-textarea\" data-comment-id=\"". $comment->getId() . "\">
                <span class=\"glyphicons glyphicons-quote\"></span> Цитировать</a>", $currentComment);
        } else {
            $currentComment = str_replace_once("{COMMENT_EDIT_BUTTON}", "", $currentComment);
            $currentComment = str_replace_once("{COMMENT_QUOTE_BUTTON}", "", $currentComment);
            $currentComment = str_replace_once("{COMMENT_DELETE_BUTTON}", "", $currentComment);
        }
        if ($comment->getChangeInfo()["editorId"] != "") {
            $userEditor = $comment->getChangeInfo()["editorId"];
            $reasonEdit = $comment->getChangeInfo()["editReason"];
            $dateEdit = $comment->getChangeInfo()["editDate"];
            $currentComment = str_replace_once("{COMMENT_EDIT_INFO}", "Отредактировано by " . \Users\UserAgent::GetUserNick($userEditor) . " в " . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $dateEdit)) .
                ((strlen($reasonEdit) > 0) ? " по причине: " . $reasonEdit : ""), $currentComment) . "";
            } else
                $currentComment = str_replace_once("{COMMENT_EDIT_INFO}", "", $currentComment);
        array_push($comments, $currentComment);
    }
    $new = str_replace_once("{TOPIC_COMMENTS}", implode($comments), $new);
    //Создание пагинации.
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
    $pageName = "Редактор тем";
    include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/newedit.html";
    $editor = getBrick();

    $editor = str_replace("{TOPIC_NAME}", $topic->getName(), $editor);
    $editor = str_replace_once("{TOPIC_ID}", $topic->getId(), $editor);

    switch($_GET["res"]){
        case "3nsc":
            $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Вы не выбрали категорию.</div>";
            break;
        case "3np":
            $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> У Вас недостаточно прав для взаимодействия с данной категорией.</div>";
            break;
        case "3ntltn":
            $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Название темы неправильной длины. Оно должно быть больше 4 символов и меньше 100.</div>";
            break;
        case "3ntlm":
            $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Текст темы слишком короткий. Он должен быть длиннее 15 символов и нести в себе смысловую нагрузку.</div>";
            break;
        case "3ncct":
            $editorResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Не удалось создать тему. Обратитесь к Администратору.</div>";
            break;
        default:
            $creatorResponse = "";
            break;
    }

    $editor = str_replace_once("{TOPIC_ERRORS}", $editorResponse, $editor);

    $categoriesList = "";
    foreach ($categories as $c){
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
    $editor = str_replace_once("{TOPIC_PREVIEW_TEXT}", $topic->getPretext(), $editor);
    $editor = str_replace_once("{TOPIC_CONTENT_TEXT}", $topic->getText(), $editor);
    $isNotClosed = "<option value=\"1\"" . (($topic->getStatus() == 1) ? " selected" : "") . ">Открыта</option>";
    $isClosed = "<option value=\"0\"" . (($topic->getStatus() == 0) ? " selected" : "") . ">Закрыта</option>";
    $editor = str_replace_once("{TOPIC_PAGE:STATUS_OPTIONS", $isNotClosed . $isClosed, $editor);
    echo $editor;


}
elseif (isset($_GET["cedit"])){
    $pageName = "Редактор комментария";
    include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/commentedit.html";
    $editor = getBrick();
    $comment = new \Forum\TopicComment($_GET["cedit"]);

    $editor = str_replace_once("{COMMENT_ID}", $comment->getId(), $editor);
    $editor = str_replace_once("{COMMENT_TEXT}", $comment->getText(), $editor);

    $errors = "";
    if ($_GET["res"] == "3ntlc"){
        $errors = "<div class=\"alert alert-danger\"><span class='glyphicons glyphicons-remove'></span> Текст комментария не может быть меньше 4 символов.</div>";
    }
    $editor = str_replace_once("{COMMENT_ERRORS}", $errors, $editor);

    echo $editor;
}