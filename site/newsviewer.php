<?php
$topic = new \Forum\Topic($_GET["topic"]);
$author = $topic->getAuthor();

if (!isset($_GET["edit"])) {
    $pageName = $topic->getName();
    include_once "templates/" . \Engine\Engine::GetEngineInfo("stp") . "/news/new.html";
    $new = getBrick();
    if (isset($_GET["res"]) && $_GET["res"] == "3ndt")
        $new = str_replace_once("{TOPIC_DELETE_ERROR}", "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-remove\"></span> Не удалось удалить тему.</div>", $new);
    $new = str_replace_once("{TOPIC_DELETE_ERROR}", "", $new);
    $new = str_replace("{TOPIC_ID}", $topic->getId(), $new);
    $new = str_replace_once("{TOPIC_HEADER}", $topic->getName(), $new);
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
        $new = str_replace_once("{TOPIC_EDIT_INFO}", "Последнее редактирование by <a href=\"profile.php?uid=". $topic->getLastEditor() . "\">" . \Users\UserAgent::GetUserNick($topic->getLastEditor()) . "</a> в "
        . \Engine\Engine::DatetimeFormatToRead($topic->getLastEditDateTime()), $new);
    else
        $new = str_replace_once("{TOPIC_EDIT_INFO}", "", $new);
    $new = str_replace_once("{TOPIC_AUTHOR_SKYPE}", (($author->IsSkypePublic()) ? "Skype: <a href=\"skype:" . $author->getSkype() . "?chat\">" . $author->getSkype() . "</a><br>" : ""), $new);
    $new = str_replace_once("{TOPIC_AUTHOR_EMAIL}", (($author->IsEmailPublic()) ? "Email: <a href=\"mailto:" . $author->getEmail . "\">написать</a><br>" : ""), $new);
    $new = str_replace_once("{TOPIC_AUTHOR_VK}", (($author->IsVKPublic()) ? "VK: <a href=\"https://vk.com/" . $author->getVK() . "\">" . $author->getVK() . "</a><br>" : ""), $new);
    $new = str_replace_once("{TOPIC_CONTENT}", \Engine\Engine::CompileBBCode($topic->getText()), $new);
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
        $new = str_replace_once("{TOPIC_ADD_COMMENT}", "<button class=\"btn btn-default\" type=\"button\"><span class=\"glyphicons glyphicons-comments\"></span> Комментировать</button>", $new);
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

    echo $new;
}
else {
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
    $editor = str_replace_once("{TOPIC_PREVIEW_TEXT}", $topic->getPretext(), $editor);
    $editor = str_replace_once("{TOPIC_CONTENT_TEXT}", $topic->getText(), $editor);
    echo $editor;
}