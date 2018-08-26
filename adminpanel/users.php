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
if(!empty($_GET["uid"])) {
    if (\Users\UserAgent::IsUserExist($_GET["uid"])) {
        $userExists = true;
        $USER = new \Users\User($_GET["uid"]);
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
    else $userParams = 0;
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
?>
<div class="inner cover">
    <h1 class="cover-heading">Пользователи</h1>
    <p class="lead">Создание, удаление, редактирование и блокировка пользователей сайта.</p>
    <div class="btn-group" id="btn-show-panel">
        <?php
        #Поверка на возможность просматривать чужие профили. Если да, то можно и исктаь пользователей.
        if ($canSeeProfiles){ ?>
        <button type="button" class="btn btn-default<?php echo (!isset($_REQUEST["reqtype"]) && empty($_GET["uid"])) ? " active" : ""; ?>" id="find" onclick="showToBelow('user-finder', 'find')"><span class="glyphicons glyphicons-vcard"></span> Поиск пользователей</button>
        <?php } if ($canSigns) { ?>
        <button type="button" class="btn btn-default<?php echo (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 3) ? " active" : ""; ?>" id="add" onclick="showToBelow('user-signup', 'add')"><span class="glyphicons glyphicons-user-vr-add"></span> Регистрация пользователей</button>
        <?php } if ($canUserBan || $canUserUnban) { ?>
        <button type="button" class="btn btn-default<?php echo (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 1) ? " active" : ""; ?>" id="banning" onclick="showToBelow('user-banned', 'banning')"><span class="glyphicons glyphicons-user-ban"></span> Блокировка пользователей</button>
        <?php } if ($canIPBan || $canIPUnban) {?>
        <button type="button" class="btn btn-default<?php echo (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 2) ? " active" : ""; ?>" id="banningip" onclick="showToBelow('user-bannedip', 'banningip')"><span class="glyphicons glyphicons-ban-circle"></span> Блокировка IP адресов</button>
        <?php } if ($canSeeProfiles && $canChangeProfiles && $userExists) { ?>
        <button type="button" class="btn btn-default alert-info active" id="user-edit" onclick="showToBelow('user-editor', 'user-edit')"><span class="glyphicon glyphicon-user"></span> <?php echo $USER->getNickname(); ?> - Редактирование</button>
        <?php } ?>
    </div>
    <form enctype="multipart/form-data" method="post" action="adminpanel/scripts/userer.php" name="user-form" id="user-form">
        <div class="custom-group">
            <?php
            #Поверка на возможность просматривать чужие профили. Если да, то можно и искать пользователей.
            if ($canSeeProfiles){ ?>
            <div class="div-border" id="user-finder" <?php if ((isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] != 0) || !empty($_GET["uid"])) echo setVisible(false);?>>
                <h2>Поиск пользователей</h2>
                <p class="helper">Осуществление поиска пользователей по их нику, email, рефереру и IP адресу.</p>
                <hr>
                <div class="alert alert-info" <?php if (!isset($_REQUEST["fnn"]) && !isset($_REQUEST["frid"]) && !isset($_REQUEST["fgroup"]) &&
                                                        !isset($_REQUEST["flip"]) && !isset($_REQUEST["femail"])) echo "hidden";?>>
                    <span class="glyphicons glyphicons-search"></span> Применённые фильтры:
                    <hr>
                    <?php if (isset($_REQUEST["fnn"])) echo "<strong>Никнейм:</strong> " . htmlentities($_REQUEST["fnn"]) . "<br>";
                          if (isset($_REQUEST["frid"])) echo "<strong>Реферер:</strong> " . htmlentities($_REQUEST["frid"]) . "<br>";
                          if (isset($_REQUEST["fgroup"])) echo "<strong>Группа:</strong> " . \Users\GroupAgent::GetGroupNameById($_REQUEST["fgroup"]) . "<br>";
                          if (isset($_REQUEST["flip"])) echo "<strong>Последний IP:</strong> " . htmlentities($_REQUEST["flip"]) . "<br>";
                          if (isset($_REQUEST["femail"])) echo "<strong>Email:</strong> " . htmlentities($_REQUEST["femail"]) . "<br>";?>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control" name="user-data-input" id="user-data-input" placeholder="Никнейм">
                    <div class="input-group-btn" id="user-data-typer">
                        <button class="btn btn-default active" type="button" onclick="findByNickname();" id="data-nickname" title="Никнейм"><span class="glyphicon glyphicon-tags"></span></button>
                        <button class="btn btn-default" type="button" onclick="findByEmail();" id="data-email" title="Адрес электронной почты"><span class="glyphicon glyphicon-envelope"></span></button>
                        <button class="btn btn-default" type="button" onclick="findByReferer();" id="data-referer" title="Никнейм реферера"><span class="glyphicons glyphicons-old-man"></span></button>
                        <button class="btn btn-default" type="button" onclick="findByIP();" id="data-ip" title="IP адрес"><span class="glyphicons glyphicons-globe-af"></span></button>
                    </div>
                </div><br>
                <p>Введите в поле для ввода соответствующие данные. Переключение данных осуществляется с помощью кнопок, расположенных в правом краю поля для ввода.
                Вы можете заменять неизвестные места знаком звёздочки (*), тогда система выдаст Вам все результаты, подходящие под оставшуюся часть.
                Например, при поиске по адресу электронной почты, на запрос "*@gmail.com" будут выданы <strong>все</strong> пользователи, что имеют электронный ящик в Gmail.com.</p>
                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> При вводе реферера, нельзя использовать знак звёздочки.</div>
                <div class="input-group">
                    <div class="input-group-addon">Искать в группе:</div>
                    <select class="form-control" name="fgroup" id="user-group-selector">
                        <option value="0">Не выбрано...</option>
                        <?php for ($i = 0; $i <= $groupCount; $i++){
                                print("<option value='" . $groupList[$i] . "'");
                                if (isset($_REQUEST["fgroup"])) if ($groupList[$i] == $_REQUEST["fgroup"]) print(" selected");
                                print(">" . htmlentities($groupNames[$i]) . "</option>");
                              } ?>
                    </select>
                </div>
                <br>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" name="users-find-button">Найти пользователей</button>
                    <?php if ($user->UserGroup()->getPermission("user_remove")) {?><button class="btn btn-default alert-danger" type="button" name="users-delete-button" id="users-delete-button" disabled>Удалить пользователей</button><?php } ?>
                    <a class="btn btn-default" href="adminpanel.php?p=users" name="users-reset-button">Отчистить фильтры</a>
                </div>
                <hr>
                <h2>Результаты поиска</h2>
                <p id="users-selected-counter" class="alert alert-info" hidden><strong>Выделенно пользователей:</strong> <span id="users-selected-counter-span"></span>.</p>
                <table id="users-find-results" class="table">
                    <thead style="background: radial-gradient(at right center, #874c15, #cc8c53); color: white; text-shadow: 2px 2px 3px black;">
                        <tr>
                            <td><input type="checkbox" id="users-select-all" name="allselectorcheck" title="Выбрать всех"></td>
                            <td>ID</td>
                            <td>Никнейм</td>
                            <td>Группа</td>
                            <td>Email</td>
                            <td>Последний IP</td>
                            <td>IP при регистрации</td>
                            <td>Последний вход</td>
                            <td>Рейтинг</td>
                            <td>Забанен</td>
                            <?php if ($user->UserGroup()->getPermission("change_another_profiles")) echo "<td></td>"; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        # Создание таблицы при отсутствии пользователей.
                        if ($userCount == 0){?>
                            <tr>
                                <td colspan="11" class="center alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Не найдено ни одного пользователя, удовлетворяющего всем параметрам поиска.</td>
                            </tr>
                        <?php }
                        # Создание таблицы при наличии в списке хотя бы одного пользователя.
                        for ($i = 0; $i < $userCount; $i++){
                            $selUser = new \Users\User($userList[$i]);
                            if ($selUser->isBanned() === true) $selUserBan = \Engine\Engine::DateFormatToRead(date("Y-m-d", \Guards\SocietyGuard::GetBanUserParam($userList[$i], "banned_time")));
                            else $selUserBan = "Не забанен"; ?>
                            <tr>
                                <td><input type="checkbox" data-uid-selected="<?php print(htmlentities($userList[$i])); ?>" title="Выбрать"></td>
                                <td><?php print(htmlentities($userList[$i]));?></td>
                                <td><?php print(htmlentities($selUser->getNickname()));?></td>
                                <td><?php print("<span style=\"color: " . htmlentities($selUser->UserGroup()->getColor()) . ";\">" . htmlentities($selUser->UserGroup()->getName()) . "</span>");?></td>
                                <td><?php print(htmlentities($selUser->getEmail())); ?></td>
                                <td><?php print(htmlentities($selUser->getLastIp())); ?></td>
                                <td><?php print(htmlentities($selUser->getRegIp())); ?></td>
                                <td><?php print ($selUser->getLastDate() == "1970-01-01") ? ("Не заходил") : (htmlentities(\Engine\Engine::DateFormatToRead(($selUser->getLastDate())))); ?></td>
                                <td><?php print(htmlentities($selUser->getReputation()->getReputationPoints()) . " балл."); ?></td>
                                <td><?php print(htmlentities($selUserBan)); ?></td>
                                <?php if ($canSeeProfiles){ ?>
                                <td class="alert-info"><button class="btn btn-default alert-info" style="width: 100%;" type="submit" formaction="adminpanel/scripts/userer.php?uide=<?php print(htmlentities($userList[$i])); ?>">Редактировать</button></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="btn-group center">
                    <?php
                    if ($usersCount > 50) { for ($i = 1; $i <= ceil($usersCount / 50); $i++) { ?>
                    <button class="btn btn-default <?php echo (isset($_REQUEST["fpage"]) && $_REQUEST["fpage"] == $i) ? "selected" : ""; ?>"
                        type="submit" formaction="adminpanel/scripts/userer.php?fpage=<?php echo $i . $formLink;?>"><?php echo $i; ?></button>
                    <?php } }?>
                </div>
            </div>
            <?php }
            if ($canUserBan || $canUserUnban) { ?>
                <div class="div-border" id="user-banned" <?php if (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 1) echo setVisible(true); else echo setVisible(false);?>>
                    <h2>Блокировка пользователей</h2>
                    <p class="helper">Управление доступом пользователей к сайту и его материалам.</p>
                    <hr>
                    <?php
                    if ($banSearchActive){ ?>
                        <div class="alert alert-info">
                            <span class="glyphicon glyphicon-search"></span> Применённые фильтры:
                            <hr>
                            <?php if (!empty($_REQUEST["fbnn"])) { ?> <strong>Никнейм:</strong> <?php echo htmlentities($_REQUEST["fbnn"]); } ?>
                            <?php if (!empty($_REQUEST["fbr"])) { ?> <strong>Причина:</strong> <?php echo htmlentities($_REQUEST["fbr"]); } ?>
                        </div>
                    <?php } ?>
                    <p>Система позволяет блокировать аккаунты с определённым никнеймом, после чего их доступ к сайту будет заблокирован.
                    Блокировка может быть как ограничена по времени, так и может быть перманентна. Здесь же вы можете разблокировать пользователей.</p>
                    <input type="text" class="form-control" placeholder="Никнейм" id="user_ban_input" name="user_ban_input">
                    <input type="text" class="form-control" placeholder="Причина" id="user_ban_reason" name="user_ban_reason">
                    <div class="input-group">
                        <input type="number" class="form-control" placeholder="Время (в секундах)" id="user_ban_time" name="user_ban_time">
                        <div class="input-group-btn">
                            <button class="btn btn-default" type="button" onclick="secToDate();" id="data-ban-time" title="Перевести в дату"><span class="glyphicon glyphicon-time"></span></button>
                        </div>
                    </div>
                    <br>

                    <p>Введите нужный никнейм в соответствующее поле для ввода. Вы можете искать пользователей, опять же вписав в нужные поля
                    нужные данные и нажав кнопку "Поиск". Исключение: нельзя искать по времени блокировки. Здесь при поиске действует
                    то же правило, что и поиске пользователей: значок звёздочки (*) выполняет функцию любого символа, строки или пробела.</p>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Заблокировать пользователей при использовании звёздочки тоже можно.</div>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Для <em>перманентной</em> блокировки укажите в строке "Время" ноль.</div>
                    <div class="btn-group">
                        <?php if ($canUserBan) { ?> <button class="btn btn-default" type="submit" name="user_ban_ban" id="user_ban_ban">Заблокировать</button> <?php } ?>
                        <?php if ($canUserUnban) { ?> <button class="btn btn-default alert-warning" type="button" name="user_ban_unban" id="user_ban_unban" disabled>Разблокировать</button> <?php } ?>
                        <button class="btn btn-default" type="submit" name="user_ban_find" id="user_ban_find">Поиск</button>
                    </div>
                    <hr>
                    <h2>Результаты поиска</h2>
                    <div class="alert alert-info" id="users-ban-selected-counter" hidden>
                        <strong>Выделенно строк:</strong> <span id="users-ban-selected-counter-span"></span>
                    </div>
                    <table id="users-banfind-results" class="table">
                        <thead style="background: radial-gradient(at right center, #66070a, #eb2b2c); color: white; text-shadow: 2px 4px 3px black;">
                            <tr>
                                <td><input type="checkbox" id="users-ban-select-all" name="users-ban-select-all" title="Выделить всех"></td>
                                <td>Никнейм</td>
                                <td>Забанен</td>
                                <td>До</td>
                                <td>Забанил</td>
                                <td>Причина</td>
                                <?php if ($canUserUnban) { ?><td></td><?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bannedCount == 0 && !$banSearchActive) print("<tr><td colspan=\"7\" class=\"center alert alert-info\">Нет ни одного заблокированного пользователя.</td></tr>");
                            elseif ($bannedCount == 0 && $banSearchActive) print("<tr><td colspan=\"7\" class=\"center alert alert-info\">Не найденно ни одного заблокированного пользователя по данным критериям.</td></tr>");
                            else {
                                for ($i = 0; $i < $bannedCount; $i++) { ?>
                                    <tr>
                                        <td><input type="checkbox" data-bid-selected="<?php echo $bannedList[$i]; ?>"></td>
                                        <td><?php echo \Users\UserAgent::GetUserNick($bannedList[$i]); ?></td>
                                        <td><?php echo Engine\Engine::DateFormatToRead(date("Y-m-d", \Guards\SocietyGuard::GetBanUserParam($bannedList[$i], "banned_time"))); ?></td>
                                        <td><?php echo (\Guards\SocietyGuard::GetBanUserParam($bannedList[$i], "unban_time") == 0) ? "Перманентно" :
                                                Engine\Engine::DateFormatToRead(date("Y-m-d", \Guards\SocietyGuard::GetBanUserParam($bannedList[$i], "unban_time"))); ?></td>
                                        <td><?php echo \Users\UserAgent::GetUserNick(\Guards\SocietyGuard::GetBanUserParam($bannedList[$i], "author")); ?></td>
                                        <td><?php echo htmlentities(\Guards\SocietyGuard::GetBanUserParam($bannedList[$i], "reason")); ?></td>
                                        <?php if ($canUserUnban) { ?><td>
                                            <button class="btn btn-default" type="submit" style="width: 100%" formaction="adminpanel/scripts/userer.php?ufuban=<?php echo $bannedList[$i]; ?>">Разбанить</button>
                                        </td>
                                        <?php } ?>
                                    </tr>
                                <?php }
                            }?>
                        </tbody>
                    </table>
                    <div class="btn-group center">
                        <?php
                        if ($bannedCount > 50) { for ($i = 1; $i <= ceil($bannedCount / 50); $i++) { ?>
                            <button class="btn btn-default <?php echo (isset($_REQUEST["bpage"]) && $_REQUEST["bpage"] == $i) ? "selected" : ""; ?>"
                                    type="submit" formaction="adminpanel/scripts/userer.php?bpage=<?php echo $i . $formLink;?>"><?php echo $i; ?></button>
                        <?php } }?>
                    </div>
                </div>
            <?php }
            if ($canIPBan || $canIPUnban){ ?>
            <div class="div-border" id="user-bannedip" <?php if (isset($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 2) echo setVisible(true); else echo setVisible(false);?>>
                <h2>Блокировка IP адрессов</h2>
                    <p class="helper">Запрет доступа всем пользователям с определёнными IP адресами.</p>
                    <hr>
                    <p>Система позволяет блокировать IP адреса. После этого, пользователи с заблокированными адресами не смогут пользоваться
                    сайтом и материалами портала.</p>
                    <input class="form-control" type="text" id="user-banip-input" name="user-banip-input" placeholder="IP-адрес">
                    <input class="form-control" type="text" id="user-banip-reason" name="user-banip-reason" placeholder="Причина">
                    <input class="form-control" type="number" id="user-banip-time" name="user-banip-time" placeholder="Время">
                <hr>
                <p>Вы также можете заменять неизвестные участки знаком звёздочки (*). В таком случае, все подходящие
                IP-адреса будут заблокированы.</p>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span>  Для <em>перманентной</em> блокировки укажите в строке "Время" ноль.</div>
                <div class="btn-group">
                    <button class="btn btn-default" id="user_bip_ban" name="user_bip_ban">Заблокировать</button>
                    <button class="btn btn-default alert-warning" id="user_bip_unban" name="user_bip_unban" disabled>Разблокировать</button>
                    <button class="btn btn-default" id="user_bip_search" name="user_bip_search">Поиск</button>
                </div>
                <h2>Результаты поиска</h2>
                <div class="alert alert-info" id="users-bip-selected-counter" hidden><strong>Выделенно IP-адресов: </strong><span id="users-bip-selected-counter-span"></span></div>
                <table class="table">
                    <thead style="background: radial-gradient(at right, #003eff, #10e4ff); color: white; text-shadow: 2px 2px 4px black;">
                        <tr>
                            <td><input type="checkbox" id="user-bip-select-all" title="Выделить всех"></td>
                            <td>IP-адрес</td>
                            <td>Забанен</td>
                            <td>Время разбана</td>
                            <td>Забанил</td>
                            <td>Причина</td>
                            <?php if ($canIPUnban) { ?><td></td><?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($banipCount == 0){ ?><tr><td class="alert-info" colspan="7" style="text-align: center;"><span class="glyphicon glyphicon-info-sign"></span> Нет ни одного заблокированного IP-адреса.</td></tr> <?php }
                        else for ($h = 0; $h <= $banipCount-1; $h++) { ?>
                            <tr>
                                <td><input type="checkbox" data-bip-selected="<?php echo htmlentities($banipList[$h]); ?>"></td>
                                <td><?php print htmlentities($banipList[$h]); ?></td>
                                <td><?php print \Engine\Engine::DateFormatToRead(date("Y-m-d", \Guards\SocietyGuard::GetIPBanParam($banipList[$h], "banned_time"))); ?></td>
                                <td><?php print (\Guards\SocietyGuard::GetIPBanParam($banipList[$h], "unban_time") == 0) ? "Перманентно" : \Engine\Engine::DateFormatToRead(date("Y-m-d",\Guards\SocietyGuard::GetIPBanParam($banipList[$h], "unban_time"))); ?></td>
                                <td><?php print \Users\UserAgent::GetUserNick(\Guards\SocietyGuard::GetIPBanParam($banipList[$h], "author")); ?></td>
                                <td><?php print htmlentities(\Guards\SocietyGuard::GetIPBanParam($banipList[$h], "reason")); ?></td>
                                <?php if ($canIPUnban) { ?> <td><button style="width: 100%;" class="btn btn-default" type="submit" formaction="adminpanel/scripts/userer.php?ipuban=<?php print htmlentities($banipList[$h]); ?>">Разблокировать</button></td> <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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
                <h2>Регистрация пользователей</h2>
                <p class="helper">Добавление новых аккаунтов в базу данных.</p>
                <hr>
                <p>Через данную форму, Вы можете зарегистрировать пользователя без последующей необходимости в активации учётной записи.
                Однако, здесь действуют те же правила, что и на обычной странице регистрации. Здесь вы также можете сразу определить, какие
                права будет иметь новозарегистрированный пользователь.</p>
                <input class="form-control" type="text" placeholder="Никнейм" id="user-add-nickname" name="user-add-nickname" autocomplete="new-password" maxlength="16">
                <input class="form-control" type="password" placeholder="Пароль" id="user-add-password" name="user-add-password" autocomplete="new-password" maxlength="16">
                <input class="form-control" type="email" placeholder="Email" id="user-add-email" name="user-add-email" autocomplete="off">
                <select class="form-control" name="user-add-group" id="user-add-group">
                    <option value="0">Выберите группу...</option>
                    <?php for($i = 0; $i < count($groupList = \Users\GroupAgent::GetGroupList()); $i++){
                        echo "<option value='".$groupList[$i]."'";
                        if (isset($_REQUEST["group"])) if ($_REQUEST["group"] != 0 && $_REQUEST["group"] == $groupList[$i]) echo "selected";
                        echo ">" . \Users\GroupAgent::GetGroupNameById($groupList[$i]) . "</option>";
                    } ?>
                </select><br>
                Все поля <ins>обязательны</ins>.
                <hr>
                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> Никнейм не может быть короче 4 символов и длиннее 16. Те же требования и к паролю. </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span> Если Вы не укажете группу,
                    в которую нужно зачислить нового пользователя, он будет зачислен в стандартную при регистрации. </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Если Вы в настройках требуете активации аккаунтов, стандартное сообщение об активации всё равно будет отправлено. </div>
                <hr>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" id="user-add-add" name="user-add-add"><span class="glyphicon glyphicon-ok"></span> Зарегистрировать</button>
                    <button class="btn btn-default" type="reset" id="user-add-reset" name="user-add-reset"><span class="glyphicon glyphicon-erase"></span> Отчистить форму</button>
                </div>
            </div>
            <?php }
            if ($canSeeProfiles && $canChangeProfiles && $userExists) { ?>
            <div class="div-border" id="user-editor" <?php if ($userExists) echo setVisible(true); else echo setVisible(false); ?>>
                <h2><?php print $USER->getNickname(); ?></h2>
                <p class="helper">Редактирование данных пользователя.</p>
                <hr>
                <p>Здесь Вы можете получить полный доступ к информации о пользователе, а так же, можете её редактировать.
                Однако, здесь же Вы можете получить и системную информацию о пользователе, её изменять с помощью панели управления нельзя и мы
                <em>настоятельно рекомендуем НЕ делать</em> этого способами, которыми это возможно.</p>
                <div class="input-group">
                    <div class="input-group-addon">ID:</div>
                    <input type="text" readonly class="form-control alert-info" value="<?php echo $USER->getId();?>" name="user-edit-id">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Статус:</div>
                    <?php $status = ($USER->getLastTime()+900 < time()) ? 0 : 1;?>
                    <div class="form-control <?php echo ($status === 1) ? "alert-success" : "alert-danger"; ?>"><?php echo ($status === 1) ? "Онлайн" : "Оффлайн; был(-а) в сети в " . Engine\Engine::DatetimeFormatToRead(date("Y-m-d H:m", $USER->getLastTime())); ?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Активация:</div>
                    <div class="form-control alert-info"><?php echo ($USER->getActiveStatus() === true) ? "Активен" : $USER->Activate();?></div>
                    <?php if ($USER->getActiveStatus() != true) { ?>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit" title="Автивировать" name="user-edit-activate">
                            <span class="glyphicons glyphicons-ok"></span>
                        </button>
                    </span>
                    <?php } ?>
                </div>
                <hr>
                <h4>Основная информация:</h4>
                <div class="input-group">
                    <div class="input-group-addon">Никнейм:</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getNickname();?>" maxlength="16" name="user-edit-nickname">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Сменить пароль:</div>
                    <input class="form-control" type="password" placeholder="Если здесь пусто, то пароль не изменится." maxlength="16" name="user-edit-password">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Email:</div>
                    <input class="form-control" value="<?php echo $USER->getEmail();?>" type="email" name="user-edit-email">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" title="Написать письмо" onclick="window.open('mailto:<?php echo $USER->getEmail();?>');">
                            <span class="glyphicons glyphicons-message-full"></span>
                        </button>
                    </span>
                </div>
                <?php if ($user->UserGroup()->getPermission("change_user_group")){ ?>
                <div class="input-group">
                    <div class="input-group-addon">Группа:</div>
                    <select class="form-control" name="user-edit-group">
                        <option value="0">Выберите группу...</option>
                        <?php for($i = 0; $i < count($groupList = \Users\GroupAgent::GetGroupList()); $i++){
                            echo "<option value=\"".$groupList[$i]."\"";
                            if ($USER->getGroupId() == $groupList[$i]) echo "selected";
                            echo ">" . \Users\GroupAgent::GetGroupNameById($groupList[$i]) . "</option>";
                        } ?>
                    </select>
                </div>
                <?php } ?>
                <br>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Здесь действуют те же правила, что и при регистрации: пароль и никнейм не длиннее
                16 символов, и не короче 4; использовать можно только буквы латиницы, цифры и точки.</div>
                <hr>
                <h4>История:</h4>
                <div class="input-group">
                    <div class="input-group-addon">Реферер:</div>
                    <div class="form-control alert-info"><?php echo ($USER->getReferer() == 0) ? "Никто" : $USER->getReferer()->getNickname();?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Дата регистрации:</div>
                    <div class="form-control alert-info"><?php echo \Engine\Engine::DateFormatToRead($USER->getRegDate());?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Последний вход:</div>
                    <div class="form-control alert-info"><?php echo ($USER->getLastDate() != "1970-01-01") ? \Engine\Engine::DateFormatToRead($USER->getLastDate()) : "не заходил";?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">IP при регистрации:</div>
                    <div class="form-control alert-info"><?php echo $USER->getRegIp();?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Последний IP:</div>
                    <div class="form-control alert-info"><?php echo $USER->getLastIp();?></div>
                </div>
                <hr>
                <h4>Социальная информация:</h4>
                <div class="input-group">
                    <div class="input-group-addon">Рейтинг:</div>
                    <div class="form-control"><?php echo $USER->getReputation()->getReputationPoints(); ?> балл(ов).</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Откуда:</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getFrom();?>" name="user-edit-from">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" title="Найти" onclick="window.open('https://www.google.ru/search?q=<?php echo $USER->getFrom();?>');">
                            <span class="glyphicons glyphicons-nearby-circle"></span>
                        </button>
                    </span>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">VK:</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getVK();?>" name="user-edit-vk">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" title="Перейти" onclick="window.open('http://vk.com/<?php echo $USER->getVK();?>');">
                            <strong>VK</strong>
                        </button>
                    </span>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Skype:</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getSkype();?>" name="user-edit-skype">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" title="Связаться" onclick="window.open('skype:<?php echo $USER->getSkype();?>?chat');">
                            <span class="glyphicons glyphicons-chat"></span>
                        </button>
                    </span>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Настоящее имя:</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getRealName();?>" name="user-edit-realname">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">День рождения:</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getBirth();?>" name="user-edit-birthday">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Пол:</div>
                    <select class="form-control" name="user-edit-sex">
                        <option value="0">Не выбрано...</option>
                        <option value="1" <?php if ($USER->getSex() == 1) echo "selected";?>>Парень</option>
                        <option value="2" <?php if ($USER->getSex() == 2) echo "selected";?>>Девушка</option>
                    </select>
                </div>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon">Хобби</div>
                    <input class="form-control" type="text" value="<?php echo $USER->getHobbies();?>" name="user-edit-hobbies">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">О себе</div>
                    <textarea class="form-control" style="resize: vertical; max-height: 500px; min-height: 90px;" name="user-edit-about"><?php echo $USER->getAbout();?></textarea>
                </div>
                <br>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> В подписи Вы можете использовать BB-code.</div>
                <div class="input-group">
                    <div class="input-group-addon">Подпись</div>
                    <textarea class="form-control" style="resize: vertical; max-height: 500px; min-height: 90px;" name="user-edit-signature"><?php echo $USER->getSignature();?></textarea>
                </div>
                <hr>
                <h4>Аватарка</h4>
                <img src="<?php echo $USER->getAvatar();?>" style="float: left; padding-right: 5px;">
                <p>Вы можете загрузить другую аватарку.</p>
                <p class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> Аватарка должна быть по размерам <?php echo \Engine\Engine::GetEngineInfo("aw") . "x" . \Engine\Engine::GetEngineInfo("ah");?> и не больше 6 МБ</p>
                <input type="hidden" name="MAX_FILE_SIZE" value="6291456" />
                <input class="form-control" type="file" accept="image/jpeg,image/jpg,image/png,image/gif,image/tif" name="user-edit-avatar">
                <hr>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" name="user-edit-save"><span class="glyphicon glyphicon-ok"></span> Сохранить изменения</button>
                    <button class="btn btn-default" type="button" name="user-edit-profile-see" onclick="window.location = 'profile.php?uid=<?php echo $USER->getId(); ?>';"><span class="glyphicon glyphicon-eye-open"></span> Просмотреть на сайте</button>
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
            $("#user-data-input").attr("placeholder", "Никнейм");
            $("#paramType-input").attr("value", "nickname");
            activateButton("data-nickname");
        }

        function findByEmail(){
            $("#user-data-input").attr("placeholder", "Адрес электронной почты");
            $("#paramType-input").attr("value", "email");
            activateButton("data-email");
        }

        function findByReferer(){
            $("#user-data-input").attr("placeholder", "Никнейм реферера");
            $("#paramType-input").attr("value", "referer");
            activateButton("data-referer");
        }

        function findByIP(){
            $("#user-data-input").attr("placeholder", "Последний IP адрес");
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
