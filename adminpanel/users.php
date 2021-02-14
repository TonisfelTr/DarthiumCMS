<?php
if (!defined("TT_AP")){ header("Location: ../adminapanel.php?p=forbidden"); exit; }

$canSeeProfiles = $user->UserGroup()->getPermission("user_see_foreign");
$canChangeProfiles = $user->UserGroup()->getPermission("change_another_profiles");
$canUserBan = $user->UserGroup()->getPermission("user_ban");
$canUserUnban = $user->UserGroup()->getPermission("user_unban");
$canIPBan = $user->UserGroup()->getPermission("user_banip");
$canIPUnban = $user->UserGroup()->getPermission("user_unbanip");
$canSigns = $user->UserGroup()->getPermission("user_add");

if (!$canSeeProfiles &&
    !$canChangeProfiles &&
    !$canUserBan &&
    !$canUserUnban &&
    !$canIPBan &&
    !$canIPUnban &&
    !$canSigns){ header("Location: ../adminpanel.php?res=1"); exit; }

function setVisible($bool){
    if ($bool)
        return "";
    else return "hidden";
}

/**
 * @param $id
 * @param $name
 * @param $content
 * @param null $link
 * @return string
 */
function constructDiv($id, $name, $content, $link = null){
    if ($link != null){
        $link = str_replace("{{1}}", $content, $link);
        $linkButton = "<span class=\"input-group-btn\">
                        <button class=\"btn btn-default\" type=\"button\" title=\"". \Engine\LanguageManager::GetTranslation("users_panel.open") . "\" onclick=\"window.open('$link');\">
                            <span class=\"glyphicons glyphicons-new-window\"></span>
                        </button>
                    </span>";
    } else $linkButton = "";
    return "<div class=\"input-group\">
                <div class=\"input-group-addon\">$name</div>
                <input class=\"form-control\" type=\"text\" value=\"$content\" name=\"user-edit-$id\">
                $linkButton
            </div>";
}
if(!empty($_GET["uid"])) {
    if (\Users\UserAgent::IsUserExist($_GET["uid"])) {
        $userExists = true;
        $USER = new \Users\User($_GET["uid"]);

        ///////////////////////////////////////////////////////////////////////
        /// Build additional fields mechanism.
        ///////////////////////////////////////////////////////////////////////

        $additionalFields = \Users\UserAgent::GetAdditionalFieldsList();
        $userAdFields = $USER->getAdditionalFields();
        $customAF = [];
        $contactAF = [];
        $infoAF = [];
        foreach($additionalFields as $field){
            if ($field["type"] == 1){
                array_push($infoAF, constructDiv($field["id"], $field["name"], $userAdFields[$field["id"]]["content"]));
            }
            if ($field["type"] == 2) {
                array_push($contactAF, constructDiv($field["id"], $field["name"], $userAdFields[$field["id"]]["content"]));
            }
            if ($field["type"] == 3){
                if ($userAdFields[$field["id"]]["content"] != "")
                    array_push($contactAF, constructDiv($field["id"], $field["name"], $userAdFields[$field["id"]]["content"]));
                else
                    array_push($contactAF, constructDiv($field["id"], $field["name"], $field["custom"]));
            }
        }

        $infoAFJoined = implode("", $infoAF);
        $customAFJoined = implode("", $customAF);
        $contactAFJoined = implode("", $contactAF);

        //End building.
    }
} else $userExists = False;
# Проверка на права на просмотр чужих профилей.

#Код для поиска пользователей.
if ($canSeeProfiles){
    #############################################################################
    # Составление списка групп для селекторов.
    # $groupList - массив id.
    # $groupNames - массив имён групп в $groupList.
    # $groupCount - кол-во групп всего.
    #############################################################################
    $groupList = \Users\GroupAgent::GetGroupList();
    $groupCount = count($groupList)-1;
    $groupNames = array();
    foreach ($groupList as $gl) {
        $var = \Users\GroupAgent::GetGroupNameById($gl);
        array_push($groupNames, $var);
    }
    #############################################################################
    # Составление таблицы пользователей для поиска.
    # $userList - массив id.
    # $userCount - кол-во пользователей для показа.
    # $findShedule - шаблон поиска
    # $usersCount - кол-во зарегистрированных пользователей
    #############################################################################
    if ((isset($_REQUEST["fnn"])) || (isset($_REQUEST["frid"])) || (isset($_REQUEST["fgroup"])) ||
        (isset($_REQUEST["flip"])) || (isset($_REQUEST["femail"])))
        $userParams = ["nickname" => (!isset($_REQUEST["fnn"])) ? "%" : $_REQUEST["fnn"],
                       "referer" =>  (!isset($_REQUEST["frid"])) ? "%" : $_REQUEST["frid"],
                       "group" =>  (!isset($_REQUEST["fgroup"])) ? "%" : $_REQUEST["fgroup"],
                       "lastip" =>  (!isset($_REQUEST["flip"])) ? "%" : $_REQUEST["flip"],
                       "email" =>  (!isset($_REQUEST["femail"])) ? "%" : $_REQUEST["femail"]];
    else $userParams = [];
    $formLink = "";
    if (isset($_REQUEST["fnn"])) $formLink .= "&paramType=nickname&user-data-input=" . $_REQUEST["fnn"];
    if (isset($_REQUEST["frid"])) $formLink .= "&paramType=referer&user-data-input=" . $_REQUEST["frid"];
    if (isset($_REQUEST["flip"])) $formLink .= "&paramType=lastip&user-data-input=" . $_REQUEST["flip"];
    if (isset($_REQUEST["femail"])) $formLink .= "&paramType=email&user-data-input=" . $_REQUEST["femail"];
    $userList = \Users\UserAgent::GetUsersList($userParams, (isset($_REQUEST["fpage"])) ? $_REQUEST["fpage"] : 1);
    $userCount = count($userList);
    $usersCount = \Users\UserAgent::GetUsersCount();
}
if ($canUserBan || $canUserUnban){
    ################################################################################
    # Состалвение списка забаненных.
    # $bannedList - список забаненных вообще.
    # $bannedCount = кол-во людей для отображения.
    # $banSearchActive - был ли запрос на поиск.
    ################################################################################

    $bannedList = \Guards\SocietyGuard::GetBanUserList(isset($_REQUEST["bpage"]) ? $_REQUEST["bpage"] : 1);
    $bannedCount = count($bannedList);
    $banSearchActive = False;
    if (isset($_REQUEST["fbnn"]) || isset($_REQUEST["fbr"])){
        $banSearchActive = True;
        $arrayForSearch = ["nickname" => (isset($_REQUEST["fbnn"])) ? $_REQUEST["fbnn"] : "",
                           "reason" => (isset($_REQUEST["fbr"])) ? $_REQUEST["fbr"] : "" ];
        $bannedList = \Guards\SocietyGuard::GetBanListByParams($arrayForSearch, (isset($_REQUEST["bpage"])) ? $_REQUEST["bpage"] : 1);
        $bannedCount = count($bannedList);
        if(!empty($_REQUEST["fbnn"])) $formLink .= "&user_ban_input=" . $_REQUEST["fbnn"];
        if(!empty($_REQUEST["fbr"])) $formLink .= "&user_ban_reason=" . $_REQUEST["fbr"];
    }
}
if ($canIPBan || $canIPUnban){
    #############################################################
    # Раздел бана IP адресов и разбана соответственно.
    # $banipList - список забаненных.
    # $banipCount - кол-во забаненных.
    #############################################################
    $banipList = \Guards\SocietyGuard::GetIPBanList((isset($_REQUEST["bipage"])) ? $_REQUEST["bipage"] : 1);
    $banipCount = count($banipList);
}
if ($canSigns){
    $groupList = \Users\GroupAgent::GetGroupList();
}

?>
<div class="inner cover">
    <h1 class="cover-heading"><?=\Engine\LanguageManager::GetTranslation("users_panel.panel_name")?></h1>
    <p class="lead"><?=\Engine\LanguageManager::GetTranslation("users_panel.panel_description")?></p>
    <div class="btn-group" id="btn-show-panel">
        <?php
        #Поверка на возможность просматривать чужие профили. Если да, то можно и исктаь пользователей.
        if ($canSeeProfiles){ ?>
        <button type="button" class="btn btn-default<?php echo (!isset($_REQUEST["reqtype"]) && empty($_GET["uid"])) ? " active" : ""; ?>" id="find" onclick="showToBelow('user-finder', 'find')"><span class="glyphicons glyphicons-vcard"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.search_user")?></button>
        <?php } if ($canSigns) { ?>
        <button type="button" class="btn btn-default<?php echo (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 3) ? " active" : ""; ?>" id="add" onclick="showToBelow('user-signup', 'add')"><span class="glyphicons glyphicons-user-vr-add"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.register_user")?></button>
        <?php } if ($canUserBan || $canUserUnban) { ?>
        <button type="button" class="btn btn-default<?php echo (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 1) ? " active" : ""; ?>" id="banning" onclick="showToBelow('user-banned', 'banning')"><span class="glyphicons glyphicons-user-ban"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.ban_users")?></button>
        <?php } if ($canIPBan || $canIPUnban) {?>
        <button type="button" class="btn btn-default<?php echo (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 2) ? " active" : ""; ?>" id="banningip" onclick="showToBelow('user-bannedip', 'banningip')"><span class="glyphicons glyphicons-ban-circle"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.banip_users")?></button>
        <?php } if ($canSeeProfiles && $canChangeProfiles && $userExists) { ?>
        <button type="button" class="btn btn-default alert-info active" id="user-edit" onclick="showToBelow('user-editor', 'user-edit')"><span class="glyphicon glyphicon-user"></span> <?php echo $USER->getNickname(); ?> - <?=\Engine\LanguageManager::GetTranslation("users_panel.editing")?></button>
        <?php } ?>
    </div>
    <form enctype="multipart/form-data" method="post" action="adminpanel/scripts/userer.php" name="user-form" id="user-form">
        <div class="custom-group">
            <?php
            #Поверка на возможность просматривать чужие профили. Если да, то можно и искать пользователей.
            if ($canSeeProfiles){ ?>
            <div class="div-border" id="user-finder" <?php if ((isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] != 0) || !empty($_GET["uid"])) echo setVisible(false);?>>
                <h2><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user")?></h2>
                <p class="helper"><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.panel_description")?></p>
                <hr>
                <div class="alert alert-info" <?php if (!isset($_REQUEST["fnn"]) && !isset($_REQUEST["frid"]) && !isset($_REQUEST["fgroup"]) &&
                                                        !isset($_REQUEST["flip"]) && !isset($_REQUEST["femail"])) echo "hidden";?>>
                    <span class="glyphicons glyphicons-search"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.filters")?>
                    <hr>
                    <?php if (isset($_REQUEST["fnn"])) echo "<strong>" . \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.nickname") ."</strong> " . htmlentities($_REQUEST["fnn"]) . "<br>";
                          if (isset($_REQUEST["frid"])) echo "<strong>" . \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.referer") . "</strong> " . htmlentities($_REQUEST["frid"]) . "<br>";
                          if (isset($_REQUEST["fgroup"])) echo "<strong>" . \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.group") .  "</strong> " . \Users\GroupAgent::GetGroupNameById($_REQUEST["fgroup"]) . "<br>";
                          if (isset($_REQUEST["flip"])) echo "<strong>" . \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.last_ip") . "</strong> " . htmlentities($_REQUEST["flip"]) . "<br>";
                          if (isset($_REQUEST["femail"])) echo "<strong>" . \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.email") . "</strong> " . htmlentities($_REQUEST["femail"]) . "<br>";?>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control" name="user-data-input" id="user-data-input" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.js.nickname")?>">
                    <div class="input-group-btn" id="user-data-typer">
                        <button class="btn btn-default active" type="button" onclick="findByNickname();" id="data-nickname" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.js.nickname")?>"><span class="glyphicon glyphicon-tags"></span></button>
                        <button class="btn btn-default" type="button" onclick="findByEmail();" id="data-email" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.email_title")?>"><span class="glyphicon glyphicon-envelope"></span></button>
                        <button class="btn btn-default" type="button" onclick="findByReferer();" id="data-referer" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.referer_title")?>"><span class="glyphicons glyphicons-old-man"></span></button>
                        <button class="btn btn-default" type="button" onclick="findByIP();" id="data-ip" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.last_ip_title")?>"><span class="glyphicons glyphicons-globe-af"></span></button>
                    </div>
                </div><br>
                <p><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.panel_tip")?></p>
                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.referer_tip")?></div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.search_in_group")?></div>
                    <select class="form-control" name="fgroup" id="user-group-selector">
                        <option value="0"><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.not_selected")?></option>
                        <?php for ($i = 0; $i <= $groupCount; $i++){
                                print("<option value='" . $groupList[$i] . "'");
                                if (isset($_REQUEST["fgroup"])) if ($groupList[$i] == $_REQUEST["fgroup"]) print(" selected");
                                print(">" . htmlentities($groupNames[$i]) . "</option>");
                              } ?>
                    </select>
                </div>
                <br>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" name="users-find-button"><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.search_users")?></button>
                    <?php if ($user->UserGroup()->getPermission("user_remove")) {?><button class="btn btn-default alert-danger" type="button" name="users-delete-button" id="users-delete-button" disabled><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.remove_users")?></button><?php } ?>
                    <a class="btn btn-default" href="adminpanel.php?p=users" name="users-reset-button"><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.clear_filters")?></a>
                </div>
                <hr>
                <h2><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.search_result")?></h2>
                <p id="users-selected-counter" class="alert alert-info" hidden><strong><?=\Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.selected_users_count")?></strong> <span id="users-selected-counter-span"></span>.</p>
                <div class="table-responsive">
                    <table id="users-find-results" class="table">
                        <thead style="background: radial-gradient(at right center, #874c15, #cc8c53); color: white; text-shadow: 2px 2px 3px black;">
                        <tr>
                            <td><input type="checkbox" id="users-select-all" name="allselectorcheck"
                                       title="Выбрать всех"></td>
                            <td>ID</td>
                            <td><?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.table_nickname") ?></td>
                            <td><?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.table_group") ?></td>
                            <td><?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.table_email") ?></td>
                            <td><?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.table_last_ip") ?></td>
                            <td><?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.table_reg_ip") ?></td>
                            <td><?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.table_last_sign_in") ?></td>
                            <td><?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.table_rate") ?></td>
                            <td><?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.table_banned") ?></td>
                            <?php if ($user->UserGroup()->getPermission("change_another_profiles")) echo "<td></td>"; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        # Создание таблицы при отсутствии пользователей.
                        if ($userCount == 0) {
                            ?>
                            <tr>
                                <td colspan="11" class="center alert alert-info"><span
                                            class="glyphicon glyphicon-info-sign"></span> <?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.no_found_users") ?>
                                </td>
                            </tr>
                        <?php }
                        # Создание таблицы при наличии в списке хотя бы одного пользователя.
                        foreach ($userList as $User) {
                            $selUser = new \Users\User($User["id"]);
                            if ($selUser->isBanned() === true) $selUserBan = \Engine\Engine::DateFormatToRead(date("Y-m-d", \Guards\SocietyGuard::GetBanUserParam($User["id"], "banned_time")));
                            else $selUserBan = \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.not_banned"); ?>
                            <tr>
                                <td><input type="checkbox"
                                           data-uid-selected="<?php print(htmlentities($User["id"])); ?>"
                                           title="<?= \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.select") ?>">
                                </td>
                                <td><?php print(htmlentities($User["id"])); ?></td>
                                <td><?php print(htmlentities($selUser->getNickname())); ?></td>
                                <td><?php print("<span style=\"color: " . htmlentities($selUser->UserGroup()->getColor()) . ";\">" . htmlentities($selUser->UserGroup()->getName()) . "</span>"); ?></td>
                                <td><?php print(htmlentities($selUser->getEmail())); ?></td>
                                <td><?php print(htmlentities(($selUser->getLastIp() != "null") ? $selUser->getLastIp() : \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.not_sign_in_he") . (($selUser->getSex() == 2) ? \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.not_sign_in_she") : ""))); ?></td>
                                <td><?php print(htmlentities($selUser->getRegIp())); ?></td>
                                <td><?php print($selUser->getLastDate() == "1970-01-01") ? (($selUser->getSex() == 2)) : (htmlentities(\Engine\Engine::DateFormatToRead(($selUser->getLastDate())))); ?></td>
                                <td><?php print(htmlentities($selUser->getReputation()->getReputationPoints()) . " " . \Engine\LanguageManager::GetTranslation("users_panel.search_user_panel.point")); ?></td>
                                <td><?php print(htmlentities($selUserBan)); ?></td>
                                <?php if ($canSeeProfiles) { ?>
                                    <td class="alert-info">
                                        <button class="btn btn-default alert-info" style="width: 100%;" type="submit"
                                                formaction="adminpanel/scripts/userer.php?uide=<?php print(htmlentities($User["id"])); ?>"><?= \Engine\LanguageManager::GetTranslation("edit") ?></button>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="btn-group center">
                    <?php if ($usersCount > 50) {
                        for ($i = 1; $i <= ceil($usersCount / 50); $i++) { ?>
                    <button class="btn btn-default <?php echo (isset($_REQUEST["fpage"]) && $_REQUEST["fpage"] == $i) ? "selected" : ""; ?>"
                        type="submit" formaction="adminpanel/scripts/userer.php?fpage=<?php echo $i . $formLink;?>"><?php echo $i; ?></button>
                    <?php } } ?>
                </div>
            </div>
            <?php }
            if ($canUserBan || $canUserUnban) { ?>
                <div class="div-border" id="user-banned" <?php if (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 1) echo setVisible(true); else echo setVisible(false);?>>
                    <h2><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_users")?></h2>
                    <p class="helper"><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.panel_description")?></p>
                    <hr>
                    <?php
                    if ($banSearchActive){ ?>
                        <div class="alert alert-info">
                            <span class="glyphicon glyphicon-search"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.filters")?>
                            <hr>
                            <?php if (!empty($_REQUEST["fbnn"])) { ?> <strong>Никнейм:</strong> <?php echo htmlentities($_REQUEST["fbnn"]); } ?>
                            <?php if (!empty($_REQUEST["fbr"])) { ?> <strong>Причина:</strong> <?php echo htmlentities($_REQUEST["fbr"]); } ?>
                        </div>
                    <?php } ?>
                    <p><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.panel_tip")?></p>
                    <input type="text" class="form-control" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.nickname")?>" id="user_ban_input" name="user_ban_input">
                    <input type="text" class="form-control" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.reason")?>" id="user_ban_reason" name="user_ban_reason">
                    <div class="input-group">
                        <input type="number" class="form-control" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.time_in_seconds")?>" id="user_ban_time" name="user_ban_time">
                        <div class="input-group-btn">
                            <button class="btn btn-default" type="button" onclick="secToDate();" id="data-ban-time" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.translate_to_date")?>"><span class="glyphicon glyphicon-time"></span></button>
                        </div>
                    </div>
                    <br>

                    <p><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.panel_tip_2")?></p>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.panel_tip_3")?></div>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.panel_tip_4")?></div>
                    <div class="btn-group">
                        <?php if ($canUserBan) { ?> <button class="btn btn-default" type="submit" name="user_ban_ban" id="user_ban_ban"><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.ban")?></button> <?php } ?>
                        <?php if ($canUserUnban) { ?> <button class="btn btn-default alert-warning" type="button" name="user_ban_unban" id="user_ban_unban" disabled><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.unban")?></button> <?php } ?>
                        <button class="btn btn-default" type="submit" name="user_ban_find" id="user_ban_find"><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.search")?></button>
                    </div>
                    <hr>
                    <h2><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.search_results")?></h2>
                    <div class="alert alert-info" id="users-ban-selected-counter" hidden>
                        <strong><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.selected_count")?></strong> <span id="users-ban-selected-counter-span"></span>
                    </div>
                    <div class="table-responsive">
                    <table id="users-banfind-results" class="table">
                        <thead style="background: radial-gradient(at right center, #66070a, #eb2b2c); color: white; text-shadow: 2px 4px 3px black;">
                            <tr>
                                <td><input type="checkbox" id="users-ban-select-all" name="users-ban-select-all" title="Выделить всех"></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.nickname")?></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.table_banned")?></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.table_to")?></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.table_banned_by")?></td>
                                <td><?=\Engine\LanguageManager::GetTranslation("users_panel.ban_panel.table_reason")?></td>
                                <?php if ($canUserUnban) { ?><td></td><?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bannedCount == 0 && !$banSearchActive) print("<tr><td colspan=\"7\" class=\"center alert alert-info\">" . \Engine\LanguageManager::GetTranslation("users_panel.ban_panel.no_banned_users") . "</td></tr>");
                            elseif ($bannedCount == 0 && $banSearchActive) print("<tr><td colspan=\"7\" class=\"center alert alert-info\">" . \Engine\LanguageManager::GetTranslation("users_panel.ban_panel.no_banned_users_found") . "</td></tr>");
                            else {
                                foreach ($bannedList as $ban) { ?>
                                    <tr>
                                        <td><input type="checkbox" data-bid-selected="<?php echo $ban["banned"]; ?>"></td>
                                        <td><?php echo \Users\UserAgent::GetUserNick($ban["banned"]); ?></td>
                                        <td><?php echo Engine\Engine::DateFormatToRead(date("Y-m-d", \Guards\SocietyGuard::GetBanUserParam($ban["banned"], "banned_time"))); ?></td>
                                        <td><?php echo (\Guards\SocietyGuard::GetBanUserParam($ban["banned"], "unban_time") == 0) ? \Engine\LanguageManager::GetTranslation("users_panel.ban_panel.permanently") :
                                                Engine\Engine::DateFormatToRead(date("Y-m-d", \Guards\SocietyGuard::GetBanUserParam($ban["banned"], "unban_time"))); ?></td>
                                        <td><?php echo \Users\UserAgent::GetUserNick(\Guards\SocietyGuard::GetBanUserParam($ban["banned"], "author")); ?></td>
                                        <td><?php echo htmlentities(\Guards\SocietyGuard::GetBanUserParam($ban["banned"], "reason")); ?></td>
                                        <?php if ($canUserUnban) { ?><td>
                                            <button class="btn btn-default" name="user_ban_unban" type="submit" style="width: 100%" formaction="adminpanel/scripts/userer.php?ufuban=<?php echo $ban["banned"]; ?>">Разбанить</button>
                                        </td>
                                        <?php } ?>
                                    </tr>
                                <?php }
                            }?>
                        </tbody>
                    </table>
                    </div>
                    <div class="btn-group center">
                        <?php
                        if ($bannedCount > 50) { for ($i = 1; $i <= ceil($bannedCount / 50); $i++) { ?>
                            <button class="btn btn-default <?php echo (isset($_REQUEST["bpage"]) && $_REQUEST["bpage"] == $i) ? "selected" : ""; ?>"
                                    type="submit" formaction="adminpanel/scripts/userer.php?bpage=<?php echo $i . $formLink;?>"><?php echo $i; ?></button>
                        <?php } }?>
                    </div>
                </div><?php }
            if ($canIPBan || $canIPUnban){ ?>
            <div class="div-border" id="user-bannedip" <?php if (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 2) echo setVisible(true); else echo setVisible(false);?>>
                <h2><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_users")?></h2>
                    <p class="helper"><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.panel_description")?></p>
                    <hr>
                    <p><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.panel_tip")?></p>
                    <input class="form-control" type="text" id="user-banip-input" name="user-banip-input" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.placeholder_ip")?>">
                    <input class="form-control" type="text" id="user-banip-reason" name="user-banip-reason" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.placeholder_reason")?>">
                    <input class="form-control" type="number" id="user-banip-time" name="user-banip-time" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.placeholder_time")?>">
                <hr>
                <p><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.search_tip")?></p>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span>  <?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.time_tip")?></div>
                <div class="btn-group">
                    <button class="btn btn-default" id="user_bip_ban" name="user_bip_ban"><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.ban")?></button>
                    <button class="btn btn-default alert-warning" id="user_bip_unban" name="user_bip_unban" disabled><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.unban")?></button>
                    <button class="btn btn-default" id="user_bip_search" name="user_bip_search"><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.search")?></button>
                </div>
                <h2><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.search_results")?></h2>
                <div class="alert alert-info" id="users-bip-selected-counter" hidden><strong><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.selected_ips")?> </strong><span id="users-bip-selected-counter-span"></span></div>
                <div class="table-responsive">
                <table class="table">
                    <thead style="background: radial-gradient(at right, #003eff, #10e4ff); color: white; text-shadow: 2px 2px 4px black;">
                        <tr>
                            <td><input type="checkbox" id="user-bip-select-all" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.select_all")?>"></td>
                            <td><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.table_ip")?></td>
                            <td><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.table_banned")?></td>
                            <td><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.table_time_unban")?></td>
                            <td><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.table_banned_by")?></td>
                            <td><?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.table_reason")?></td>
                            <?php if ($canIPUnban) { ?><td></td><?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($banipCount == 0){ ?><tr><td class="alert-info" colspan="7" style="text-align: center;"><span class="glyphicon glyphicon-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.banip_panel.no_banned_ips")?></td></tr> <?php }
                        else foreach ($banipList as $banIP) { ?>
                            <tr>
                                <td><input type="checkbox" data-bip-selected="<?php echo htmlentities($banIP["banned"]); ?>"></td>
                                <td><?php print htmlentities($banIP["banned"]); ?></td>
                                <td><?php print \Engine\Engine::DateFormatToRead(date("Y-m-d", \Guards\SocietyGuard::GetIPBanParam($banIP["banned"], "banned_time"))); ?></td>
                                <td><?php print (\Guards\SocietyGuard::GetIPBanParam($banIP["banned"], "unban_time") == 0) ? \Engine\LanguageManager::GetTranslation("users_panel.banip_panel.permanently") : \Engine\Engine::DateFormatToRead(date("Y-m-d",\Guards\SocietyGuard::GetIPBanParam($banIP["banned"], "unban_time"))); ?></td>
                                <td><?php print \Users\UserAgent::GetUserNick(\Guards\SocietyGuard::GetIPBanParam($banIP["banned"], "author")); ?></td>
                                <td><?php print htmlentities(\Guards\SocietyGuard::GetIPBanParam($banIP["banned"], "reason")); ?></td>
                                <?php if ($canIPUnban) { ?> <td><button style="width: 100%;" class="btn btn-default" type="submit" name="user_bip_unban" formaction="adminpanel/scripts/userer.php?ipuban=<?php print htmlentities($banIP["banned"]); ?>">Разблокировать</button></td> <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                </div>
                <div class="btn-group center">
                    <?php
                    if ($banipCount > 50) { for ($i = 1; $i <= ceil($banipCount / 50); $i++) { ?>
                        <button class="btn btn-default <?php echo (isset($_REQUEST["ibpage"]) && $_REQUEST["ibpage"] == $i) ? "selected" : ""; ?>"
                                type="submit" formaction="adminpanel/scripts/userer.php?ibpage=<?php echo $i . $formLink;?>"><?php echo $i; ?></button>
                    <?php } }?>
                </div>
            </div><?php }
            if ($canSigns) { ?>
            <div class="div-border" id="user-signup" <?php if (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 3) echo setVisible(true); else echo setVisible(false);?>>
                <h2><?=\Engine\LanguageManager::GetTranslation("users_panel.register_user")?></h2>
                <p class="helper"><?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.panel_description")?></p>
                <hr>
                <p><?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.panel_tip")?></p>
                <input class="form-control" type="text" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.placeholder_nickname")?>" id="user-add-nickname" name="user-add-nickname" autocomplete="new-password" maxlength="16">
                <input class="form-control" type="password" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.placeholder_password")?>" id="user-add-password" name="user-add-password" autocomplete="new-password" maxlength="16">
                <input class="form-control" type="email" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.placeholder_email")?>" id="user-add-email" name="user-add-email" autocomplete="off">
                <?php if ($user->UserGroup()->getPermission("change_user_group")) { ?>
                <select class="form-control" name="user-add-group" id="user-add-group">
                    <option value="0"><?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.select_group")?></option>
                    <?php foreach ($groupList as $group) {
                         echo "<option value\"" . $group["id"] . "\">" . \Users\GroupAgent::GetGroupNameById($group["id"]) . "</option>";
                    } ?>
                </select>
                <?php } ?>
                <br>
                <?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.panel_fields_required_tip")?>
                <hr>
                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.nickname_tip")?> </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.group_tip")?> </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.activation_tip")?> </div>
                <hr>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" id="user-add-add" name="user-add-add"><span class="glyphicon glyphicon-ok"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.register")?></button>
                    <button class="btn btn-default" type="reset" id="user-add-reset" name="user-add-reset"><span class="glyphicon glyphicon-erase"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.register_panel.clear_form")?></button>
                </div>
            </div><?php }
            if ($canSeeProfiles && $canChangeProfiles && $userExists) { ?>
            <div class="div-border" id="user-editor" <?php if ($userExists) echo setVisible(true); else echo setVisible(false); ?>>
                <h2><?php print $USER->getNickname(); ?></h2>
                <p class="helper"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.panel_description")?></p>
                <hr>
                <p><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.panel_tip")?></p>
                <div class="input-group">
                    <div class="input-group-addon">ID:</div>
                    <input type="text" readonly class="form-control alert-info" value="<?php echo $USER->getId();?>" name="user-edit-id">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.status")?></div>
                    <div class="form-control alert-info">
                    <?php $lastOnline = 0;
                    if ($user->getLastTime() == 0){
                        $lastOnline = ($USER->getSex() == 3) ? \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.she_not_signed_in") : \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.he_not_signed_in");
                    } else
                        $lastOnline = (\Engine\Engine::GetSiteTime() > $USER->getLastTime()+15*60) ?
                            (($USER->getSex() == 3) ? \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.she_signed_in") :
                            \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.he_signed_in"))
                        . " " . \Engine\LanguageManager::GetTranslation("in") . " " . \Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:i:s",$USER->getLastTime())) : "<span style=\"color: #009900;\">" . \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.online") . "</span>";
                    echo $lastOnline; ?>
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.activation")?></div>
                    <div class="form-control alert-info"><?php echo ($USER->getActiveStatus() === true) ? \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.active") : $USER->getActivationCode();?></div>
                    <?php if ($USER->getActiveStatus() != true) { ?>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.to_active")?>" name="user-edit-activate">
                            <span class="glyphicons glyphicons-ok"></span>
                        </button>
                    </span>
                    <?php } ?>
                </div>
                <hr>
                <h4><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.custom_info")?></h4>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.nickname")?></div>
                    <input class="form-control" type="text" value="<?php echo $USER->getNickname();?>" maxlength="16" name="user-edit-nickname">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.change_password")?></div>
                    <input class="form-control" type="password" placeholder="<?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.change_password_tip")?>" maxlength="16" name="user-edit-password">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.email")?></div>
                    <input class="form-control" value="<?php echo $USER->getEmail();?>" type="email" name="user-edit-email">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.send_a_mail")?>" onclick="window.open('mailto:<?php echo $USER->getEmail();?>');">
                            <span class="glyphicons glyphicons-message-full"></span>
                        </button>
                    </span>
                </div>
                <?php if ($user->UserGroup()->getPermission("change_user_group")){ ?>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.group")?></div>
                    <select class="form-control" name="user-edit-group">
                        <option value="0"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.select_group")?></option>
                        <?php for($i = 0; $i < count($groupList = \Users\GroupAgent::GetGroupList()); $i++){
                            echo "<option value=\"".$groupList[$i]."\"";
                            if ($USER->getGroupId() == $groupList[$i]) echo "selected";
                            echo ">" . \Users\GroupAgent::GetGroupNameById($groupList[$i]) . "</option>";
                        } ?>
                    </select>
                </div>
                <?php } ?>
                <br>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.custom_info_tip")?></div>
                <hr>
                <h4><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.history")?></h4>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.referer")?></div>
                    <div class="form-control alert-info"><?php echo ($USER->getReferer() == 0) ? \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.nobody") : $USER->getReferer()->getNickname();?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.reg_date")?></div>
                    <div class="form-control alert-info"><?php echo \Engine\Engine::DateFormatToRead($USER->getRegDate());?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.last_date")?></div>
                    <div class="form-control alert-info"><?php echo ($USER->getLastDate() != "1970-01-01") ? \Engine\Engine::DateFormatToRead($USER->getLastDate()) : (($USER->getSex() == 3) ?
                        \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.she_not_signed_in") : \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.he_not_signed_in"));?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.reg_ip")?></div>
                    <div class="form-control alert-info"><?php echo $USER->getRegIp();?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.last_ip")?></div>
                    <div class="form-control alert-info"><?php echo ($USER->getLastIp() != "null") ? $USER->getLastIp() : (($USER->getSex() == 3) ? \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.she_not_signed_in") :
                        \Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.he_not_signed_in"));?></div>
                </div>
                <hr>
                <h4><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.social_info")?></h4>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.rate")?></div>
                    <div class="form-control"><?php echo $USER->getReputation()->getReputationPoints(); ?> <?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.point")?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.from")?></div>
                    <input class="form-control" type="text" value="<?php echo $USER->getFrom();?>" name="user-edit-from">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.from_search")?>" onclick="window.open('https://www.google.ru/search?q=<?php echo $USER->getFrom();?>');">
                            <span class="glyphicons glyphicons-nearby-circle"></span>
                        </button>
                    </span>
                </div>
                <?php echo $customAFJoined; ?>
                <div class="input-group">
                    <div class="input-group-addon">VK:</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getVK();?>" name="user-edit-vk">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.go_to")?>" onclick="window.open('http://vk.com/<?php echo $USER->getVK();?>');">
                            <strong>VK</strong>
                        </button>
                    </span>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Skype:</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getSkype();?>" name="user-edit-skype">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" title="<?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.contact")?>" onclick="window.open('skype:<?php echo $USER->getSkype();?>?chat');">
                            <span class="glyphicons glyphicons-chat"></span>
                        </button>
                    </span>
                </div>
                <?php echo $contactAFJoined; ?>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.real_name")?></div>
                    <input class="form-control" type="text" value="<?php echo $USER->getRealName();?>" name="user-edit-realname">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.birthday")?></div>
                    <input class="form-control" type="text" value="<?php echo $USER->getBirth();?>" name="user-edit-birthday">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.sex")?></div>
                    <select class="form-control" name="user-edit-sex">
                        <option value="1"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.not_selected")?></option>
                        <option value="2" <?php if ($USER->getSex() == 2) echo "selected";?>><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.male")?></option>
                        <option value="3" <?php if ($USER->getSex() == 3) echo "selected";?>><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.female")?></option>
                    </select>
                </div>
                <?php echo $infoAFJoined; ?>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.hobbies")?></div>
                    <input class="form-control" type="text" value="<?php echo $USER->getHobbies();?>" name="user-edit-hobbies">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.about")?></div>
                    <textarea class="form-control" style="resize: vertical; max-height: 500px; min-height: 90px;" name="user-edit-about"><?php echo $USER->getAbout();?></textarea>
                </div>
                <br>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.signature_tip")?></div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.signature")?></div>
                    <textarea class="form-control" style="resize: vertical; max-height: 500px; min-height: 90px;" name="user-edit-signature"><?php echo $USER->getSignature();?></textarea>
                </div>
                <hr>
                <h4><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.avatar")?></h4>
                <img src="<?php echo $USER->getAvatar();?>" style="float: left; padding-right: 5px;">
                <p><?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.avatar_tip")?></p>
                <p class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.avatar_size_tip")?></p>
                <input type="hidden" name="MAX_FILE_SIZE" value="6291456" />
                <input class="form-control" type="file" id="user-edit-avatar" name="user-edit-avatar">
                <hr>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" name="user-edit-save"><span class="glyphicon glyphicon-ok"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.save_changes")?></button>
                    <button class="btn btn-default" type="button" name="user-edit-profile-see" onclick="window.location = 'profile.php?uid=<?php echo $USER->getId(); ?>';"><span class="glyphicon glyphicon-eye-open"></span> <?=\Engine\LanguageManager::GetTranslation("users_panel.user_edit_panel.to_profile")?></button>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="hidden">
            <input type="text" name="paramType" id="paramType-input" value="nickname">
        </div>
    </form>
</div>
<script type="text/javascript">
    var settingDivs = [];
    settingDivs[0] = document.getElementById("user-finder");
    settingDivs[1] = document.getElementById("user-banned");
    settingDivs[2] = document.getElementById("user-signup");
    settingDivs[3] = document.getElementById("user-editor");
    settingDivs[4] = document.getElementById("user-bannedip");

    function activateButton(buttonId){
        $("#"+buttonId).parent("div")
            .children("button")
            .each(function (){
            if ( $(this).hasClass("active") )
                $(this).removeClass("active");
        });
        $("#" + buttonId).addClass("active");
    }

    function showToBelow(parentDivId, butId) {
        parentDivId = document.getElementById(parentDivId);
        settingDivs.forEach(function (item) {
            if (item != parentDivId)
                $(item).hide();
        });
        $(parentDivId).show();

        document.getElementById("btn-show-panel").childNodes.forEach(function (item) {
            $(item).removeClass("active");
        });

        $("#"+butId).addClass("active");
    }

    <?php
     #Поверка на возможность просматривать чужие профили. Если да, то можно и искать пользователей.
     if ($canSeeProfiles){ ?>
        $('#users-select-all').change(function() {
            var checkboxes = $(this).closest('table').find(':checkbox');
            if($(this).is(':checked')) {
                checkboxes.prop('checked', true);
            } else {
                checkboxes.prop('checked', false);
            }
        });

        $("input:checkbox").change(function(){
            var counter = 0;
            if ($("#users-select-all").is(":checked")) counter = counter-1;
            $("input:checkbox:checked").each(function(){
                counter = counter+1;
            });
            if (counter > 0){
                $("#users-delete-button").prop("disabled", false);
                $("#users-selected-counter").show();
                $("#users-selected-counter-span").html(counter);
            }
            else {
                $("#users-delete-button").prop("disabled", true);
                $("#users-selected-counter").hide();
            }
        });

        $("#users-delete-button").on("click", function() {
            var formActionLink = "adminpanel/scripts/userer.php?duids=";
            var comma = "";
            $("input:checkbox:checked").each(function () {
                if ($( this ).attr("data-uid-selected") != undefined){
                    if (formActionLink.charAt(formActionLink.length-1) != "=") comma = ",";
                    formActionLink = formActionLink + comma + $( this ).attr("data-uid-selected");
                }
            });
            $( "#users-delete-button" ).attr("formaction", formActionLink);
            $( "#users-delete-button").attr("type", "submit");
            $( "#users-delete-button").click();
        });

        function findByNickname(){
            $("#user-data-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("users_panel.js.nickname")?>");
            $("#paramType-input").attr("value", "nickname");
            activateButton("data-nickname");
        }

        function findByEmail(){
            $("#user-data-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("users_panel.js.email")?>");
            $("#paramType-input").attr("value", "email");
            activateButton("data-email");
        }

        function findByReferer(){
            $("#user-data-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("users_panel.js.referer_nickname")?>");
            $("#paramType-input").attr("value", "referer");
            activateButton("data-referer");
        }

        function findByIP(){
            $("#user-data-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("users_panel.js.last_ip")?>");
            $("#paramType-input").attr("value", "ip");
            activateButton("data-ip");
        }
    <?php }
    #Поверка на возможность бана\разбана.
     if ($canUserBan || $canUserUnban){ ?>
        $('#users-ban-select-all').change(function() {
            var checkboxes = $(this).closest('table').find(':checkbox');
            if($(this).is(':checked')) {
                checkboxes.prop('checked', true);
            } else {
                checkboxes.prop('checked', false);
            }
        });

        $("input:checkbox").change(function(){
            var counter = 0;
            if ($("#users-ban-select-all").is(":checked")) counter = counter-1;
            $("input:checkbox:checked").each(function(){
                counter = counter+1;
            });
            if (counter > 0){
                $("#user_ban_unban").prop("disabled", false);
                $("#users-ban-selected-counter").show();
                $("#users-ban-selected-counter-span").html(counter);
            }
            else {
                $("#user_ban_unban").prop("disabled", true);
                $("#users-ban-selected-counter").hide();
            }
        });

        $("#user_ban_unban").on("click", function() {
            var formActionLink = "adminpanel/scripts/userer.php?ufuban=";
            var comma = "";
            $("input:checkbox:checked").each(function () {
                if ($( this ).attr("data-bid-selected") != undefined){
                    if (formActionLink.charAt(formActionLink.length-1) != "=") comma = ",";
                    formActionLink = formActionLink + comma + $( this ).attr("data-bid-selected");
                }
            });
            $( "#user_ban_unban" ).attr("formaction", formActionLink);
            $( "#user_ban_unban").attr("type", "submit");
            $( "#user_ban_unban").click();
        });
     <?php }
    #Проверка на возможность бана\разбана IP.
     if ($canIPBan || $canIPUnban){ ?>
        $('#user-bip-select-all').change(function() {
            var checkboxes = $(this).closest('table').find(':checkbox');
            if($(this).is(':checked')) {
                checkboxes.prop('checked', true);
            } else {
                checkboxes.prop('checked', false);
            }
        });

        $("input:checkbox").change(function(){
            var counter = 0;
            if ($("#user-bip-select-all").is(":checked")) counter = counter-1;
            $("input:checkbox:checked").each(function(){
                counter = counter+1;
            });
            if (counter > 0){
                $("#user_bip_unban").prop("disabled", false);
                $("#users-bip-selected-counter").show();
                $("#users-bip-selected-counter-span").html(counter);
            }
            else {
                $("#user_bip_unban").prop("disabled", true);
                $("#users-bip-selected-counter").hide();
            }
        });

        $("#user_bip_unban").on("click", function() {
            var formActionLink = "adminpanel/scripts/userer.php?ipuban=";
            var comma = "";
            $("input:checkbox:checked").each(function () {
                if ($( this ).attr("data-bip-selected") != undefined){
                    if (formActionLink.charAt(formActionLink.length-1) != "=") comma = ",";
                    formActionLink = formActionLink + comma + $( this ).attr("data-bip-selected");
                }
            });
            $( "#user_bip_unban" ).attr("formaction", formActionLink);
            $( "#user_bip_unban").attr("type", "submit");
            $( "#user_bip_unban").click();
        });
     <?php
     }
      ?>
</script>
