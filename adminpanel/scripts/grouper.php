<?php
require_once "../../engine/engine.php";
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
if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\Models\User((new \Users\Services\Session(\Users\Services\FlashSession::getSessionId()))->getContent()["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if ((!$user->UserGroup()->getPermission("group_create")) &&
    (!$user->UserGroup()->getPermission("group_change")) &&
    (!$user->UserGroup()->getPermission("group_delete")) &&
    (!$user->UserGroup()->getPermission("change_perms"))) { header("Location: ../../adminpanel.php&&res=1"); exit; } else {

    $groupName = \Users\GroupAgent::GetGroupNameById($_POST["group-id-input"]);

    if (isset($_POST["save_perms_button"])) {
        if ($user->UserGroup()->getPermission("change_perms")) {
            //Custom engine perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "enterpanel", $_POST["permadminpanel"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "change_engine_settings", $_POST["change_engine_settings"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "offline_visiter", $_POST["offline_visiter"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "rules_edit", $_POST["rules_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "change_template_design", $_POST["change_template_design"]);

            //Custom groups perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "change_perms", $_POST["permchangeperms"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "group_create", $_POST["permgroupcreate"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "group_delete", $_POST["permgroupdelete"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "group_change", $_POST["permgroupchange"]);

            //Custom users perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "user_add", $_POST["user_add"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "user_remove", $_POST["user_remove"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "user_see_foreign", $_POST["user_see_foreign"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "change_profile", $_POST["change_profile"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "change_another_profiles", $_POST["change_another_profiles"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "change_user_group", $_POST["cug"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "user_signs", $_POST["user_signs"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "user_ban", $_POST["user_ban"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "user_unban", $_POST["user_unban"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "user_banip", $_POST["user_banip"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "user_unbanip", $_POST["user_unbanip"]);

            //Custom report perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_create", $_POST["report_create"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_remove", $_POST["report_remove"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_foreign_remove", $_POST["report_foreign_remove"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_edit", $_POST["report_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_foreign_edit", $_POST["report_foreign_remove"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_close", $_POST["report_close"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_talking", $_POST["report_talking"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_answer_edit", $_POST["report_answer_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "report_foreign_answer_edit", $_POST["report_foreign_answer_edit"]);

            //Custom uploads perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "upload_add", $_POST["upload-add"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "upload_delete", $_POST["upload-delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "upload_delete_foreign", $_POST["upload-delete-foreign"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "upload_see_all", $_POST["upload_see_all"]);

            //Custom categories perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "category_create", $_POST["category_create"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "category_edit", $_POST["category_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "category_delete", $_POST["category_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "category_see_unpublic", $_POST["category_see_unpublic"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "category_params_ignore", $_POST["category_params_ignore"]);

            //Custom topics perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "topic_create", $_POST["topic_create"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "topic_edit", $_POST["topic_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "topic_foreign_edit", $_POST["topic_foreign_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "topic_delete", $_POST["topic_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "topic_foreign_delete", $_POST["topic_foreign_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "topic_manage", $_POST["topic_manage"]);

            //Custom comments perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "comment_create", $_POST["comment_create"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "comment_edit", $_POST["comment_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "comment_foreign_edit", $_POST["comment_foreign_edit"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "comment_delete", $_POST["comment_delete"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "comment_foreign_delete", $_POST["comment_foreign_delete"]);

            //Perms for manage static content of site.
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "sc_create_pages", $_POST["sc_create_pages"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "sc_edit_pages", $_POST["sc_edit_pages"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "sc_remove_pages", $_POST["sc_remove_pages"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "sc_design_edit", $_POST["sc_design_edit"]);

            //Custom bot mail perms
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "bmail_sende", $_POST["bmail_sende"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "bmail_sends", $_POST["bmail_sends"]);

            //Custom logs permissions
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "logs_see", $_POST["logs_see"]);
            \Users\GroupAgent::ChangeGroupPerms($_POST["group-id-input"], "plugins_control", $_POST["see_controllers"]);

            //Plugins permissions.
            $plugins = \Engine\PluginManager::GetInstalledPlugins();
            foreach ($plugins as $plugin){
                $permissions = \Engine\PluginManager::GetPermissionsOfPlugin($plugin["id"], $_POST["group-id-input"]);
                foreach($permissions as $permission){
                    \Engine\PluginManager::SetPermissionValue($plugin["id"], $permission["codename"], $_POST["group-id-input"], $_POST[$plugin["codeName"] . "_" . $permission["translate_path"]]);
                }
            }

            \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("group_panel.logs.change_group_perm_log") . "\"$groupName\".");
        } else { header("Location: ../../adminpanel.php?res=3npc&p=groups&visible&group=" . $_POST["group"]); exit; }
        { header("Location: ../../adminpanel.php?p=groups&res=3spc&visible&group=" . $_POST["group"]); exit; }

    }

    if (isset($_POST["add_group_button"])) {
        if (!$user->UserGroup()->getPermission("group_create")) { header("Location: ../../adminpanel.php?p=groups&res=1"); exit; }
        else {
            if (\Users\GroupAgent::AddGroup($_POST["groupname_create"], $_POST["groupcolor_create"], $_POST["groupsubscribe_create"]) === True)
                {
                    \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("group_panel.logs.create_group_log") . "\"" . $_POST["groupname_create"] . "\"");
                    header("Location: ../../adminpanel.php?p=groups&res=3sgc");
                    exit;
                }
            else { header("Location: ../../adminpanel.php?p=groups&res=3ngc"); exit; }
        }
    }

    if (isset($_POST["delete_group_button"])) {
        if (!$user->UserGroup()->getPermission("group_delete")) { header("Location: ../../adminpanel.php?p=groups&res=1"); exit; }
        else {
            if ($_POST["group-id-input"] == 1 || $_POST["group-id-input"] == 2 || $_POST["group-id-input"] == 3){ { header("Location: ../../adminpanel.php?p=groups&res=3ndd&group=".$_POST["group-id-input"]."&visible"); exit; } }
            elseif ($_POST["group-id-input"] == \Engine\Engine::GetEngineInfo("sg")){ { header("Location: ../../adminpanel.php?p=groups&res=3ngsd&group=".$_POST["group-id-input"]."&visible"); exit; } }
            else {
                if (\Users\GroupAgent::MoveGroupMembers($_POST["group-id-input"], \Engine\Engine::GetEngineInfo("sg"))) {
                    if (\Users\GroupAgent::RemoveGroup($_POST["group-id-input"]) === True) {
                        \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("group_panel.logs.remove_group_log") . "\"" . $groupName . "\"");
                        header("Location: ../../adminpanel.php?p=groups&res=3sgd");
                        exit;
                    }
                    else { header("Location: ../../adminpanel.php?p=groups&res=3ngmm"); exit; }
                }
            }
        }
    }

    if (isset($_POST["save_group_button"])) {
        if ($user->UserGroup()->getPermission("group_change")) {
                \Users\GroupAgent::ChangeGroupData($_POST["group-id-input"], "name", $_POST["groupname"]);
                \Users\GroupAgent::ChangeGroupData($_POST["group-id-input"], "descript", $_POST["groupsubscribe"]);
                \Users\GroupAgent::ChangeGroupData($_POST["group-id-input"], "color", $_POST["groupcolor"]);
                \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("group_panel.logs.change_custom_group_info_log") . "\"$groupName\"");
                header("Location: ../../adminpanel.php?p=groups&res=3se&visible&group=" . $_POST["group"]);
                exit;
            }
        } else {
            header("Location: ../../adminpanel.php?p=groups&res=3ne&visible&group=" . $_POST["group"]);
            exit;
        }
    }

    if (isset($_POST["edit_group_button"])) {
        if ($_POST["group"] == 0) { header("Location: ../../adminpanel.php?p=groups&res=3ngs"); exit; }
        else {
            header("Location: ../../adminpanel.php?p=groups&visible&group=" . $_POST["group"]);
            exit;
        }
        //if ()
    }

{ header("Location: ../../adminpanel.php?p=forbidden"); exit; }