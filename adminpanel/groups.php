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
    <h1 class="cover-heading">Группы</h1>
    <p class="lead">Редактирование групп и их привилегий.</p>
    <div class="col-lg-2">
        <div class="btn-group-vertical" role="group" style="width: 100%;">
            <?php if ($permGroupManage){?>
            <button type="button" class="btn btn-default active" id="manage">Управление группами</button><?php }
            if ($user->UserGroup()->getPermission("group_create")) { ?>
            <button type="button" class="btn btn-default" id="add">Добавление групп</button><?php } ?>
        </div>
    </div>
    <div class="col-lg-10 panel-body">
        <form name="groups" method="post" action="adminpanel/scripts/grouper.php">
            <div id="group_manage">
                <div class="custom-group">
                    <h2 style="margin-top: 0px;">Управление группами</h2>
                    <p class="helper">Здесь производится распределение прав групп к различным действиям на сайте, а так же редактирование итендефикационной информации.</p>
                    <div class="input-group">
                        <div class="input-group-addon">Группа</div>
                        <select class="form-control" name="group" id="selector_group">
                            <option value="0">Выберите группу...</option>
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
                                <h2>Итендефикационная информация</h2>
                                <p class="helper">Здесь Вы можете изменить базовую информацию группы (сейчас эта группа - "<?php echo \Users\GroupAgent::GetGroupNameById($_REQUEST["group"]); ?>").</p>
                                <hr />
                                <div class="input-group">
                                    <div class="input-group-addon">ID группы</div>
                                    <div class="form-control alert-info"><?php echo $_REQUEST["group"];?></div>
                                </div>
                                <div class="input-group">
                                    <div class="input-group-addon">Название группы</div>
                                    <input type="text" class="form-control" maxlength="16" id="exampleInputAmount" name="groupname" value="<?php echo \Users\GroupAgent::GetGroupNameById($_REQUEST["group"]);?>">
                                    <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> Не должно быть иметь меньше 4 и больше 16 символов.</div>
                                </div>
                                <div class="input-group">
                                    <div class="input-group-addon">Описание группы</div>
                                    <textarea class="form-control" id="exampleInputAmount" name="groupsubscribe"><?php echo Users\GroupAgent::GetGroupDescribe($_REQUEST["group"]);?></textarea>
                                </div>
                                <div class="input-group">
                                    <div class="input-group-addon">Цвет группы</div>
                                    <input class="form-control" type="color" name="groupcolor" value="<?php echo Users\GroupAgent::GetGroupColor($_REQUEST["group"]);?>">
                                    <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> Обычно модераторы - зелёные, администраторы - красные.</div>
                                </div>
                                <hr />
                                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> Вы не можете удалить группы администраторов, модераторов и пользователей; остальные функции разрешены.</div>
                                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> При удалении группы, все её члены перейдут в группу, куда записываются новозарегистрированные пользователи.</div>
                                <div class="btn-group" role="group">
                                    <?php if (isset($_REQUEST["visible"]) && $_REQUEST["group"] != 0){ ?>
                                        <button type="submit" class="btn btn-default" style="background-color: #9adeff;" name="edit_group_button">Редактировать выделенную группу</button>
                                        <?php if ($user->UserGroup()->getPermission("group_change")) { ?><button type="submit" class="btn btn-default" name="save_group_button">Сохранить основную информацию</button><?php }
                                        if ($user->UserGroup()->getPermission("group_delete")) { ?><button type="submit" class="btn btn-default" name="delete_group_button">Удалить группу</button><?php }
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
                                    <h2>Редактор прав</h2>
                                    <p class="helper">Здесь вы можете изменить права для выбранной группы (сейчас это "<?php echo \Users\GroupAgent::GetGroupNameById($_REQUEST["group"]); ?>"). Будьте внимательны! Одно лишнее отключение и можно
                                    лишить прав всю команду администраторов!</p>
                                    <hr />
                                    <p class="h-helper">Права по изменению внутренностей сайта</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Доступ к панели управления</div>
                                        <select class="form-control" name="permadminpanel">
                                            <option value="1"  class="success alert-success" class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "enterpanel")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "enterpanel")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Доступ к выключенному сайту</div>
                                        <select class="form-control" name="offline_visiter">
                                            <option value="1"  class="success alert-success" class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "offline_visiter")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "offline_visiter")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение редактирования шаблонов</div>
                                        <select class="form-control" name="change_design">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_design")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_design")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на изменение настроек сайта</div>
                                        <select class="form-control" name="change_engine_settings">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_engine_settings")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_engine_settings")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на просмотр статистики</div>
                                        <select class="form-control" name="look_statistic">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "look_statistic")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "look_statistic")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на редактирование правил сайта</div>
                                        <select class="form-control" name="rules_edit">
                                            <option value="1"  class="success alert-success" class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "rules_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "rules_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управления группами</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение менять права групп</div>
                                        <select class="form-control" name="permchangeperms">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_perms")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_perms")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение создавать группы</div>
                                        <select class="form-control" name="permgroupcreate">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_create")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_create")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение удалять группы</div>
                                        <select class="form-control" name="permgroupdelete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_delete")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_delete")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение изменять основную информацию групп</div>
                                        <select class="form-control" name="permgroupchange">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_change")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "group_change")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управления профилями пользователей</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение создавать пользователей</div>
                                        <select class="form-control" name="user_add">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_add")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_add")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение удалять пользователей</div>
                                        <select class="form-control" name="user_remove">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_remove")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_remove")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение изменять свой профиль</div>
                                        <select class="form-control" name="change_profile">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_profile")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_profile")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение изменять чужие профили</div>
                                        <select class="form-control" name="change_another_profiles">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_another_profiles")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_another_profiles")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение изменять группу пользователей</div>
                                        <select class="form-control" name="cug">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_user_group")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "change_user_group")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение блокировать пользователей</div>
                                        <select class="form-control" name="user_ban">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_ban")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_ban")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение разблокировать пользователей</div>
                                        <select class="form-control" name="user_unban">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_unban")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_unban")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение блокировать IP адреса</div>
                                        <select class="form-control" name="user_banip">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_banip")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_banip")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение разблокировать IP адреса</div>
                                        <select class="form-control" name="user_unbanip">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_unbanip")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_unbanip")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение использовать подписи</div>
                                        <select class="form-control" name="user_signs">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_signs")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_signs")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на просмотр чужих профилей</div>
                                        <select class="form-control" name="user_see_foreign">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_see_foreign")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "user_see_foreign")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управления жалобами</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на создание жалоб</div>
                                        <select class="form-control" name="report_create">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_create")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_create")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на удаление своих жалоб</div>
                                        <select class="form-control" name="report_remove">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_remove")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_remove")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на удаление чужих жалоб</div>
                                        <select class="form-control" name="report_foreign_remove">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_remove")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_remove")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение изменять свои жалобы</div>
                                        <select class="form-control" name="report_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                            <div class="input-group-addon">Разрешение изменять чужие жалобы</div>
                                            <select class="form-control" name="report_foreign_edit">
                                                <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_edit")) echo "selected";?>>Разрешено</option>
                                                <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_edit")) echo "selected";?>>Запрещено</option>
                                            </select>
                                        </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение изменять свои ответы</div>
                                        <select class="form-control" name="report_answer_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_answer_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_answer_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение изменять чужие ответы</div>
                                        <select class="form-control" name="report_foreign_answer_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_answer_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_foreign_answer_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                            <div class="input-group-addon">Разрешить закрывать жалобы</div>
                                            <select class="form-control" name="report_close">
                                                <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_close")) echo "selected";?>>Разрешено</option>
                                                <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_close")) echo "selected";?>>Запрещено</option>
                                            </select>
                                        </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на диалог в дисскусиях</div>
                                        <select class="form-control" name="report_talking">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_talking")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "report_talking")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управления загрузками</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение загружать файлы</div>
                                        <select class="form-control" name="upload-add">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_add")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_add")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение удалять свои файлы</div>
                                        <select class="form-control" name="upload-delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_delete")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_delete")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение удалять чужие файлы</div>
                                        <select class="form-control" name="upload-delete-foreign">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_delete_foreign")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_delete_foreign")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение просматривать список загруженных файлов</div>
                                        <select class="form-control" name="upload_see_all">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_see_all")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "upload_see_all")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управления категориями</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение создавать категории</div>
                                        <select class="form-control" name="category_create">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_create")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_create")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение редактировать категории</div>
                                        <select class="form-control" name="category_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение удалять категории</div>
                                        <select class="form-control" name="category_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_delete")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_delete")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на просмотр непубличных категорий</div>
                                        <select class="form-control" name="category_see_unpublic">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_see_unpublic")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_see_unpublic")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на игнорирование правил категории</div>
                                        <select class="form-control" name="category_params_ignore">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_params_ignore")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "category_params_ignore")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управлением темами</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение создавать темы</div>
                                        <select class="form-control" name="topic_create">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_create")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_create")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на редактирование своих тем</div>
                                        <select class="form-control" name="topic_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на редактирование чужих тем</div>
                                        <select class="form-control" name="topic_foreign_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_foreign_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_foreign_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на удаление своих тем</div>
                                        <select class="form-control" name="topic_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_delete")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_delete")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на удаление чужих тем</div>
                                        <select class="form-control" name="topic_foreign_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_foreign_delete")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_foreign_delete")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение на управление темами</div>
                                        <select class="form-control" name="topic_manage">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_manage")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "topic_manage")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управления комментариями</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение оставлять комментарии</div>
                                        <select class="form-control" name="comment_create">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_create")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_create")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение редактировать свои комментарии</div>
                                        <select class="form-control" name="comment_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение редактировать чужие комментарии</div>
                                        <select class="form-control" name="comment_foreign_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_foreign_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_foreign_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение удалять свои комментарии</div>
                                        <select class="form-control" name="comment_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_delete")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_delete")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение удалять чужие комментарии</div>
                                        <select class="form-control" name="comment_foreign_delete">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_foreign_delete")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_foreign_delete")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение оценивать комментарии</div>
                                        <select class="form-control" name="comment_commend">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_commend")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "comment_commend")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управления статическим контентом</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение создавать статические страницы</div>
                                        <select class="form-control" name="sc_create_pages">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_create_pages")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_create_pages")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение редактировать статические страницы</div>
                                        <select class="form-control" name="sc_edit_pages">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_edit_pages")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_edit_pages")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение удалять статические страницы</div>
                                        <select class="form-control" name="sc_remove_pages">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_remove_pages")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_remove_pages")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">Разрешение редактировать переферию сайта</div>
                                        <select class="form-control" name="sc_design_edit">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_design_edit")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "sc_design_edit")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <p class="h-helper">Права управления рассылкой</p>
                                    <div class="input-group">
                                        <div class="input-group-addon">Рассылка email</div>
                                        <select class="form-control" name="bmail_sende">
                                            <option value="1"  class="success alert-success" <?php if (\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "bmail_sende")) echo "selected";?>>Разрешено</option>
                                            <option value="0"  class="danger alert-danger" <?php if (!\Users\GroupAgent::IsHavePerm($_REQUEST["group"], "bmail_sende")) echo "selected";?>>Запрещено</option>
                                        </select>
                                    </div>
                                    <hr />
                                    <div class="btn-group" role="group">
                                        <?php if (isset($_REQUEST["visible"]) && $_REQUEST["group"] != 0){
                                            if ($user->UserGroup()->getPermission("change_perms")) { ?>
                                                <button type="submit" class="btn btn-default" name="save_perms_button">Сохранить права</button><?php }
                                        } ?>
                                    </div>
                                </div>
                            <?php }
                            if (!$user->UserGroup()->getPermission("change_perms") && !$user->UserGroup()->getPermission("group_change")){ ?>
                                <hr>
                                <div class="alert alert-danger"><span class="glyphicon glyphicon-stop"></span> У вас нет доступа к редактированию групп и к редактированию их прав.</div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="btn-group" role="group" id="group_edit_panel">
                    <button type="submit" class="btn btn-default" name="edit_group_button">Редактировать</button>
                </div>
            </div>
            <div id="group_add" <?php if ($permGroupManage) echo "hidden"; ?>>
                <div class="custom-group">
                    <h2 style="margin-top: 0px;">Добавление группы</h2>
                    <p class="helper">Здесь Вы можете создать свои группы.</p>
                    <hr>
                    <p>Здесь вы можете только создать новую группу. Редактирование прав данной группы производится в панели "Управление группами".</p>
                    <div class="input-group">
                        <div class="input-group-addon">Название группы</div>
                        <input type="text" maxlength="16" class="form-control" id="exampleInputAmount" name="groupname_create" value="">
                        <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> Введите название группы, которая будет создана. Название не должно быть короче 4 и длиннее 16 символов.</div>
                    </div>
                    <hr>
                    <div class="input-group">
                        <div class="input-group-addon">Описание группы</div>
                        <textarea class="form-control" style="max-width: 100%; min-width: 100%; min-height: 85px;" name="groupsubscribe_create" maxlength="300"></textarea>
                        <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> Напишите, какую функцию выполняют члены этой группы. Максимум - 300 символов.</div>
                    </div>
                    <hr>
                    <div class="input-group">
                        <div class="input-group-addon">Цвет группы</div>
                        <input class="form-control" type="color" name="groupcolor_create">
                        <div class="form-control info alert-info" id="exampleInputAmount"><span class="glyphicon glyphicon-info-sign"></span> Этим цветом будет подсвечиваться название группы.</div>
                    </div>
                    <hr />
                    <div class="btn-group" role="group">
                        <button type="submit" class="btn btn-default" name="add_group_button">Создать</button>
                        <button type="reset" class="btn btn-default" name="restart_group_button">Отчистить</button>
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