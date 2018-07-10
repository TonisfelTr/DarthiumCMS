<?php
require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();
/* Errors code:
 * 3s - all changes have been saved [successfuly].
 * 3se - group [edition] has been saved [successfuly].
 * 3spc - [permissions] have been [changed] [successfuly].
 * 3sgc - [group] has been [created] [successfuly].
 * 3sgd - [group] has been [deleted] [successfuly].
 * 3nlfs - groupname has [less] then [four] [symbols] of itself.
 * 3nmfts - groupname has [more] then [fif[ty]] [symbols] of itself.
 * 3ne - group [edition] is [not] permitted.
 * 3npc - group [permissions] [change] is [not] permitted.
 * 3ngc - [group] has [not] been [created].
 * 3ngd - [group] has [not] been [deleted].
 * 3ndd - group [delete] is [denied].
 * 3ngs - [group] is [not] [selected].
 * 3ngmm - [group] [member] have [not] been [moved].
 * 3ngsd - [standard] [group] can [not] been [deleted].
 */
if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }
if ((!$user->UserGroup()->getPermission("group_create")) &&
    (!$user->UserGroup()->getPermission("group_change")) &&
    (!$user->UserGroup()->getPermission("group_delete")) &&
    (!$user->UserGroup()->getPermission("change_perms"))) { header("Location: ../../adminpanel.php&&res=1"); exit; } else {
    if (isset($_REQUEST["save_perms_button"])) {
        if ($user->UserGroup()->getPermission("change_perms")) {
            //Custom engine perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "enterpanel", $_REQUEST["permadminpanel"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "change_engine_settings", $_REQUEST["change_engine_settings"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "change_design", $_REQUEST["change_design"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "look_statistic", $_REQUEST["look_statistic"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "offline_visiter", $_REQUEST["offline_visiter"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "rules_edit", $_REQUEST["rules_edit"]);

            //Custom groups perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "change_perms", $_REQUEST["permchangeperms"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "group_create", $_REQUEST["permgroupcreate"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "group_delete", $_REQUEST["permgroupdelete"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "group_change", $_REQUEST["permgroupchange"]);

            //Custom users perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "user_add", $_REQUEST["user_add"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "user_remove", $_REQUEST["user_remove"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "user_see_foreign", $_REQUEST["user_see_foreign"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "change_profile", $_REQUEST["change_profile"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "change_another_profiles", $_REQUEST["change_another_profiles"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "change_user_group", $_REQUEST["cug"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "user_signs", $_REQUEST["user_signs"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "user_ban", $_REQUEST["user_ban"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "user_unban", $_REQUEST["user_unban"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "user_banip", $_REQUEST["user_banip"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "user_unbanip", $_REQUEST["user_unbanip"]);

            //Custom reports perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_create", $_REQUEST["report_create"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_remove", $_REQUEST["report_remove"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_foreign_remove", $_REQUEST["report_foreign_remove"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_edit", $_REQUEST["report_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_foreign_edit", $_REQUEST["report_foreign_remove"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_close", $_REQUEST["report_close"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_talking", $_REQUEST["report_talking"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_answer_edit", $_REQUEST["report_answer_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "report_foreign_answer_edit", $_REQUEST["report_foreign_answer_edit"]);

            //Custom uploads perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "upload_add", $_REQUEST["upload-add"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "upload_delete", $_REQUEST["upload-delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "upload_delete_foreign", $_REQUEST["upload-delete-foreign"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "upload_see_all", $_REQUEST["upload_see_all"]);

            //Custom categories perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "category_create", $_REQUEST["category_create"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "category_edit", $_REQUEST["category_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "category_delete", $_REQUEST["category_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "category_see_unpublic", $_REQUEST["category_see_unpublic"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "category_params_ignore", $_REQUEST["category_params_ignore"]);

            //Custom topics perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "topic_create", $_REQUEST["topic_create"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "topic_edit", $_REQUEST["topic_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "topic_foreign_edit", $_REQUEST["topic_foreign_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "topic_delete", $_REQUEST["topic_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "topic_foreign_delete", $_REQUEST["topic_foreign_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "topic_manage", $_REQUEST["topic_manage"]);

            //Custom comments perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "comment_create", $_REQUEST["comment_create"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "comment_edit", $_REQUEST["comment_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "comment_foreign_edit", $_REQUEST["comment_foreign_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "comment_delete", $_REQUEST["comment_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "comment_foreign_delete", $_REQUEST["comment_foreign_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "comment_commend", $_REQUEST["comment_commend"]);

            //Perms for manage static content of site.
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "sc_create_pages", $_REQUEST["sc_create_pages"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "sc_edit_pages", $_REQUEST["sc_edit_pages"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "sc_remove_pages", $_REQUEST["sc_remove_pages"]);
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "sc_design_edit", $_REQUEST["sc_design_edit"]);

            //Custom bot mail perms
            \Users\GroupAgent::ChangeGroupPerms($_REQUEST["group"], "bmail_sende", $_REQUEST["bmail_sende"]);
        } else { header("Location: ../../adminpanel.php?res=3npc&p=groups&visible&group=" . $_REQUEST["group"]); exit; }
        { header("Location: ../../adminpanel.php?p=groups&res=3spc&visible&group=" . $_REQUEST["group"]); exit; }

    }

    if (isset($_REQUEST["add_group_button"])) {
        if (!$user->UserGroup()->getPermission("group_create")) { header("Location: ../../adminpanel.php?p=groups&res=1"); exit; }
        else {
            if (\Users\GroupAgent::AddGroup($_REQUEST["groupname_create"], $_REQUEST["groupcolor_create"], $_REQUEST["groupsubscribe_create"]) === True)
                { header("Location: ../../adminpanel.php?p=groups&res=3sgc"); exit; }
            else { header("Location: ../../adminpanel.php?p=groups&res=3ngc"); exit; }
        }
    }

    if (isset($_REQUEST["delete_group_button"])) {
        if (!$user->UserGroup()->getPermission("group_delete")) { header("Location: ../../adminpanel.php?p=groups&res=1"); exit; }
        else {
            if ($_REQUEST["group"] == 1 || $_REQUEST["group"] == 2 || $_REQUEST["group"] == 3){ { header("Location: ../../adminpanel.php?p=groups&res=3ndd&group=".$_REQUEST["group"]."&visible"); exit; } }
            elseif ($_REQUEST["group"] == \Engine\Engine::GetEngineInfo("sg")){ { header("Location: ../../adminpanel.php?p=groups&res=3ngsd&group=".$_REQUEST["group"]."&visible"); exit; } }
            else {
                if (\Users\GroupAgent::MoveGroupMembers($_REQUEST["group"], \Engine\Engine::GetEngineInfo("sg"))) {
                    if (\Users\GroupAgent::RemoveGroup($_REQUEST["group"]) === True) { header("Location: ../../adminpanel.php?p=groups&res=3sgd"); exit; }
                    else { header("Location: ../../adminpanel.php?p=groups&res=3ngmm"); exit; }
                }
            }
        }
    }

    if (isset($_REQUEST["save_group_button"])) {
        if ($user->UserGroup()->getPermission("group_change")) {
            if (strlen(utf8_decode($_REQUEST["groupname"])) <= 4) {
                \Engine\ErrorManager::GenerateError(15);
                { header("Location: ../../adminpanel.php?p=groups&res=3nlfs&visible&group=" . $_REQUEST["group"]); exit; }
            } elseif (strlen(utf8_decode($_REQUEST["groupname"])) >= 50) {
                \Engine\ErrorManager::GenerateError(16);
                { header("Location: ../../adminpanel.php?p=groups&res=3nmfts&visible&group=" . $_REQUEST["group"]); exit; }
            } else {
                \Users\GroupAgent::ChangeGroupData($_REQUEST["group"], "name", $_REQUEST["groupname"]);
                \Users\GroupAgent::ChangeGroupData($_REQUEST["group"], "descript", $_REQUEST["groupsubscribe"]);
                \Users\GroupAgent::ChangeGroupData($_REQUEST["group"], "color", $_REQUEST["groupcolor"]);
                { header("Location: ../../adminpanel.php?p=groups&res=3se&visible&group=" . $_REQUEST["group"]); exit; }
            }
        } else { header("Location: ../../adminpanel.php?p=groups&res=3ne&visible&group=" . $_REQUEST["group"]); exit; }
    }

    if (isset($_REQUEST["edit_group_button"])) {
        if ($_REQUEST["group"] == 0) { header("Location: ../../adminpanel.php?p=groups&res=3ngs"); exit; }
        else { header("Location: ../../adminpanel.php?p=groups&visible&group=" . $_REQUEST["group"]); exit; }
        //if ()
    }
}

{ header("Location: ../../adminpanel.php?p=forbidden"); exit; }