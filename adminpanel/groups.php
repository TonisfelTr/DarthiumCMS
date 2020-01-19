<?php
if (!defined("TT_AP")){ header("Location: ../adminpanel.php?p=forbidden"); exit; }
//Проверка на права.
if ((!$user->UserGroup()->getPermission("group_create")) &&
   (!$user->UserGroup()->getPermission("group_change")) &&
   (!$user->UserGroup()->getPermission("group_delete")) &&
   (!$user->UserGroup()->getPermission("change_perms"))) header("Location: ../adminpanel.php?res=1"); else {
    if ($user->UserGroup()->getPermission("group_change") ||
        $user->UserGroup()->getPermission("group_delete") ||
        $user->UserGroup()->getPermission("change_perms")) $permGroupManage = true;
    else $permGroupManage = false;
$groupList = \Users\GroupAgent::GetGroupList();
?>
<div class="inner cover">
    <h1 class="cover-heading"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.page_name"); ?></h1>
    <p class="lead"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.page_description"); ?></p>
    <div class="col-lg-2">
        <div class="btn-group-vertical" role="group" style="width: 100%;">
            <?php if ($permGroupManage){?>
            <button type="button" class="btn btn-default active" id="manage"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.panel_name"); ?></button><?php }
            if ($user->UserGroup()->getPermission("group_create")) { ?>
            <button type="button" class="btn btn-default" id="add"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.panel_name"); ?></button><?php } ?>
        </div>
    </div>
    <div class="col-lg-10 panel-body">
        <form name="groups" method="post" action="adminpanel/scripts/grouper.php">
            <div id="group_manage">
                <div class="custom-group">
                    <h2 style="margin-top: 0px;"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.panel_name"); ?></h2>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.panel_description"); ?></p>
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.group"); ?></div>
                        <select class="form-control" name="group" id="selector_group">
                            <option value="0"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.select_group"); ?></option>
                            <?php for($i = 0; $i < count($groupList); $i++){
                                echo "<option value='".$groupList[$i]."'";
                                if (isset($_REQUEST["group"])) if ($_REQUEST["group"] != 0 && $_REQUEST["group"] == $groupList[$i]) echo "selected";
                                echo ">" . \Users\GroupAgent::GetGroupNameById($groupList[$i]) . "</option>";
                            } ?>
                        </select>
                    </div>
                    <?php if (isset($_GET["visible"]) && $_REQUEST["group"] != 0){ ?>
                        <div id="group_sel_manage">
                            <?php
                            /* ################################################################
                             *  Панель изменения параметров группы.
                             ##################################################################*/
                            if ($user->UserGroup()->getPermission("group_change")) { ?>
                            <hr/>
                            <div id="info_editor">
                                <h2><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.custom_info"); ?></h2>
                                <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.custom_info_tip"); ?>"<?php echo \Users\GroupAgent::GetGroupNameById($_REQUEST["group"]); ?>").</p>
                                <hr />
                                <div class="input-group">
                                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.id_group"); ?></div>
                                    <div class="form-control alert-info"><?php echo $_REQUEST["group"];?></div>
                                </div>
                                <div class="input-group">
                                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.group_name"); ?></div>
                                    <input type="text" class="form-control" maxlength="16" id="exampleInputAmount" name="groupname" value="<?php echo \Users\GroupAgent::GetGroupNameById($_REQUEST["group"]);?>">
                                    <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.group_name_tip"); ?></div>
                                </div>
                                <div class="input-group">
                                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.group_description"); ?></div>
                                    <textarea class="form-control" id="exampleInputAmount" name="groupsubscribe"><?php echo Users\GroupAgent::GetGroupDescribe($_REQUEST["group"]);?></textarea>
                                </div>
                                <div class="input-group">
                                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.group_color"); ?></div>
                                    <input class="form-control" type="color" name="groupcolor" value="<?php echo Users\GroupAgent::GetGroupColor($_REQUEST["group"]);?>">
                                    <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.group_color_tip"); ?></div>
                                </div>
                                <hr />
                                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.first_hint"); ?></div>
                                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.second_hint"); ?></div>
                                <div class="btn-group" role="group">
                                    <?php if (isset($_REQUEST["visible"]) && $_REQUEST["group"] != 0){ ?>
                                        <button type="submit" class="btn btn-default" style="background-color: #9adeff;" name="edit_group_button"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.edit_selected_group_btn"); ?></button>
                                        <?php if ($user->UserGroup()->getPermission("group_change")) { ?><button type="submit" class="btn btn-default" name="save_group_button"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.save_custom_info_btn"); ?></button><?php }
                                        if ($user->UserGroup()->getPermission("group_delete")) { ?><button type="submit" class="btn btn-default" name="delete_group_button"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.remove_group_btn"); ?></button><?php }
                                     } ?>
                                </div>
                            </div>
                            <?php }
                            /* ################################################################
                             *  Панель управления правами.
                             ##################################################################*/
                            if ($user->UserGroup()->getPermission("change_perms")) { ?>
                                <hr />
                                <div id="perm_editor">
                                    <h2><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_editor"); ?></h2>
                                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_editor_tip.first_part"); ?>"<?php echo \Users\GroupAgent::GetGroupNameById($_REQUEST["group"]); ?>"<?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_editor_tip.second_part"); ?></p>
                                    <hr />
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_to_custom.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_to_custom.adminpanel_access"); ?></div>
                                        <select class="form-control" name="permadminpanel">
                                            <option value="1"  class="success alert-success" class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "enterpanel")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "enterpanel")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_to_custom.offline_site_access"); ?></div>
                                        <select class="form-control" name="offline_visiter">
                                            <option value="1"  class="success alert-success" class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "offline_visiter")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "offline_visiter")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_to_custom.change_settings_access"); ?></div>
                                        <select class="form-control" name="change_engine_settings">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_engine_settings")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_engine_settings")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_to_custom.change_rules_access"); ?></div>
                                        <select class="form-control" name="rules_edit">
                                            <option value="1"  class="success alert-success" class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "rules_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "rules_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_group_managment.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_group_managment.change_perms_group"); ?></div>
                                        <select class="form-control" name="permchangeperms">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_perms")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_perms")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_group_managment.create_group"); ?></div>
                                        <select class="form-control" name="permgroupcreate">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_group_managment.remove_group"); ?></div>
                                        <select class="form-control" name="permgroupdelete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_group_managment.custom_info_edit"); ?></div>
                                        <select class="form-control" name="permgroupchange">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_change")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_change")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.create_new_users"); ?></div>
                                        <select class="form-control" name="user_add">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_add")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_add")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.remove_users"); ?></div>
                                        <select class="form-control" name="user_remove">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_remove")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_remove")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.change_own_profile"); ?></div>
                                        <select class="form-control" name="change_profile">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_profile")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_profile")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.change_foreign_profile"); ?></div>
                                        <select class="form-control" name="change_another_profiles">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_another_profiles")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_another_profiles")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.change_group_user"); ?></div>
                                        <select class="form-control" name="cug">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_user_group")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_user_group")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.ban_user"); ?></div>
                                        <select class="form-control" name="user_ban">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_ban")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_ban")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.unban_user"); ?></div>
                                        <select class="form-control" name="user_unban">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_unban")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_unban")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.banip_user"); ?></div>
                                        <select class="form-control" name="user_banip">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_banip")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_banip")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.unbanip_user"); ?></div>
                                        <select class="form-control" name="user_unbanip">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_unbanip")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_unbanip")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.use_signature"); ?></div>
                                        <select class="form-control" name="user_signs">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_signs")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_signs")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_users_managment.see_foreign_profiles"); ?></div>
                                        <select class="form-control" name="user_see_foreign">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_see_foreign")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_see_foreign")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.create_report"); ?></div>
                                        <select class="form-control" name="report_create">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.remove_own_reports"); ?></div>
                                        <select class="form-control" name="report_remove">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_remove")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_remove")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.remove_foreign_reports"); ?></div>
                                        <select class="form-control" name="report_foreign_remove">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_remove")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_remove")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.change_own_reports"); ?></div>
                                        <select class="form-control" name="report_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                            <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.change_foreign_reports"); ?></div>
                                            <select class="form-control" name="report_foreign_edit">
                                                <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                                <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                            </select>
                                        </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.change_own_answers"); ?></div>
                                        <select class="form-control" name="report_answer_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_answer_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_answer_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.change_foreign_answers"); ?></div>
                                        <select class="form-control" name="report_foreign_answer_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_answer_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_answer_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                            <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.close_reports"); ?></div>
                                            <select class="form-control" name="report_close">
                                                <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_close")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                                <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_close")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                            </select>
                                        </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_report.dialogue_in_reports"); ?></div>
                                        <select class="form-control" name="report_talking">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_talking")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_talking")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_upload.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_upload.upload_files"); ?></div>
                                        <select class="form-control" name="upload-add">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_add")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_add")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_upload.remove_own_files"); ?></div>
                                        <select class="form-control" name="upload-delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_upload.remove_foreign_files"); ?></div>
                                        <select class="form-control" name="upload-delete-foreign">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_delete_foreign")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_delete_foreign")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_upload.list_of_uploaded"); ?></div>
                                        <select class="form-control" name="upload_see_all">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_see_all")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_see_all")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_categories.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_categories.create_category"); ?></div>
                                        <select class="form-control" name="category_create">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_categories.edit_category"); ?></div>
                                        <select class="form-control" name="category_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_categories.remove_categories"); ?></div>
                                        <select class="form-control" name="category_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_categories.unpublic_see"); ?></div>
                                        <select class="form-control" name="category_see_unpublic">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_see_unpublic")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_see_unpublic")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_categories.ignore_category_rules"); ?></div>
                                        <select class="form-control" name="category_params_ignore">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_params_ignore")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_params_ignore")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_topics.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_topics.create_topics"); ?></div>
                                        <select class="form-control" name="topic_create">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_topics.edit_own_topics"); ?></div>
                                        <select class="form-control" name="topic_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_topics.edit_foreign_topics"); ?></div>
                                        <select class="form-control" name="topic_foreign_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_foreign_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_foreign_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_topics.remove_own_topics"); ?></div>
                                        <select class="form-control" name="topic_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_topics.remove_foreign_topics"); ?></div>
                                        <select class="form-control" name="topic_foreign_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_foreign_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_foreign_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_topics.managment_topics"); ?></div>
                                        <select class="form-control" name="topic_manage">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_manage")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_manage")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_comments.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_comments.create_comments"); ?></div>
                                        <select class="form-control" name="comment_create">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_create")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_comments.edit_own_comments"); ?></div>
                                        <select class="form-control" name="comment_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_comments.edit_foreign_comments"); ?></div>
                                        <select class="form-control" name="comment_foreign_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_foreign_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_foreign_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_comments.remove_own_comments"); ?></div>
                                        <select class="form-control" name="comment_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_comments.remove_foreign_comments"); ?></div>
                                        <select class="form-control" name="comment_foreign_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_foreign_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_foreign_delete")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_staticc.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_staticc.create_pages"); ?></div>
                                        <select class="form-control" name="sc_create_pages">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_create_pages")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_create_pages")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_staticc.edit_pages"); ?></div>
                                        <select class="form-control" name="sc_edit_pages">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_edit_pages")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_edit_pages")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_staticc.remove_pages"); ?></div>
                                        <select class="form-control" name="sc_remove_pages">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_remove_pages")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_remove_pages")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_staticc.edit_site_interface"); ?></div>
                                        <select class="form-control" name="sc_design_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_design_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_design_edit")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_sender.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_sender.send_email"); ?></div>
                                        <select class="form-control" name="bmail_sende">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "bmail_sende")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "bmail_sende")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_sender.send_pm"); ?></div>
                                        <select class="form-control" name="bmail_sends">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "bmail_sends")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "bmail_sends")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_logs.tip"); ?></p>
                                    <div class="input-group">
                                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.perms_logs.see_logs"); ?></div>
                                        <select class="form-control" name="logs_see">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "logs_see")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.allow"); ?></option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "logs_see")) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.denied"); ?></option>
                                        </select>
                                    </div>
                                    <hr />
                                    <div class="btn-group" role="group">
                                        <?php if (isset($_REQUEST["visible"]) && $_REQUEST["group"] != 0){
                                            if ($user->UserGroup()->getPermission("change_perms")) { ?>
                                                <button type="submit" class="btn btn-default" name="save_perms_button"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.save_perms_btn"); ?></button><?php }
                                        } ?>
                                    </div>
                                </div>
                            <?php }
                            if (!$user->UserGroup()->getPermission("change_perms") && !$user->UserGroup()->getPermission("group_change")){ ?>
                                <hr>
                                <div class="alert alert-danger"><span class="glyphicon glyphicon-stop"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_managment.access_denied"); ?></div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="btn-group" role="group" id="group_edit_panel">
                    <button type="submit" class="btn btn-default" name="edit_group_button"><?php echo \Engine\LanguageManager::GetTranslation("edit"); ?></button>
                </div>
            </div>
            <div id="group_add" <?php if ($permGroupManage) echo "hidden"; ?>>
                <div class="custom-group">
                    <h2 style="margin-top: 0px;"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.panel_name"); ?></h2>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.panel_description"); ?></p>
                    <hr>
                    <p><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.panel_tip"); ?></p>
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.group_name"); ?></div>
                        <input type="text" maxlength="16" class="form-control" id="exampleInputAmount" name="groupname_create" value="">
                        <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.group_name_tip"); ?></div>
                    </div>
                    <hr>
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.group_description"); ?></div>
                        <textarea class="form-control" style="max-width: 100%; min-width: 100%; min-height: 85px;" name="groupsubscribe_create" maxlength="300"></textarea>
                        <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.group_description_tip"); ?></div>
                    </div>
                    <hr>
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.group_color"); ?></div>
                        <input class="form-control" type="color" name="groupcolor_create">
                        <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.group_color_tip"); ?></div>
                    </div>
                    <hr />
                    <div class="btn-group" role="group">
                        <button type="submit" class="btn btn-default" name="add_group_button"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.create_btn"); ?></button>
                        <button type="reset" class="btn btn-default" name="restart_group_button"><?php echo \Engine\LanguageManager::GetTranslation("group_panel.group_add.clear_btn"); ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">

    var m_div = document.getElementById("group_manage");
    var m_but = document.getElementById("manage");
    var a_but = document.getElementById("add");
    var e = document.getElementById("selector_group");
    if (document.getElementById("group_sel_manage") != undefined) {
        var selectors = document.getElementById("perm_editor").getElementsByTagName("select");

        if ($(document).ready()) {
            if (e.options[e.selectedIndex].text == $("input[name=group]").val())
                $("button[name=edit_group_button]").attr("disabled", "disabled");

            for (i = 0; i < selectors.length; i++) {
                if (selectors[i].options[selectors[i].selectedIndex].value == 0)
                    $(selectors[i]).addClass("danger alert-danger");

                if (selectors[i].options[selectors[i].selectedIndex].value == 1)
                    $(selectors[i]).addClass("success alert-success");
            }
        }

        $(selectors).each(function () {
            $(this).on("change", function () {
                if (this.options[this.selectedIndex].value == 0) {
                    $(this).removeClass("success alert-success");
                    $(this).addClass("danger alert-danger");
                }

                if (this.options[this.selectedIndex].value == 1) {
                    $(this).removeClass("danger alert-danger");
                    $(this).addClass("success alert-success");
                }
            });
        });

    }

    $(e).on("change", function(){
        if (e.options[e.selectedIndex].text == $("input[name=groupname]").val())
            $("button[name=edit_group_button]").attr("disabled", "disabled");
        else
            $("button[name=edit_group_button]").removeAttr("disabled");
    });

    $( "#add" ).click(function() {
        if (!m_div.hasAttribute("hidden"))
        {
            $(a_but).addClass("active");
            $(m_but).removeClass("active");

            $("#group_manage").animate({
                  height: "hide"
             }, 1000);
            $("#group_add").animate({
                height: "show"
            }, 1000);
        }
    });

    $( "#manage").click(function() {
        if (!m_div.hasAttribute("hidden")) {
            $(a_but).removeClass("active");
            $(m_but).addClass("active");
            $("#group_add").animate({
                height: "hide"
            }, 1000);
            $("#group_manage").animate({
                height: "show"
            }, 1000);
        }
    });

    if (document.getElementById("info_editor") != undefined || document.getElementById("perm_editor") != undefined){
        $("#group_edit_panel").hide();
    }

</script>
<?php } ?>