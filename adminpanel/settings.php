<?php

/***************************************************************************************
 * Так как настройки сохраняются не сразу, то есть не после загрузки ЭТОЙ страницы, а раньше
 * то сохранение происходит прямо перед загрузкой предыдущей конфигурации.
 * Это происходит в самой админпанели, adminpanel.php, стр. 13. Исходник функции Replace()
 * находится в /engine/replacer.php.
 ***************************************************************************************/

if (!defined("TT_AP")){ header("Location: ../adminpanel.php?p=forbidden"); exit; }
//Проверка на наличие доступа к изменению конфигурации движка.
if (!$user->UserGroup()->getPermission("change_engine_settings")) header("Location: ../../adminpanel.php?res=1");
else {
   $langs = \Engine\Engine::GetLanguagePacks();?>
<div class="inner cover">
    <h1 class="cover-heading">Настройки</h1>
    <p class="lead">Настройки работы сайта.</p>
    <div id="btn-show-panel" class="btn-group">
        <button id="btn-show-custom" class="btn btn-default active" onclick="showToBelow('custom_sets', 'btn-show-custom')"><span class="glyphicon glyphicon-cog"></span> Конфигурация</button>
        <button id="btn-show-email" class="btn btn-default" onclick="showToBelow('email_sets', 'btn-show-email')"><span class="glyphicon glyphicon-envelope"></span> Бот-рассылка</button>
        <button id="btn-show-reg" class="btn btn-default" onclick="showToBelow('reg_sets', 'btn-show-reg')"><span class="glyphicon glyphicon-pencil"></span> Регистрация</button>
        <button id="btn-show-users" class="btn btn-default" onclick="showToBelow('users_sets', 'btn-show-users')"><span class="glyphicon glyphicon-user"></span> Пользователи</button>
        <button id="btn-show-todo" class="btn btn-default" onclick="showToBelow('todoeditor', 'btn-show-todo')"><span class="glyphicon glyphicon-th-list"></span> To-do лист</button>
    </div>
    <form name="settings" method="post" action="adminpanel/scripts/replacer.php">
        <div class="custom-group">
            <div class="div-border" id="custom_sets">
                <h3><span class="glyphicon glyphicon-cog"></span> Конфигурация</h3>
                <p class="helper">Здесь находятся основные настройки сайта: описание, язык, региональные настройки.</p>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon">Название сайта</div>
                    <input type="text" class="form-control"  name="sitename" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("sn"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Это название будет отображаться в шапке сайта, а так же в имени вкладок.</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Слоган</div>
                    <input type="text" class="form-control" name="sitetagline" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("stl"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Слоган будет отображаться под названием сайта на главной странице.</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Домен</div>
                    <input type="text" class="form-control"  name="domain" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("dm"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Этот домен будет добавляться в письма при рассылке.</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Описание сайта</div>
                    <input type="text" class="form-control"  name="sitesubscribe" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ssc"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Этот текст будет выводится при поиске этого сайта в поисковых системах.</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Хештеги</div>
                    <input type="text" class="form-control"  name="sitehashtags" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("sh"));?>">
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Перечислите через запятую теги, по которым сайт можно будет найти в Интернете.</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Статус сайта</div>
                    <select class="form-control" name="sitestatus">
                        <option value="1" <?php if (\Engine\Engine::GetEngineInfo("ss") == 1) echo "selected"; ?>>Включен</option>
                        <option value="0" <?php if (\Engine\Engine::GetEngineInfo("ss") == 0) echo "selected"; ?>>Выключен</option>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Если сайт выключен, то доступ к нему будут иметь только группы пользователей,
                    что имеют соответствующие права.</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Используемый шаблон</div>
                    <select class="form-control" name="sitetemplate">
                        <?php foreach(\Engine\Engine::GetTemplatesPacks() as $f){
                            if ($f == \Engine\Engine::GetEngineInfo("stp"))
                                echo "<option value=\"$f\" selected>$f</option>";
                            else
                                echo "<option value=\"$f\">$f</option>";}
                        ?>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Шаблон - это правила для Вашего сайта, а именно правила
                        дизайна профиля и главной страницы. Все шаблоны находятся в папке "/site/templates/" корня ресурса.</div>
                </div>
                <hr>
                <p>Ниже располагаются настройки локали: язык и региональное время.</p>
                <div class="input-group">
                    <div class="input-group-addon">Язык</div>
                    <select class="form-control" name="sitelang">
                        <?php if (\Engine\Engine::GetEngineInfo("sl") === 0){ ?><option value="0" selected>&lt;пусто&gt;</option><?php }
                        /*Перебрать названия языков...*/  for ($i = 0; $i <= count($langs)-1; $i++){ ?>
                            <option value="<?php echo $langs[$i];?>" <?php if (\Engine\Engine::GetEngineInfo("sl") == $langs[$i]) echo " selected";?>><?php echo $langs[$i];?></option>
                        <?php }  ?>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Все переводы находятся в папке "languages" корня сайта.</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Часовой пояс</div>
                    <select class="form-control" name="siteregiontime">
                        <option value="-12" <?php if (\Engine\Engine::GetEngineInfo("srt") == -12) echo "selected";?>>UTC -12:00</option>
                        <option value="-11" <?php if (\Engine\Engine::GetEngineInfo("srt") == -11) echo "selected";?>>UTC -11:00</option>
                        <option value="-10" <?php if (\Engine\Engine::GetEngineInfo("srt") == -10) echo "selected";?>>UTC -10:00 Алеутские острова, Гаваи...</option>
                        <option value="-9.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == -9.5) echo "selected";?>>UTC -9:30 Маркизские острова...</option>
                        <option value="-9" <?php if (\Engine\Engine::GetEngineInfo("srt") == -9) echo "selected";?>>UTC -9:00 Аляска...</option>
                        <option value="-8" <?php if (\Engine\Engine::GetEngineInfo("srt") == -8) echo "selected";?>>UTC -8:00 Нижняя Калифорния, Тихоокеанское время (США и Канада)...</option>
                        <option value="-7" <?php if (\Engine\Engine::GetEngineInfo("srt") == -7) echo "selected";?>>UTC -7:00 Аризона, Горное время (США и Канада), Ла-Пас, Мазатлан, Чихуахуа...</option>
                        <option value="-6" <?php if (\Engine\Engine::GetEngineInfo("srt") == -6) echo "selected";?>>UTC -6:00 Гвадалахара, Мехико, Монтеррей, остров Пасхи, Саскачеван, Центральное время (США и Канада), Центральная Америка...</option>
                        <option value="-5" <?php if (\Engine\Engine::GetEngineInfo("srt") == -5) echo "selected";?>>UTC -5:00 Богота, Кито, Лима, Рио-Бранко, Восточное время (США и Канада), Гавана, Гаити...</option>
                        <option value="-4" <?php if (\Engine\Engine::GetEngineInfo("srt") == -4) echo "selected";?>>UTC -4:00 Асунсьон, Атлантическое время (Канада), Джорджтаун, Ла-Пас, Манаус...</option>
                        <option value="-3.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == -3.5) echo "selected";?>>UTC -3:30 Ньюфаундленд</option>
                        <option value="-3" <?php if (\Engine\Engine::GetEngineInfo("srt") == -3) echo "selected";?>>UTC -3:00 Арагуаяна, Бразилия, Буэнос-Айрес, Гренландия...</option>
                        <option value="-2" <?php if (\Engine\Engine::GetEngineInfo("srt") == -2) echo "selected";?>>UTC -2:00</option>
                        <option value="-1" <?php if (\Engine\Engine::GetEngineInfo("srt") == -1) echo "selected";?>>UTC -1:00 Азорские острова, Кабо-Верде...</option>
                        <option value="0" <?php if (\Engine\Engine::GetEngineInfo("srt") == 0) echo "selected";?>>UTC Лондон, Эдинбург, Лиссабон, Дублин, Рейкьявик...</option>
                        <option value="1" <?php if (\Engine\Engine::GetEngineInfo("srt") == 1) echo "selected";?>>UTC +1:00 Амсетрдам, Берлин, Берн, Вена, Рим, Стокгольм...</option>
                        <option value="2" <?php if (\Engine\Engine::GetEngineInfo("srt") == 2) echo "selected";?>>UTC +2:00 Амман, Афины, Бухарест, Бейрут, Вильнюс, Рига, Киев, Таллин...</option>
                        <option value="3" <?php if (\Engine\Engine::GetEngineInfo("srt") == 3) echo "selected";?>>UTC +3:00 Москва, Минск, Санкт-Петербург, Багдад, Кувейт...</option>
                        <option value="3.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 3.5) echo "selected";?>>UTC +3:30 Тегеран...</option>
                        <option value="4" <?php if (\Engine\Engine::GetEngineInfo("srt") == 4) echo "selected";?>>UTC +4:00 Астрахань, Ульяновск, Баку, Ереван, Ижевск, Самара, Тбилиси...</option>
                        <option value="4.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 4.5) echo "selected";?>>UTC +4:30 Кабул...</option>
                        <option value="5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 5) echo "selected";?>>UTC +5:00 Екатеринбург, Ташкент, Ашхабад, Карачи...</option>
                        <option value="5.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 5.5) echo "selected";?>>UTC +5:30 Колката, Мумбаи, Нью-Дели...</option>
                        <option value="5.75" <?php if (\Engine\Engine::GetEngineInfo("srt") == 5.75) echo "selected";?>>UTC +5:45 Катманду...</option>
                        <option value="6" <?php if (\Engine\Engine::GetEngineInfo("srt") == 6) echo "selected";?>>UTC +6:00 Омск, Астана, Дакка...</option>
                        <option value="6.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 6.5) echo "selected";?>>UTC +6:30 Янгон...</option>
                        <option value="7" <?php if (\Engine\Engine::GetEngineInfo("srt") == 7) echo "selected";?>>UTC +7:00 Красноярск, Новосибирск, Томск, Барнаул, Горно-Алтайск,Банкок, Джакарта, Ханой...</option>
                        <option value="8" <?php if (\Engine\Engine::GetEngineInfo("srt") == 8) echo "selected";?>>UTC +8:00 Иркутск, Гонконг, Пекин, Сингапур...</option>
                        <option value="8.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 8.5) echo "selected";?>>UTC +8:30 Пхеньян...</option>
                        <option value="8.75" <?php if (\Engine\Engine::GetEngineInfo("srt") == 8.75) echo "selected";?>>UTC +8:45 Юкла...</option>
                        <option value="9" <?php if (\Engine\Engine::GetEngineInfo("srt") == 9) echo "selected";?>>UTC +9:00 Чита, Якутск, Осака, Сеул, Токио, Саппоро...</option>
                        <option value="9.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 9.5) echo "selected";?>>UTC +9:30 Аделаида, Дарвин...</option>
                        <option value="10" <?php if (\Engine\Engine::GetEngineInfo("srt") == 10) echo "selected";?>>UTC +10:00 Брисбен, Владивосток, Гуам, Каннабера, Сидней...</option>
                        <option value="10.5" <?php if (\Engine\Engine::GetEngineInfo("srt") == 10.5) echo "selected";?>>UTC +10:30 Лорд-Хау...</option>
                        <option value="11" <?php if (\Engine\Engine::GetEngineInfo("srt") == 11) echo "selected";?>>UTC +11:00 Магадан, остров Бугенвиль, остров Норфолк, Сахалин...</option>
                        <option value="12" <?php if (\Engine\Engine::GetEngineInfo("srt") == 12) echo "selected";?>>UTC +12:00 Петропавловск-Камчатский, Анадырь, Веллингтон, Окленд, Фиджи...</option>
                        <option value="12.75" <?php if (\Engine\Engine::GetEngineInfo("srt") == 12.75) echo "selected";?>>UTC +12:45 Чатем...</option>
                        <option value="13" <?php if (\Engine\Engine::GetEngineInfo("srt") == 13) echo "selected";?>>UTC +13:00 Нукуалофа, Самоа...</option>
                        <option value="14" <?php if (\Engine\Engine::GetEngineInfo("srt") == 14) echo "selected";?>>UTC +14:00 остров Киритимати</option>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Выберите часовой пояс, удобный Вам. Это нужно для корректирования времени сайта.</div>
                </div>
            </div>
            <div class="div-border" id="email_sets" hidden>
                <h3><span class="glyphicon glyphicon-envelope"></span> Бот-рассылка</h3>
                <p class="helper">Параметры почты для рассылки писем при регистрации и прочем.</p>
                <hr>
                <p>Для рассылки писем на электронную почту Вам нужен отдельный аккаунт, который будет связан с сайтом. Настоятельно советуем использовать <a href="http://gmail.com">Gmail</a>,
                так как он наиболее практичен и популярен, по сравнению с другими аналогами (в России это Yandex, Mail.ru, например). Все настройки и параметры для подключения Вы можете найти в интернете.
                Также, не забудьте включить доступ к "недоверенным" приложениям - у Google (то есть, у Gmail) он есть.</p>
                <div class="input-group">
                    <div class="input-group-addon">Email логин</div>
                    <input type="text" class="form-control" name="emaillogin" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("el"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Email пароль</div>
                    <input type="password" class="form-control" name="emailpassword" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ep"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Тип соединения</div>
                    <select class="form-control" name="emailconnecttype">
                        <option value="1" <?php if (\Engine\Engine::GetEngineInfo("ecp") == "tsl") echo "selected"; ?>>TSL</option>
                        <option value="0" <?php if (\Engine\Engine::GetEngineInfo("ecp") == "ssl") echo "selected"; ?>>SSL</option>
                    </select>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Email сервер</div>
                    <input type="text" class="form-control"  name="emailhost" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("eh"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Email порт</div>
                    <input type="text" class="form-control"  name="emailport" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ept"));?>">
                </div>
                <br>
                <div class="alert alert-warning">Ваш почтовый ящик используется ТОЛЬКО для рассылки писем посредством административной панели сайта и соответствующих писем при регистрации. Сайт не
                следит за Вашими сообщениями и так же не учавствует в поддержании чистоты на предоставленном для рассылки аккаунте электронной почты.</div>
            </div>
            <div class="div-border" id="reg_sets"  hidden>
                <h3><span class="glyphicon glyphicon-pencil"></span> Регистрация</h3>
                <p class="helper">Конфигурация регистрации и авторизации на сайте.</p>
                <hr>
                <p>Дубликаты плохи тем, что они засоряют базы данных, некоторые нарочно создаются для жульничества, некоторые создаются ботами и под ботов и прочее. Чтобы избежать всего перечисленного,
                лучше требовать активацию email ящиков, что позволит исключить хотя бы какую-то часть злонамеренных пользователей.</p>
                <div class="input-group">
                    <div class="input-group-addon">Активация</div>
                    <select class="form-control" name="needactivate">
                        <option value="1" <?php if (\Engine\Engine::GetEngineInfo("na") == "1") echo "selected"; ?>>Нужна</option>
                        <option value="0" <?php if (\Engine\Engine::GetEngineInfo("na") == "0") echo "selected"; ?>>Не нужна</option>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Если активация будет включена, то при регистрации нужно будет
                        подтверждать свой Email. Также, это позволяет обеспечить некую защиту от наличия у одного хозяина нескольких аккаунтов.</div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Запрет мультиаккаунта</div>
                    <input type="checkbox" class="form-control" name="multiacc" <?php if (\Engine\Engine::GetEngineInfo("map") == "1") echo "checked"; ?>>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Запретить регистрацию аккаунтов с одиннаковых IP адресов.</div>
                </div>
                <hr>
                <div class="input-group">
                    <?php $r = \Users\GroupAgent::GetGroupList(); ?>
                    <div class="input-group-addon">Группа при регистрации</div>
                    <select class="form-control" name="standartgroup">
                        <?php for($i = 0; $i <= count($r)-1; $i++){
                                echo "<option value='".$r[$i]."'";
                                if (\Engine\Engine::GetEngineInfo("sg") == $r[$i]) echo " selected";
                                echo ">" . \Users\GroupAgent::GetGroupNameById($r[$i]) . "</option>";
                        } ?>
                    </select>
                    <div class="form-control info alert-info" ><span class="glyphicon glyphicon-info-sign"></span> Новички будут зачисляться в эту группу.</div>
                </div>
            </div>
            <div class="div-border" id="users_sets" hidden>
                <h3><span class="glyphicon glyphicon-user"></span> Пользователи</h3>
                <p class="helper">Здесь меняются настройки общие для всех пользователей.</p>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon">Возможные причины жалоб</div>
                    <textarea class="form-control" style="resize: vertical; min-height: 100px;" name="report-reasons"><?php echo \Engine\Engine::GetReportReasons(); ?></textarea>
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Перечислите все возможные причины жалоб пользователей. Каждая
                    новая причина должна быть на новой строке.</div>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon">Максимальная ширина аватарки</div>
                    <input type="number" class="form-control"  name="avatarmaxwidth" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("aw"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Максимальная длина аватарки</div>
                    <input type="number" class="form-control"  name="avatarmaxheight" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ah"));?>">
                </div>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon">Разрешённые к загрузке форматы</div>
                    <input type="text" class="form-control"  name="uploadformats" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("upf"));?>">
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Перечисляйте форматы через запятую <em>без</em> пробелов.</div>
                <div class="input-group">
                    <div class="input-group-addon">Максимальный размер файла</div>
                    <input type="number" class="form-control"  name="maxfilesize" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ups"));?>">
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Размер указывается в байтах.</div>
                <div class="input-group">
                    <div class="input-group-addon">Разрешить гостям просматривать профили</div>
                    <input type="checkbox" class="form-control" name="guest_see_profiles" <?php if (\Engine\Engine::GetEngineInfo("gsp")) echo "checked"; ?>>
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Под гостями подразумеваются незарегистрированные пользователи.</div>
            </div>
            <div class="div-border" id="todoeditor" hidden>
                <h3><span class="glyphicon glyphicon-th-list"></span> To-do</h3>
                <p class="helper">Здесь вы можете оставлять заметки по работе на сайте.</p>
                <hr>
                <textarea name="todo_texter" class="form-control" style="min-width: 100%; max-width: 100%; min-height: 500px;"><?php echo trim(file_get_contents("adminpanel/.todolist")); ?></textarea>
            </div>
        </div>
        <hr />
        <div class="btn-group" role="group">
            <button type="submit" class="btn btn-default" name="save_cfg_button">Сохранить</button>
            <button type="button" class="btn btn-default" name="restart_cfg_button">Вернуть</button>
        </div>
    </form>
</div>
<script>
    var settingDivs = [];
    settingDivs[0] = document.getElementById("custom_sets");
    settingDivs[1] = document.getElementById("email_sets");
    settingDivs[2] = document.getElementById("reg_sets");
    settingDivs[3] = document.getElementById("users_sets");
    settingDivs[4] = document.getElementById("todoeditor");

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
</script>
<?php } ?>