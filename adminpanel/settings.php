<?php

/***************************************************************************************
 * Так как настройки сохраняются не сразу, то есть не после загрузки ЭТОЙ страницы, а раньше
 * то сохранение происходит прямо перед загрузкой предыдущей конфигурации.
 * Это происходит в самой админпанели, adminpanel.php, стр. 13. Исходник функции Replace()
 * находится в /engine/replacer.php.
 ***************************************************************************************/

use Engine\DataKeeper;

if (!defined("TT_AP")){ header("Location: ../adminpanel.php?p=forbidden"); exit; }
//Проверка на наличие доступа к изменению конфигурации движка.
if (!$user->UserGroup()->getPermission("change_engine_settings")) header("Location: ../../adminpanel.php?res=1");
else {
   $langs = \Engine\Engine::GetLanguagePacks();
   $additionalFields = \Users\UserAgent::GetAdditionalFieldsList();
   $additionalFieldsOptions = [];
   $additionalFieldsOptions[] = "<option value=\"0\">". \Engine\LanguageManager::GetTranslation("settings_panel.not_selected"). "</option>";
   foreach ($additionalFields as $field) {
       $additionalFieldsOptions[] = "<option value=\"" . $field["id"] . "\">" . $field["name"] . "</option>";
   }

   function array_clear_up(){
        $pluginsList = \Engine\PluginManager::GetPluginsList();
        $installedList = \Engine\PluginManager::GetInstalledPlugins();

        $installedKeys = array_keys($installedList);
        foreach($installedKeys as $key){
            unset($pluginsList[$key]);
        }
        return $pluginsList;
   }

   $pluginList = array_clear_up();
   $installedPluginList = \Engine\PluginManager::GetInstalledPlugins();
   ?>
<div class="inner cover">
    <h1 class="cover-heading"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.page_name");?></h1>
    <p class="lead"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.page_description");?></p>
    <div id="btn-show-panel" class="btn-group">
        <button type="button" class="btn btn-default active" data-div-number="1"><span class="glyphicon glyphicon-cog"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.configuration");?></button>
        <button type="button" class="btn btn-default" data-div-number="2"><span class="glyphicon glyphicon-envelope"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman");?></button>
        <button type="button" class="btn btn-default" data-div-number="3"><span class="glyphicon glyphicon-pencil"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration");?></button>
        <button type="button" class="btn btn-default" data-div-number="4"><span class="glyphicon glyphicon-user"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users");?></button>
        <button type="button" class="btn btn-default" data-div-number="5"><span class="glyphicons glyphicons-book"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.plugins");?></button>
        <button type="button" class="btn btn-default" data-div-number="6"><span class="glyphicons glyphicons-pie-chart"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.statistic");?></button>
    </div>

    <form name="settings" method="post" action="adminpanel/scripts/replacer.php">
        <div class="custom-group">
            <div class="div-border" id="custom_sets" data-number="1">
                <h3><span class="glyphicon glyphicon-cog"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.panel_name");?></h3>
                <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.panel_description");?></p>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_name");?></div>
                    <input type="text" class="form-control"  name="sitename" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("sn"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_name_tip");?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.tagline");?></div>
                    <input type="text" class="form-control" name="sitetagline" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("stl"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.tagline_tip");?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.domain");?></div>
                    <input type="text" class="form-control"  name="domain" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("dm"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.domain_tip");?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_description");?></div>
                    <input type="text" class="form-control"  name="sitesubscribe" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ssc"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.site_description_tip");?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.hashtags");?></div>
                    <input type="text" class="form-control"  name="sitehashtags" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("sh"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.hashtags_tip");?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.status");?></div>
                    <select class="form-control" name="sitestatus">
                        <option value="1" <?php if (\Engine\Engine::GetEngineInfo("ss") == 1) echo "selected"; ?>><?php echo \Engine\LanguageManager::GetTranslation("on");?></option>
                        <option value="0" <?php if (\Engine\Engine::GetEngineInfo("ss") == 0) echo "selected"; ?>><?php echo \Engine\LanguageManager::GetTranslation("off");?></option>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.status_tip");?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.template");?></div>
                    <select class="form-control" name="sitetemplate">
                        <?php foreach(\Engine\Engine::GetTemplatesPacks() as $f){
                            if ($f == \Engine\Engine::GetEngineInfo("stp"))
                                echo "<option value=\"$f\" selected>$f</option>";
                            else
                                echo "<option value=\"$f\">$f</option>";}
                        ?>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.template_tip");?></div>
                </div>
                <hr>
                <p><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.locale_tip");?></p>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.lang");?></div>
                    <select class="form-control" name="sitelang">
                        <?php if (\Engine\Engine::GetEngineInfo("sl") === 0){ ?><option value="0" selected>&lt;<?=\Engine\LanguageManager::GetTranslation("empty")?>&gt;</option><?php }
                        /*Перебрать названия языков...*/  for ($i = 0; $i <= count($langs)-1; $i++){ ?>
                            <option value="<?php echo $langs[$i];?>" <?php if (\Engine\Engine::GetEngineInfo("sl") == $langs[$i]) echo " selected";?>><?php echo $langs[$i];?></option>
                        <?php }  ?>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.lang_tip");?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezone");?></div>
                    <select class="form-control" name="siteregiontime">
                        <option value="-12" <?php if (\Engine\Engine::GetEngineInfo("srt") == -12) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-12");?></option>
                        <option value="-11" <?php if (\Engine\Engine::GetEngineInfo("srt") == -11) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-11");?></option>
                        <option value="-10" <?php if (\Engine\Engine::GetEngineInfo("srt") == -10) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-10");?></option>
                        <option value="-9.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == -9.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-9:30");?></option>
                        <option value="-9" <?php if (\Engine\Engine::GetEngineInfo("srt") == -9) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-9");?></option>
                        <option value="-8" <?php if (\Engine\Engine::GetEngineInfo("srt") == -8) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-8");?></option>
                        <option value="-7" <?php if (\Engine\Engine::GetEngineInfo("srt") == -7) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-7");?></option>
                        <option value="-6" <?php if (\Engine\Engine::GetEngineInfo("srt") == -6) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-6");?></option>
                        <option value="-5" <?php if (\Engine\Engine::GetEngineInfo("srt") == -5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-5");?></option>
                        <option value="-4" <?php if (\Engine\Engine::GetEngineInfo("srt") == -4) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-4");?></option>
                        <option value="-3.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == -3.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-3:30");?></option>
                        <option value="-3" <?php if (\Engine\Engine::GetEngineInfo("srt") == -3) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-3");?></option>
                        <option value="-2" <?php if (\Engine\Engine::GetEngineInfo("srt") == -2) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-2");?></option>
                        <option value="-1" <?php if (\Engine\Engine::GetEngineInfo("srt") == -1) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc-1");?></option>
                        <option value="0" <?php if (\Engine\Engine::GetEngineInfo("srt") == 0) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc");?></option>
                        <option value="1" <?php if (\Engine\Engine::GetEngineInfo("srt") == 1) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+1");?></option>
                        <option value="2" <?php if (\Engine\Engine::GetEngineInfo("srt") == 2) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+2");?></option>
                        <option value="3" <?php if (\Engine\Engine::GetEngineInfo("srt") == 3) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+3");?></option>
                        <option value="3.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 3.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+3:30");?></option>
                        <option value="4" <?php if (\Engine\Engine::GetEngineInfo("srt") == 4) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+4");?></option>
                        <option value="4.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 4.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+4:30");?></option>
                        <option value="5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+5");?></option>
                        <option value="5.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 5.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+5:30");?></option>
                        <option value="5.75" <?php if (\Engine\Engine::GetEngineInfo("srt") == 5.75) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+5:45");?></option>
                        <option value="6" <?php if (\Engine\Engine::GetEngineInfo("srt") == 6) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+6");?></option>
                        <option value="6.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 6.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+6:30");?></option>
                        <option value="7" <?php if (\Engine\Engine::GetEngineInfo("srt") == 7) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+7");?></option>
                        <option value="8" <?php if (\Engine\Engine::GetEngineInfo("srt") == 8) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+8");?></option>
                        <option value="8.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 8.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+8:30");?></option>
                        <option value="8.75" <?php if (\Engine\Engine::GetEngineInfo("srt") == 8.75) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+8:45");?></option>
                        <option value="9" <?php if (\Engine\Engine::GetEngineInfo("srt") == 9) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+9");?></option>
                        <option value="9.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 9.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+9:30");?></option>
                        <option value="10" <?php if (\Engine\Engine::GetEngineInfo("srt") == 10) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+10");?></option>
                        <option value="10.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 10.5) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+10:30");?></option>
                        <option value="11" <?php if (\Engine\Engine::GetEngineInfo("srt") == 11) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+11");?></option>
                        <option value="12" <?php if (\Engine\Engine::GetEngineInfo("srt") == 12) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+12");?></option>
                        <option value="12.75" <?php if (\Engine\Engine::GetEngineInfo("srt") == 12.75) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+12:45");?></option>
                        <option value="13" <?php if (\Engine\Engine::GetEngineInfo("srt") == 13) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+13");?></option>
                        <option value="14" <?php if (\Engine\Engine::GetEngineInfo("srt") == 14) echo "selected";?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezones.utc+14");?></option>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.config_panel.timezone_tip");?></div>
                </div>
            </div>
            <div class="div-border" id="email_sets" data-number="2" hidden>
                <h3><span class="glyphicon glyphicon-envelope"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.panel_name");?></h3>
                <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.panel_description");?></p>
                <hr>
                <p><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.hint");?></p>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.email_login");?></div>
                    <input type="text" class="form-control" name="emaillogin" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("el"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.email_password");?></div>
                    <input type="password" class="form-control" name="emailpassword" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ep"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.email_protocol");?></div>
                    <select class="form-control" name="emailconnecttype">
                        <option value="tls" <?php if (\Engine\Engine::GetEngineInfo("ecp") == "tls") echo "selected"; ?>>TLS</option>
                        <option value="ssl" <?php if (\Engine\Engine::GetEngineInfo("ecp") == "ssl") echo "selected"; ?>>SSL</option>
                    </select>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.email_server");?></div>
                    <input type="text" class="form-control"  name="emailhost" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("eh"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.email_port");?></div>
                    <input type="text" class="form-control"  name="emailport" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ept"));?>">
                </div>
                <br>
                <div class="alert alert-warning"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.warning_first");?></div>
                <div class="alert alert-warning"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.warning_second");?></div>
                <button class="btn btn-default" id="mail-test-ajax-btn" type="button" style="width: 100%;"><span class="glyphicons glyphicons-message-out"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.bot_postman_panel.email_check_validity");?></button>
            </div>
            <div class="div-border" id="reg_sets"  data-number="3" hidden>
                <h3><span class="glyphicon glyphicon-pencil"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.panel_name");?></h3>
                <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.panel_description");?></p>
                <hr>
                <p><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.panel_tip");?></p>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.activation");?></div>
                    <select class="form-control" name="needactivate">
                        <option value="1" <?php if (\Engine\Engine::GetEngineInfo("na") == "1") echo "selected"; ?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.activation_on");?></option>
                        <option value="0" <?php if (\Engine\Engine::GetEngineInfo("na") == "0") echo "selected"; ?>><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.activation_off");?></option>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.activation_tip");?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.multiacc");?></div>
                    <select class="form-control" name="multiaccount" id="multiaccount">
                        <option value="1" <?php if (\Engine\Engine::GetEngineInfo("map") == "y") echo "selected"; ?>><?php echo \Engine\LanguageManager::GetTranslation("on");?></option>
                        <option value="0" <?php if (\Engine\Engine::GetEngineInfo("map") == "n") echo "selected"; ?>><?php echo \Engine\LanguageManager::GetTranslation("off");?></option>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.multiacc_tip");?></div>
                </div>
                <hr>
                <div class="input-group">
                    <?php $r = \Users\GroupAgent::GetGroupList(); ?>
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.reggroup");?></div>
                    <select class="form-control" name="standartgroup">
                        <?php for($i = 0; $i <= count($r)-1; $i++){
                                echo "<option value='".$r[$i]."'";
                                if (\Engine\Engine::GetEngineInfo("sg") == $r[$i]) echo " selected";
                                echo ">" . \Users\GroupAgent::GetGroupNameById($r[$i]) . "</option>";
                        } ?>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.registration_panel.reggroup_tip");?></div>
                </div>
            </div>
            <div class="div-border" id="users_sets" data-number="4" hidden>
                <h3><span class="glyphicon glyphicon-user"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.panel_name");?></h3>
                <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.panel_description");?></p>
                <hr>
                <div class="alert hidden" id="add-fields-info-div"><span></span></div>
                <h4><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.additive_field_header");?></h4>
                <p><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.additive_field_tip");?></p>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.additive_field_select");?></div>
                    <select class="form-control" name="user-additional-fields" id="user-add-fields">
                        <?php foreach($additionalFieldsOptions as $option) echo $option; ?>
                    </select>
                    <div class="input-group-btn">
                        <button class="btn btn-default" type="button" id="field-add-btn" title="<?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.additive_field_plus_btn_title");?>"><span class="glyphicons glyphicons-plus"></span></button>
                        <button class="btn btn-default" type="button" id="field-remove-btn" title="<?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.additive_field_minus_btn_title");?>" disabled><span class="glyphicons glyphicons-minus"></span></button>
                    </div>
                </div>
                <div id="field-panel-manage" class="div-border" style="display: none; margin-top: 15px;">
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_name");?></div>
                        <input class="form-control" name="field-name-input" id="field-name-input" maxlength="16">
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_name_tip");?></div>
                    </div>
                    <br>
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_description");?></div>
                        <input class="form-control" type="text" name="field-description" id="field-description">
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_description_tip");?></div>
                    </div>
                    <br>
                    <div class="input-group">
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_type.name");?></div>
                        <select class="form-control" id="field-type-selector">
                            <option value="1"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_type.mixing");?></option>
                            <option value="2"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_type.contact");?></option>
                            <option value="3"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_type.custom");?></option>
                        </select>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_type.tip");?></div>
                    </div>
                    <br>
                    <div class="input-group" id="custom-value-input-div" hidden>
                        <div class="input-group-addon">Значение по-умолчанию</div>
                        <input class="form-control" type="text" id="custom-value-input">
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Стандартное значение поля.</div>
                    </div>
                    <br>
                    <div class="input-group" id="require-input-div" hidden>
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_requied");?></div>
                        <div class="form-control">
                            <input type="checkbox" name="field-requied" id="field-requied">
                        </div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_requied_tip");?></div>
                    </div>
                    <br>
                    <div class="input-group" id="see-on-reg-input-div" hidden>
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_visible_in_registration");?></div>
                        <div class="form-control">
                            <input type="checkbox" name="field-reg-show" id="field-reg-show">
                        </div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_visible_in_registration_tip");?></div>
                    </div>
                    <br>
                    <div class="input-group" id="private-input-div" hidden>
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_can_be_private");?></div>
                        <div class="form-control">
                            <input type="checkbox" name="field-private" id="field-private">
                        </div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_can_be_private_tip");?></div>
                    </div>
                    <br>
                    <div class="input-group" id="link-input-div">
                        <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_link");?></div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_link_tip_first");?></div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.af_link_tip_second");?></div>
                        <textarea class="form-control" name="field-link-textarea" style="resize: vertical; min-height: 100px;" id="field-link-textarea"></textarea>
                    </div>
                    <br>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" id="field-add-ajax-btn"><span class="glyphicons glyphicons-ok"></span> <?php echo \Engine\LanguageManager::GetTranslation("apply");?></button>
                        <button class="btn btn-default" type="button" id="field-cancel-btn"><span class="glyphicons glyphicons-erase"></span> <?php echo \Engine\LanguageManager::GetTranslation("cancel");?></button>
                    </div>
                </div>
                <hr>
                <h4><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.censore");?></h4>
                <p><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.censore_tip");?></p>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.words_for_censore");?></div>
                    <input class="form-control" type="text" name="chat-filter-words" value="<?php echo \Engine\Engine::GetCensoredWords(); ?>">
                </div>
                <div class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.words_for_censore_tip");?></div>
                <hr>
                <h4><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.reports");?></h4>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.reports_reason");?></div>
                    <textarea class="form-control" style="resize: vertical; min-height: 100px;" name="reports-reasons"><?php echo \Engine\Engine::GetReportReasons(); ?></textarea>
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.reports_tip");?></div>
                <hr>
                <h4><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.avatar_settings");?></h4>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.max_avatar_width");?></div>
                    <input type="number" class="form-control"  name="avatarmaxwidth" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("aw"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.max_avatar_height");?></div>
                    <input type="number" class="form-control"  name="avatarmaxheight" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ah"));?>">
                </div>
                <hr>
                <h4><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.upload_settings");?></h4>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.valid_formats");?></div>
                    <input type="text" class="form-control"  name="uploadformats" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("upf"));?>">
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.valid_formats_tip");?></div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.max_file_size");?></div>
                    <input type="number" class="form-control"  name="maxfilesize" id="maxfilesize" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ups"));?>">
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.max_file_size_tip.first_part");
                ?> <span id="file-max-size-inmb"><?php echo \Engine\Engine::GetEngineInfo("ups") /1024 /1024; ?></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.max_file_size_tip.second_part");?></div>
                <hr>
                <h4><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.guests");?></h4>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.guest_see_profile");?></div>
                    <div class="form-control">
                        <input type="checkbox" name="guest_see_profiles" <?php if (\Engine\Engine::GetEngineInfo("gsp")) echo "checked"; ?>>
                    </div>
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.guest_see_profile_tip");?></div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.users_panel.multivote_rep");?></div>
                    <div class="form-control">
                        <input type="checkbox" name="multivote_rep" <?php if (\Engine\Engine::GetEngineInfo("vmr")) echo "checked"; ?>>
                    </div>
                    <div class="form-control alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("settings_panel.users_panel.multivote_rep_tip")?></div>
                </div>
            </div>
            <div class="div-border" id="plugin_panel" data-number="5" hidden>
                <h3><span class="glyphicons glyphicons-book"></span> <?=\Engine\LanguageManager::GetTranslation("settings_panel.plugins")?></h3>
                <p class="helper">Здесь Вы можете управлять дополнениями для системы.</p>
                <hr>
                <p>Системы управления контентом предусматривают модификации созданые отдельно - это означает, что сторонние разработчики имеют в своём наборе API инструменты для работы с системой управления контента.</p>
                <div class="plugin-div">
                    <div class="plugins">
                        <label for="template_ready_to_install">Доступные плагины:</label>
                        <select class="form-control template_list" id="template_ready_to_install" size="20">
                            <?php foreach($pluginList as $plagName => $plugin){
                                echo "<option value=\"$plagName\" data-codename=\"" . $plugin["config"]["codeName"] . "\">" . $plugin["config"]["name"] . "</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="plugins">
                        <label for="template_official">Установленные плагины:</label>
                        <select class="form-control template_list" id="template_official" size="20">
                            <?php foreach($installedPluginList as $plagName => $plugin){
                                echo "<option value=\"$plagName\" data-codename=\"" . $plugin["codeName"] . "\">" . $plugin["name"] . "</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="plugins-info">
                        <h3 id="plugin-name">Выберите плагин</h3>
                        <b>Описание: </b><span id="plugin-description"></span>
                        <p id="btn-status-block"><b>Статус плагина: </b><button id="plugin-status" class="btn" type="button">Выключено</button></p>
                        <div class="btn-group" style="width: 100%; padding-top: 15px;">
                            <button class="btn btn-default" type="button" id="btn-install" disabled><span class="glyphicons glyphicons-settings"></span> Установить плагин</button>
                            <button class="btn btn-default" type="button" id="btn-delete" disabled><span class="glyphicons glyphicons-delete"></span> Удалить плагин</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="div-border" id="metric_sets" data-number="6" hidden>
                <h3><span class="glyphicons glyphicons-pie-chart"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.statistic_panel.panel_name");?></h3>
                <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.statistic_panel.panel_description");?></p>
                <hr>
                <p><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.statistic_panel.statistic_tip");?></p>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.statistic_panel.write_statistic");?></div>
                    <div class="form-control">
                        <input type="checkbox" name="metric-lever-btn" id="metric-level-btn" <?php if (\Engine\Engine::GetEngineInfo("smt")) echo "checked"; ?>>
                    </div>
                </div>
                <div class="input-group" id="metric-code-div">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("settings_panel.statistic_panel.text_to_insert");?></div>
                    <textarea class="form-control" style="height: 300px; resize: none;" name="metric-script-text"><?php echo \Engine\Engine::GetAnalyticScript(); ?></textarea>
                    <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_panel.statistic_panel.text_to_insert_tip");?></div>
                </div>
            </div>
        </div>
        <hr/>
        <div class="btn-group" role="group">
            <button type="submit" class="btn btn-default" name="save_cfg_button"><?php echo \Engine\LanguageManager::GetTranslation("apply");?></button>
            <button type="button" class="btn btn-default" name="restart_cfg_button"><?php echo \Engine\LanguageManager::GetTranslation("cancel");?></button>
        </div>
    </form>
</div>
<script>
    var HandleTurner = function (bool){
        var Lever = $("p#btn-status-block");
        if (bool === true) {
            $(Lever).show();
        } else {
            $(Lever).hide();
        }
    };

    var LycantropyActiveButton = function(bool){
        var btn = $("p#btn-status-block > .btn");
        if (bool === true){
            btn.removeClass("btn-danger");
            btn.addClass("btn-success");
            $(btn).html("Включено");
        }
        else {
            btn.removeClass("btn-success");
            btn.addClass("btn-danger");
            $(btn).html("Выключено");
        }
    };

    var GetPluginStatus = function (){
        var pluginInstalled = document.getElementById("template_official");
        var plugCodeName = pluginInstalled.options[pluginInstalled.selectedIndex].dataset.codename;
        $.ajax({
           url: "adminpanel/scripts/ajax/pluginsajax.php",
           type: "POST",
           data: "getModePlugin=1&codename=" + plugCodeName,
           success: function(data){
               switch (data){
                   case "1":
                       LycantropyActiveButton(true);
                       break;
                   case "0":
                       LycantropyActiveButton(false);
                       break;
               }
           }
        });
    };

    var ChangeStatus = function (){
        var pluginInstalled = document.getElementById("template_official");
        var plugCodeName = pluginInstalled.options[pluginInstalled.selectedIndex].dataset.codename;
        var btn = $("button#plugin-status");
        if (btn.hasClass("btn-danger")){
          $.ajax({
              url: "adminpanel/scripts/ajax/pluginsajax.php",
              type: "POST",
              data: "turnModePlugin=1&mode=1&codename=" + plugCodeName,
              success: function(){
                  LycantropyActiveButton(true);
              }
          });
        }
        if (btn.hasClass("btn-success")){
            $.ajax({
                url: "adminpanel/scripts/ajax/pluginsajax.php",
                type: "POST",
                data: "turnModePlugin=1&mode=0&codename=" + plugCodeName,
                success: function(){
                    LycantropyActiveButton(false);
                }
            });
        }
    };

    var MetricSystemGUIPrepare = function() {
        if ($("#metric-level-btn").is(":checked")){
            $("#metric-information").show();
        } else {
            $("#metric-information").hide();
        }
    };

    var ClearFieldForm = function(){
        $("#field-panel-manage > div > input").val("");
        $("#field-panel-manage > div > textarea").val("");
        $("#field-panel-manage > div > select").val(1);
        $("#field-panel-manage > div > div > input[type=checkbox]").prop("checked", false);
    };

    var ShowAnswerForm = function (type, text){
        var span = $("#add-fields-info-div > span");
        var div = $("#add-fields-info-div");
        $(div).show();
        switch(type){
            case 1:
            case "success":
            case "okey": {
                $(div).html("");
                $(div).append($(span));
                $(div).removeClass("hidden");
                $(div).addClass("alert-success");
                $(span).prop("class", "");
                $(span).addClass("glyphicons glyphicons-ok");
                $(span).after(" " + text);
                break;
            }
            case 0:
            case "error":
            case "failed":
            case "fail": {
                $(div).html("");
                $(div).append($(span));
                $(div).removeClass("hidden");
                $(div).addClass("alert-danger");
                $(span).prop("class", "");
                $(span).addClass("glyphicons glyphicons-remove");
                $(span).after(" " + text);
                break;
            }
        }
        $('html, body').animate({
            scrollTop: $(div).offset().top-100
        }, 1000);
    };

    $("p#btn-status-block").hide();

    $("#field-add-btn").on("click", function() {
       $(this).attr("disabled", true);
       $("#field-panel-manage").show();
       $("#user-add-fields").val(0);
       ClearFieldForm();
        $("#custom-value-input-div").hide();
        $("#require-input-div").hide();
        $("#see-on-reg-input-div").hide();
        $("#private-input-div").hide();
        $("#link-input-div").hide();
    });

    $("#field-cancel-btn").on("click", function () {
        $("#field-add-btn").attr("disabled", false);
        $("#field-remove-btn").attr("disabled", true);
        $("#field-panel-manage").hide();
        ClearFieldForm();
    });

    $("input#maxfilesize").on("input", function(){
       var val = $(this).val();
       var span = $("#file-max-size-inmb");
       val = val / 1024 / 1024;
       $(span).html(val);
    });

    //Save or create additional field.
    $("#field-add-ajax-btn").on("click", function() {
       var action = ($("#user-add-fields").val() != 0) ? "edit" : "add";
       var dataVar = "field-id=" + $("#user-add-fields").val() +
                     "&field-name=" + $("#field-name-input").val() +
                     "&field-description=" + $("#field-description").val() +
                     "&field-type=" + $("#field-type-selector").val() +
                     "&field-isreq=" + $("#field-requied").is(":checked") +
                     "&field-inregister=" + $("#field-reg-show").is(":checked") +
                     "&field-privatestat=" + $("#field-private").is(":checked") +
                     "&field-link=" + $("#field-link-textarea").val() +
                     "&field-custom=" + $("#custom-value-input").val() +
                     "&action=" + action;
       $.ajax({
           url: "adminpanel/scripts/ajax/adfieldsajax.php",
           type: "POST",
           data: dataVar,
           success: function (data){
                if (action === "add"){
                    if ($.isNumeric(data)){
                        $("#user-add-fields").append("<option value=\"" + data + "\">" + $("#field-name-input").val() + "</option>");
                        ShowAnswerForm(1, "<?php echo \Engine\LanguageManager::GetTranslation("settings_panel.js.field"); ?> " + $("#field-name-input").val() +
                            "\" <?php  echo \Engine\LanguageManager::GetTranslation("settings_panel.js.has_been_created_successfuly"); ?>");
                        $("#field-cancel-btn").click();
                    } else if (data == "in") {
                        ShowAnswerForm("error", "<?php  echo \Engine\LanguageManager::GetTranslation("settings_panel.js.invalid_name"); ?>");
                    }
                }
                if (action === "edit") {
                    if (data === "sef") {
                        $("#field-cancel-btn").click();
                        $("#user-add-fields").val(0);
                        $("#field-panel-manage").hide();
                        ShowAnswerForm("okey", "<?php  echo \Engine\LanguageManager::GetTranslation("settings_panel.js.field_settings_changed");  ?>");
                    } else if (data == "fef") {
                        ShowAnswerForm("error", "<?php  echo \Engine\LanguageManager::GetTranslation("settings_panel.js.failed_edit_field"); ?>");
                    } else if (data == "fne") {
                        ShowAnswerForm("error", "<?php  echo \Engine\LanguageManager::GetTranslation("settings_panel.js.field_does_not_exist");  ?>");
                    } else if (data == "in") {
                        ShowAnswerForm("error", "<?php   echo \Engine\LanguageManager::GetTranslation("settings_panel.js.invalid_name");  ?>");
                    }
                }
           },
           error: function (){
               $("#add-fields-info-div").removeClass("hidden");
               $("#add-fields-info-div").addClass("alert-error");
               $("#add-fields-info-div > span").addClass("glyphicons glyphicons-remove");
               $("#add-fields-info-div > span").after(" <?php   echo \Engine\LanguageManager::GetTranslation("settings_panel.js.field_has_not_been_saved");  ?>")
           }
       });
    });
    //Change form view in dependence of type additive field.
    $("#field-type-selector").on("change", function() {
        var type = $("#field-type-selector").val();
        $("#custom-value-input-div").hide();
        $("#require-input-div").hide();
        $("#see-on-reg-input-div").hide();
        $("#private-input-div").hide();
        $("#link-input-div").hide();
        $("#field-requied").prop("checked", false);
        $("#field-reg-show").prop("checked", false);
        $("#field-private").prop("checked", false);
        $("#custom-value-input").val("");
        $("#field-link-textarea").val("");
        if (type == 1){
            $("#custom-value-input-div").hide();
            $("#require-input-div").hide();
            $("#see-on-reg-input-div").hide();
            $("#private-input-div").hide();
            $("#link-input-div").hide();
        }
        if (type == 2){
            $("#require-input-div").show();
            $("#see-on-reg-input-div").show();
            $("#private-input-div").show();
            $("#link-input-div").show();
        }
        if (type == 3){
            $("#custom-value-input-div").show();
            $("#require-input-div").hide();
            $("#see-on-reg-input-div").hide();
            $("#private-input-div").hide();
            $("#link-input-div").hide();
        }
    });
    //Get info about the additional field.
    $("#user-add-fields").on("change", function() {
       var id = $("#user-add-fields").val();
       if (id > 0){
           $("#add-fields-info-div").hide();
           $("#field-remove-btn").prop("disabled", false);
           $.ajax({
               url: "adminpanel/scripts/ajax/adfieldsajax.php",
               type: "POST",
               data: "action=get&field-id=" + id,
               success: function (data){
                   var result = $.parseJSON(data);
                   $("#custom-value-input-div").hide();
                   $("#require-input-div").hide();
                   $("#see-on-reg-input-div").hide();
                   $("#private-input-div").hide();
                   $("#link-input-div").hide();
                   $("#field-panel-manage").show();
                   $("#field-name-input").val(result.name);
                   $("#field-description").val(result.description);
                   $("#field-type-selector").val(result.type);
                   if ($("#field-type-selector").val() == 1) {
                       $("#custom-value-input-div").hide();
                       $("#require-input-div").hide();
                       $("#see-on-reg-input-div").hide();
                       $("#private-input-div").hide();
                       $("#link-input-div").hide();
                   }
                   if ($("#field-type-selector").val() == 2) {
                       $("#require-input-div").show();
                       $("#see-on-reg-input-div").show();
                       $("#private-input-div").show();
                       $("#link-input-div").show();
                       if (result.isRequied == "1")
                           $("#field-requied").prop("checked", true);
                       else
                           $("#field-requied").prop("checked", false);
                       if (result.inRegister == "1")
                           $("#field-reg-show").prop("checked", true);
                       else
                           $("#field-reg-show").prop("checked", false);
                       if (result.canBePrivate == "1")
                           $("#field-private").prop("checked", true);
                       else
                           $("#field-private").prop("checked", false);
                       $("#field-link-textarea").val(result.link);
                   }
                   if ($("#field-type-selector").val() == 3) {
                       $("#custom-value-input-div").show();
                       $("#require-input-div").hide();
                       $("#see-on-reg-input-div").hide();
                       $("#private-input-div").hide();
                       $("#link-input-div").hide();
                       $("#custom-value-input").val(result.custom);
                   }
               }
           });
       } else {
           $("#field-remove-btn").attr("disabled", true);
           $("#field-panel-manage").hide();
       }
    });

    //Delete additional field.
    $("#field-remove-btn").on("click", function() {
       if ($("#user-add-fields").val() != 0){
           var id = $("#user-add-fields").val();
            $.ajax({
                url: "adminpanel/scripts/ajax/adfieldsajax.php",
                type: "POST",
                data: "action=delete&field-id=" + id,
                success: function (data){
                    if (data == "sdf"){
                        ShowAnswerForm("okey", "<?php   echo \Engine\LanguageManager::GetTranslation("settings_panel.js.field_has_been_removed");  ?>");
                        $("#user-add-fields").children("option[value=" + id + "]").remove();
                        $("#user-add-fields").val(0);
                        $("#field-cancel-btn").click();
                    } else if (data == "fdf"){
                        //Failed deleting.
                        ShowAnswerForm("fail", "<?php    echo \Engine\LanguageManager::GetTranslation("settings_panel.js.failed_to_remove_field");  ?>");
                    } else if (data == "fne"){
                        ShowAnswerForm("fail", "<?php    echo \Engine\LanguageManager::GetTranslation("settings_panel.js.field_does_not_exist");  ?>");
                    }
                }
            });
       } else {
           $(this).prop("disabled", true);
       }
    });

    $("#mail-test-ajax-btn").on("click", function () {
       $.ajax({
           url: "adminpanel/scripts/ajax/testmailajax.php",
           type: "POST",
           data: "test=1",
           success: function (data){
               if (data == "okey")
                   alert("<?php    echo \Engine\LanguageManager::GetTranslation("settings_panel.js.test_mail_has_been_sended_successfuly");  ?>");
               else if (data == "false")
                   alert("<?php    echo \Engine\LanguageManager::GetTranslation("settings_panel.js.test_mail_has_not_been_sended");  ?>");
           }
       });
    });

    $(document).ready(MetricSystemGUIPrepare);

    $("#metric-level-btn").on("change", MetricSystemGUIPrepare);

    $("#metric-code-div").show();

    $("button").on("click", function() {
       if ($(this).data("div-number") != undefined){
           var divNum = $(this).data("div-number");
           $("div.custom-group > div.div-border").hide();
           $("div.custom-group > div.div-border[data-number=" + divNum +"]").show();
           $("button").removeClass("active");
           $("button[data-div-number=" + divNum + "]").addClass("active");
       }
    });

    $("select#template_ready_to_install").on("click", function() {
       if (this.selectedIndex != -1){
           var pluginToInstall = document.getElementById("template_ready_to_install");
           var plugCodeName = pluginToInstall.options[pluginToInstall.selectedIndex].dataset.codename;

           $("h3#plugin-name").html(this.options[this.selectedIndex].innerHTML);
           $("button#btn-install").attr("disabled", false);
           var pluginToInstall = document.getElementById("template_ready_to_install");
           $.ajax({
               url: "adminpanel/scripts/ajax/pluginsajax.php",
               type: "POST",
               data: "descriptionPlugin=1&pluginCodeName=" + plugCodeName,
               success: function (data){
                   $("span#plugin-description").html(data);
                   HandleTurner(false);

               }
           });
       }
    });

    $("select#template_official").on("click", function() {
        if (this.selectedIndex != -1) {
            var pluginInstalled = document.getElementById("template_official");
            var plugCodeName = pluginInstalled.options[pluginInstalled.selectedIndex].dataset.codename;
            $("h3#plugin-name").html(this.options[this.selectedIndex].innerHTML);
            $("button#btn-install").attr("disabled", true);
            $("button#btn-delete").attr("disabled", false);
            $.ajax({
                url: "adminpanel/scripts/ajax/pluginsajax.php",
                type: "POST",
                data: "descriptionPlugin=1&pluginCodeName=" + pluginInstalled.options[pluginInstalled.selectedIndex].dataset.codename,
                success: function (data) {
                    $("span#plugin-description").html(data);
                    HandleTurner(true);
                    GetPluginStatus();
                }
            });
        } else {
            $("button#btn-install").attr("disabled", true);
            $("button#btn-delete").attr("disabled", true);
        }
    });

    $("button#btn-install").click(function(){
        var pluginToInstall = document.getElementById("template_ready_to_install");
        var pluginInstalled = document.getElementById("template_official");
        pluginInstalled.append(pluginToInstall.options[pluginToInstall.selectedIndex]);
        $.ajax({
            url: "adminpanel/scripts/ajax/pluginsajax.php",
            type: "POST",
            data: "installPlugin=1&pluginCodeName=" + pluginInstalled.options[pluginInstalled.selectedIndex].dataset.codename,
            success: function(){
                HandleTurner(true);
                //LycantropyActiveButton(true);
                GetPluginStatus();
            }
        });
    });

    $("button#btn-delete").click(function() {
        var pluginToInstall = document.getElementById("template_ready_to_install");
        var pluginInstalled = document.getElementById("template_official");
        pluginToInstall.append(pluginInstalled.options[pluginInstalled.selectedIndex]);
        $.ajax({
            url: "adminpanel/scripts/ajax/pluginsajax.php",
            type: "POST",
            data: "deletePlugin=1&pluginCodeName=" + pluginToInstall.options[pluginToInstall.selectedIndex].dataset.codename
        });
        if ($("select#template_official").selectedIndex == -1 || $("select#template_official option").length == 0)
            $(this).attr("disabled", true);
        HandleTurner(false);
    });

    $("select#template_ready_to_install").click(function() {
       $("button#btn-delete").prop('disabled', true);
    });

    $("#plugin-status").on("click", function(){
       ChangeStatus();
    });

    <?php if ($user->UserGroup()->getPermission("look_statistic")) { ?>
        $("#metric-type-info").on("change", function(){
           if ($(this).val() >= 2){
               $("#metric-code-js-div").show();
           } else {
               $("#metric-code-js-div").hide();
           }
        });
    <?php } ?>
</script>
<?php } ?>