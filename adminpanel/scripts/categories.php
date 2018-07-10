<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (isset($_REQUEST["category-add-btn"])){
    if ($user->UserGroup()->getPermission("category_create")){
        if (empty($_REQUEST["category-add-name"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6ncn");
            exit;
        }
        if (empty($_REQUEST["category-add-description"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6ncd");
            exit;
        }
        if (strlen($_REQUEST["category-add-name"]) < 4 || strlen($_REQUEST["category-add-name"]) > 50){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6nvcn");
            exit;
        }
        if (strlen($_REQUEST["category-add-description"]) < 4 || strlen($_REQUEST["category-add-description"]) > 350){
            header("Location: ../../adminpanel.php?p=categories&reqtype=1&res=6nvcd");
            exit;
        }

        $result = \Forum\ForumAgent::CreateCategory($_REQUEST["category-add-name"], $_REQUEST["category-add-description"],
            (isset($_REQUEST["category_add_public"])) ? 1 : 0,
            (isset($_REQUEST["category_add_nocomments"])) ? 1 : 0,
            (isset($_REQUEST["category_add_notopics"])) ? 1 : 0);
        if ($result === TRUE){
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

if (isset($_REQUEST["category_edit_btn"])){
    if ($user->UserGroup()->getPermission("category_edit")){
        if (!empty($_REQUEST["cid"])) {
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&cid=" . $_REQUEST["cid"]);
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

if (isset($_REQUEST["category_edit_save"])){
    if ($user->UserGroup()->getPermission("category_edit")){
        if (empty($_REQUEST["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }

        $category = new \Forum\Category($_REQUEST["cid"]);
        if ($category == 32){
            header("Location: ../../adminpanel.php?p=categories&res=6nct");
            exit;
        }

        if (empty($_REQUEST["category_edit_name"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6ncn&cid=" . $category->getId());
            exit;
        }
        if (empty($_REQUEST["category_edit_descript"])){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6ncd&cid=" . $category->getId());
            exit;
        }
        if (strlen($_REQUEST["category_edit_name"]) < 4 || strlen($_REQUEST["category_edit_name"]) > 50 ){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6nvcn&cid=" . $category->getId());
            exit;
        }
        if (strlen($_REQUEST["category_edit_descript"]) < 4 || strlen($_REQUEST["category_edit_descript"]) > 350 ){
            header("Location: ../../adminpanel.php?p=categories&reqtype=2&res=6nvcd&cid=" . $category->getId());
            exit;
        }

        \Forum\ForumAgent::ChangeCategoryParams($_REQUEST["cid"], "name", $_REQUEST["category_edit_name"]);
        \Forum\ForumAgent::ChangeCategoryParams($_REQUEST["cid"], "descript", $_REQUEST["category_edit_descript"]);
        \Forum\ForumAgent::ChangeCategoryParams($_REQUEST["cid"], "public", (isset($_REQUEST["category_edit_public_checker"])) ? "1" : "0");
        \Forum\ForumAgent::ChangeCategoryParams($_REQUEST["cid"], "no_comment", (isset($_REQUEST["category_edit_nocomments_checker"])) ? "1" : "0");
        \Forum\ForumAgent::ChangeCategoryParams($_REQUEST["cid"], "no_new_topics", (isset($_REQUEST["category_edit_notopics_checker"])) ? "1" : "0");

        header("Location: ../../adminpanel.php?p=categories&res=6sce");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_REQUEST["category_edit_delete"])){
    if ($user->UserGroup()->getPermission("category_delete")){
        if (empty($_REQUEST["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }

        $result = \Forum\ForumAgent::DeleteCategory($_REQUEST["cid"]);
        if ($result === TRUE){
            header("Location: ../../adminpanel.php?p=categories&res=6scdt");
            exit;
        }
        elseif ($result == 32) {
            if (empty($_REQUEST["cid"])){
                header("Location: ../../adminpanel.php?p=categories&res=6ntc");
                exit;
            }
        } else {
            if (empty($_REQUEST["cid"])){
                header("Location: ../../adminpanel.php?p=categories&res=6ncdt&reqtype=2&cid=" . $_REQUEST["cid"]);
                exit;
            }
        }

    } else {
        header("Location: ../../adminpanel.php?p=categories&res=1");
        exit;
    }
}

if (isset($_REQUEST["categories-table-delete"])){
    if ($user->UserGroup()->getPermission("category_delete")){
        if (empty($_REQUEST["cid"])){
            header("Location: ../../adminpanel.php?p=categories&res=6ncid");
            exit;
        }

        $cids = explode(",", $_REQUEST["cid"]);
        for ($y = 0; $y <= count($cids)-1; $y++){
            $result = \Forum\ForumAgent::DeleteCategory($_REQUEST["cid"]);
            if ($result === TRUE){
                continue;
            }
            elseif ($result == 32) {
                if (empty($_REQUEST["cid"])){
                    header("Location: ../../adminpanel.php?p=categories&res=6ntc");
                    exit;
                }
            } else {
                if (empty($_REQUEST["cid"])){
                    header("Location: ../../adminpanel.php?p=categories&res=6ncdt&reqtype=2&cid=" . $_REQUEST["cid"]);
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