<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if (isset($_POST["category-add-btn"])){
    if ($user->UserGroup()->getPermission("category_create")){
        if (empty($_POST["category-add-name"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6ncn");
            exit;
        }
        if (empty($_POST["category-add-description"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6ncd");
            exit;
        }
        if (strlen($_POST["category-add-name"]) < 4 || strlen($_POST["category-add-name"]) > 50){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6nvcn");
            exit;
        }
        if (strlen($_POST["category-add-description"]) < 4 || strlen($_POST["category-add-description"]) > 350){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6nvcd");
            exit;
        }

        $result = \Forum\ForumAgent::CreateCategory($_POST["category-add-name"], $_POST["category-add-description"],
            $_POST["category_add_keywords"],
            (isset($_POST["category_add_public"])) ? 1 : 0,
            (isset($_POST["category_add_nocomments"])) ? 1 : 0,
            (isset($_POST["category_add_notopics"])) ? 1 : 0);
        if ($result > 0){
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.create_new_category_log") . "\"" . $_POST["category-add-name"] . "\"");
            header("Location: ../../adminpanel.php?p=categories&res=6scc");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=categories&res=6ncc");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_POST["category_edit_btn"])){
    if ($user->UserGroup()->getPermission("category_edit")){
        if (!empty($_GET["cid"])) {
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&cid=" . $_GET["cid"]);
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_POST["category_edit_save"])){
    if ($user->UserGroup()->getPermission("category_edit")){
        if (empty($_GET["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }

        $category = new \Forum\Category($_GET["cid"]);
        if ($category == 32){
            header("Location: ../../adminpanel.php?p=categories&res=6nct");
            exit;
        }

        if (empty($_POST["category_edit_name"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6ncn&cid=" . $category->getId());
            exit;
        }
        if (empty($_POST["category_edit_descript"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6ncd&cid=" . $category->getId());
            exit;
        }
        if (strlen($_POST["category_edit_name"]) < 4 || strlen($_POST["category_edit_name"]) > 50 ){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6nvcn&cid=" . $category->getId());
            exit;
        }
        if (strlen($_POST["category_edit_descript"]) < 4 || strlen($_POST["category_edit_descript"]) > 350 ){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6nvcd&cid=" . $category->getId());
            exit;
        }

        if ($category->getName() != $_POST["category-edit-name"]) {
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.rename_category_log") ."[" . $category->getName() . " -> " . $_POST["category-edit-name"] . "]");
            \Forum\ForumAgent::ChangeCategoryParams($_GET["cid"], "name", $_POST["category_edit_name"]);
        }
        if ($category->getDescription() != $_POST["category-edit-descript"]) {
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.change_description_log") . "[" . $category->getName() . " " . $category->getDescription() . " -> " . $_POST["category-edit-descript"] . "]");
            \Forum\ForumAgent::ChangeCategoryParams($_GET["cid"], "descript", $_POST["category_edit_descript"]);
        }
        if ($category->isPublic() != $_POST["category_edit_public_checker"]) {
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.public_category_change_log_log") . "\"" . $category->getName() . "\" [" . $category->isPublic() . " -> " . (isset($_POST["category_edit_public_checker"]) ? "1" : "0") . "]");
            \Forum\ForumAgent::ChangeCategoryParams($_GET["cid"], "public", (isset($_POST["category_edit_public_checker"])) ? "1" : "0");
        }
        if ($category->CanCreateComments() != $_POST["category_edit_nocomments_checker"]) {
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.change_perm_for_comment_create_log") ."\"" . $category->getName() . "\" "
                . $category->CanCreateComments() . " -> " . (isset($_POST["category_edit_nocomments_checker"]) ? "1" : "0"));
            \Forum\ForumAgent::ChangeCategoryParams($_GET["cid"], "no_comment", (isset($_POST["category_edit_nocomments_checker"])) ? "1" : "0");
        }
        if ($category->CanCreateTopic() != $_POST["category_edit_notopics_checker"]) {
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.change_perm_for_topic_create_log") ."\"" . $category->getName() . "\" "
                . $category->CanCreateTopic() . " -> " . $_POST["category_edit_notopics_checker"]);
            \Forum\ForumAgent::ChangeCategoryParams($_GET["cid"], "no_new_topics", (isset($_POST["category_edit_notopics_checker"])) ? "1" : "0");
        }
        if ($category->getKeyWords() != $_POST["category_edit_keywords"]) {
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.change_keywords_log") ."\"" . $category->getName() . "\" "
                . $category->CanCreateTopic() . " -> " . $_POST["category_edit_notopics_checker"]);
            \Forum\ForumAgent::ChangeCategoryParams($_GET["cid"], "keywords", $_POST["category_edit_keywords"]);
        }

        header("Location: ../../adminpanel.php?p=categories&res=6sce");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_POST["category_edit_delete"])){
    if ($user->UserGroup()->getPermission("category_delete")){
        if (empty($_GET["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }
        $categoryName = \Forum\ForumAgent::GetCategoryParam($_GET["cid"], "name");
        $result = \Forum\ForumAgent::DeleteCategory($_GET["cid"]);
        if ($result === TRUE){
            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.remove_category_log") ."\"$categoryName\".");
            header("Location: ../../adminpanel.php?p=categories&res=6scdt");
            exit;
        }
        elseif ($result == 32) {
            if (empty($_GET["cid"])){
                header("Location: ../../adminpanel.php?p=categories&res=6ntc");
                exit;
            }
        } else {
            if (empty($_GET["cid"])){
                header("Location: ../../adminpanel.php?p=categories&res=6ncdt&reqtype=2&cid=" . $_GET["cid"]);
                exit;
            }
        }

    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_POST["categories-table-delete"])){
    if ($user->UserGroup()->getPermission("category_delete")){
        if (empty($_GET["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }

        $cids = explode(",", $_GET["cid"]);
        for ($y = 0; $y <= count($cids)-1; $y++){
            $categoryName = \Forum\ForumAgent::GetCategoryParam($_GET["cid"], "name");
            $result = \Forum\ForumAgent::DeleteCategory($_GET["cid"]);
            if ($result === TRUE){
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("categories_panel.remove_category_log") ."\"$categoryName\".");
                continue;
            }
            elseif ($result == 32) {
                if (empty($_GET["cid"])){
                    header("Location: ../../adminpanel.php?p=categories&res=6ntc");
                    exit;
                }
            } else {
                if (empty($_GET["cid"])){
                    header("Location: ../../adminpanel.php?p=categories&res=6ncdt&reqtype=2&cid=" . $_GET["cid"]);
                    exit;
                }
            }
        }
        header("Location: ../../adminpanel.php?p=categories&res=6scdt");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

header("Location: ../../adminpanel.php?p=forbidden");
exit;