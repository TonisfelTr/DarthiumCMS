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
   $langs = \Engine\Engine::GetLanguagePacks();
   $hasPerms = ($user->UserGroup()->getPermission("look_statistic")) ? true : false;
   $additionalFields = \Users\UserAgent::GetAdditionalFieldsList();
   $additionalFieldsOptions = [];
   $additionalFieldsOptions[] = "<option value=\"0\">Не выбрано</option>";
   foreach ($additionalFields as $field) {
       $additionalFieldsOptions[] = "<option value=\"" . $field["id"] . "\">" . $field["name"] . "</option>";
   }


   ?>
<div class="inner cover">
    <h1 class="cover-heading">Настройки</h1>
    <p class="lead">Настройки работы сайта.</p>
    <div id="btn-show-panel" class="btn-group">
        <button type="button" class="btn btn-default active" data-div-number="1"><span class="glyphicon glyphicon-cog"></span> Конфигурация</button>
        <button type="button" class="btn btn-default" data-div-number="2"><span class="glyphicon glyphicon-envelope"></span> Бот-рассылка</button>
        <button type="button" class="btn btn-default" data-div-number="3"><span class="glyphicon glyphicon-pencil"></span> Регистрация</button>
        <button type="button" class="btn btn-default" data-div-number="4"><span class="glyphicon glyphicon-user"></span> Пользователи</button>
        <?php if ($hasPerms) { ?>
        <button type="button" class="btn btn-default" data-div-number="5"><span class="glyphicons glyphicons-pie-chart"></span> Статистика</button>
        <?php } ?>
        <button type="button" class="btn btn-default" data-div-number="6"><span class="glyphicon glyphicon-th-list"></span> To-do лист</button>
    </div>
    <form name="settings" method="post" action="adminpanel/scripts/replacer.php">
        <div class="custom-group">
            <div class="div-border" id="custom_sets" data-number="1">
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
            <div class="div-border" id="email_sets" data-number="2" hidden>
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
                <div class="alert alert-warning">Чтобы протестировать правильность введённых данных и доступ к аккаунту, Вам нужно сначала сохранить настройки почты и только потом нажать на кнопку.</div>
                <button class="btn btn-default" id="mail-test-ajax-btn" type="button" style="width: 100%;"><span class="glyphicons glyphicons-message-out"></span> Протестировать правильность</button>
            </div>
            <div class="div-border" id="reg_sets"  data-number="3" hidden>
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
            <div class="div-border" id="users_sets" data-number="4" hidden>
                <h3><span class="glyphicon glyphicon-user"></span> Пользователи</h3>
                <p class="helper">Здесь меняются настройки общие для всех пользователей.</p>
                <hr>
                <div class="alert hidden" id="add-fields-info-div"><span></span></div>
                <h4>Дополнительные поля</h4>
                <p>Здесь Вы можете создать свои поля, которые Вы считаете нужными. Можно настроить их отображение в профиле, приватность и логику; например, если Вы хотите
                сделать поле, по клику на которое в профиле будет происходить какое-либо действие.</p>
                <div class="input-group">
                    <div class="input-group-addon">Дополнительные поля</div>
                    <select class="form-control" name="user-additional-fields" id="user-add-fields">
                        <?php foreach($additionalFieldsOptions as $option) echo $option; ?>
                    </select>
                    <div class="input-group-btn">
                        <button class="btn btn-default" type="button" id="field-add-btn" title="Добавить поле"><span class="glyphicons glyphicons-plus"></span></button>
                        <button class="btn btn-default" type="button" id="field-remove-btn" title="Удалить поле" disabled><span class="glyphicons glyphicons-minus"></span></button>
                    </div>
                </div>
                <div id="field-panel-manage" class="div-border" style="display: none; margin-top: 15px;">
                    <div class="input-group">
                        <div class="input-group-addon">Название поля</div>
                        <input class="form-control" name="field-name-input" id="field-name-input" maxlength="16">
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Максимальная длина - 16 букв.</div>
                    </div>
                    <br>
                    <div class="input-group">
                        <div class="input-group-addon">Описание</div>
                        <input class="form-control" type="text" name="field-description" id="field-description">
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Данный текст будет показываться как подсказка при наведении на соответствующее поле.</div>
                    </div>
                    <br>
                    <div class="input-group">
                        <div class="input-group-addon">Тип</div>
                        <select class="form-control" id="field-type-selector">
                            <option value="1">Сведение</option>
                            <option value="2">Контакт</option>
                            <option value="3">Общее</option>
                        </select>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Здесь Вы можете выбрать, где будет отображаться поле.</div>
                    </div>
                    <br>
                    <div class="input-group">
                        <div class="input-group-addon">Обязательное поле</div>
                        <div class="form-control">
                            <input type="checkbox" name="field-requied" id="field-requied">
                        </div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Если данное поле обязательное, его нужно будет заполнить при регистрации.</div>
                    </div>
                    <br>
                    <div class="input-group">
                        <div class="input-group-addon">Показывать при регистрации</div>
                        <div class="form-control">
                            <input type="checkbox" name="field-reg-show" id="field-reg-show">
                        </div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Показывать поле при регистрации нового пользователя.</div>
                    </div>
                    <br>
                    <div class="input-group">
                        <div class="input-group-addon">Может быть частным</div>
                        <div class="form-control">
                            <input type="checkbox" name="field-private" id="field-private">
                        </div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Добавляет настройку приватности поля в профиль.</div>
                    </div>
                    <br>
                    <div class="input-group">
                        <div class="input-group-addon">Ссылка</div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Введите ссылку, на которую нужно переходить по клику на неё. Значение поля можно получить
                            написав в нужном месте "<a href="#" id="field-add-to-textarea"><strong>{{1}}</strong></a>". Система сама заменит данную конструкцию на значение поля.</div>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Это необязательный параметр. Если Вы не укажете ссылку, то поле
                        будет чисто текстовым, а не представлять из себя ссылку.</div>
                        <textarea class="form-control" name="field-link-textarea" style="resize: vertical; min-height: 100px;" id="field-link-textarea"></textarea>
                    </div>
                    <br>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" id="field-add-ajax-btn"><span class="glyphicons glyphicons-ok"></span> Применить</button>
                        <button class="btn btn-default" type="button" id="field-cancel-btn"><span class="glyphicons glyphicons-erase"></span> Отменить</button>
                    </div>
                </div>
                <hr>
                <h4>Жалобы</h4>
                <div class="input-group">
                    <div class="input-group-addon">Возможные причины жалоб</div>
                    <textarea class="form-control" style="resize: vertical; min-height: 100px;" name="reports-reasons"><?php echo \Engine\Engine::GetReportReasons(); ?></textarea>
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Перечислите все возможные причины жалоб пользователей. Каждая
                    новая причина должна быть на новой строке.</div>
                <hr>
                <h4>Настройки аватарок</h4>
                <div class="input-group">
                    <div class="input-group-addon">Максимальная ширина аватарки</div>
                    <input type="number" class="form-control"  name="avatarmaxwidth" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("aw"));?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Максимальная длина аватарки</div>
                    <input type="number" class="form-control"  name="avatarmaxheight" value="<?php echo htmlentities(\Engine\Engine::GetEngineInfo("ah"));?>">
                </div>
                <hr>
                <h4>Настройки загрузки файлов</h4>
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
                    <div class="form-control">
                        <input type="checkbox" name="guest_see_profiles" <?php if (\Engine\Engine::GetEngineInfo("gsp")) echo "checked"; ?>>
                    </div>
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Под гостями подразумеваются незарегистрированные пользователи.</div>
            </div>
            <?php if ($hasPerms) { ?>
            <div class="div-border" id="metric_sets" data-number="5" hidden>
                <h3><span class="glyphicons glyphicons-pie-chart"></span> Статистика</h3>
                <p class="helper">Здесь меняются настройки статистики сайта.</p>
                <hr>
                <p>Вы можете использовать как сторонние сервисы для анализирования клиентского потока Вашего портала, так и встроенный. Для этого выберите соответствующий
                параметр, нужный Вам. Также, Вы можете вовсе отказаться от использования аналитических сервисов.</p>
                <div class="input-group">
                    <div class="input-group-addon">Записывать статистику:</div>
                    <div class="form-control">
                        <input type="checkbox" name="metric-lever-btn" id="metric-level-btn" <?php if (\Engine\Engine::GetEngineInfo("smt")) echo "checked"; ?>>
                    </div>
                </div>
                <div id="metric-information" style="display: none;">
                    <div class="input-group">
                        <div class="input-group-addon">Сервис:</div>
                        <select id="metric-service-select" name="metric-service-select" class="form-control">
                            <option value="0" <?php if (\Engine\Engine::GetEngineInfo("sms") == 0) echo "selected";?>>Встроенный</option>
                            <option value="1" <?php if (\Engine\Engine::GetEngineInfo("sms") == 1) echo "selected";?>>Сторонний</option>
                        </select>
                    </div>
                    <div class="input-group" style="display: none;" id="metric-code-div">
                        <div class="input-group-addon">Текст для встраивания:</div>
                        <textarea class="form-control" style="height: 300px; resize: none;" name="metric-script-text"><?php echo \Engine\Engine::GetAnalyticScript(); ?></textarea>
                        <div class="form-control info alert-info"><span class="glyphicons glyphicons-info-sign"></span> Здесь должен быть код, который предоставляется сервисом.
                            В инструкции Вас попросят разместить этот код на всех страницах Вашего портала, именно данный текст Вам нужно вставить сюда.</div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="div-border" id="todoeditor" data-number="6" hidden>
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
    var MetricCodeGUIPrepare = function () {
        if ($("#metric-service-select").val() == "1") {
            $("#metric-code-div").show();
        } else {
            $("#metric-code-div").hide();
        }
    };

    var MetricSystemGUIPrepare = function() {
        if ($("#metric-level-btn").is(":checked")){
            $("#metric-information").show();
            MetricCodeGUIPrepare();
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
                $(span).addClass("glyphicons glyphicons-remove");
                $(span).after(" " + text);
                break;
            }
        }
        $('html, body').animate({
            scrollTop: $(div).offset().top-100
        }, 1000);
    };

    $("#field-add-btn").on("click", function() {
       $(this).attr("disabled", true);
       $("#field-panel-manage").show();
       $("#user-add-fields").val(0);
       ClearFieldForm();
    });

    $("#field-cancel-btn").on("click", function () {
        $("#field-add-btn").attr("disabled", false);
        $("#field-remove-btn").attr("disabled", true);
        $("#field-panel-manage").hide();
        ClearFieldForm();
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
                     "&action=" + action;
       $.ajax({
           url: "adminpanel/scripts/ajax/adfieldsajax.php",
           type: "POST",
           data: dataVar,
           success: function (data){
                if (action === "add"){
                    if ($.isNumeric(data)){
                        $("#user-add-fields").append("<option value=\"" + data + "\">" + $("#field-name-input").val() + "</option>");
                        ShowAnswerForm("okey", "Поле \"" + $("#field-name-input").val() + "\" было успешно создано!");
                        $("#field-cancel-btn").click();
                    } else if (data == "in") {
                        ShowAnswerForm("error", "Название не отвечает требованиям: название поля должно быть меньше 16 и больше 4 символов.");
                    }
                }
                if (action === "edit") {
                    if (data === "sef") {
                        $("#field-cancel-btn").click();
                        $("#user-add-fields").val(0);
                        $("#field-panel-manage").hide();
                        ShowAnswerForm("okey", "Параметры поля успешно изменены!");
                    } else if (data == "fef") {
                        ShowAnswerForm("error", "Не удалось отредактировать поле.");
                    } else if (data == "fne") {
                        ShowAnswerForm("error", "Такого поля не существует. Вероятно, его кто-то удалил, обновите страницу.");
                    } else if (data == "in") {
                        ShowAnswerForm("error", "Название не отвечает требованиям: название поля должно быть меньше 16 и больше 4 символов.");
                    }
                }
           },
           error: function (){
               $("#add-fields-info-div").removeClass("hidden");
               $("#add-fields-info-div").addClass("alert-error");
               $("#add-fields-info-div > span").addClass("glyphicons glyphicons-remove");
               $("#add-fields-info-div > span").after(" Не удалось сохранить дополнительное поле.")
           }
       });
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
                   $("#field-panel-manage").show();
                   $("#field-name-input").val(result.name);
                   $("#field-description").val(result.description);
                   $("#field-type-selector").val(result.type);
                   if (result.isRequied == "1")
                       $("#field-requied").prop("checked", true);
                   if (result.inRegister == "1")
                       $("#field-reg-show").prop("checked", true);
                   if (result.canBePrivate == "1"){
                       $("#field-private").prop("checked", true);
                   }
                   $("#field-link-textarea").val(result.link);
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
                        ShowAnswerForm("okey", "Дополнительное поле успешно удалено.");
                        $("#user-add-fields").children("option[value=" + id + "]").remove();
                        $("#user-add-fields").val(0);
                        $("#field-cancel-btn").click();
                    } else if (data == "fdf"){
                        //Failed deleting.
                        ShowAnswerForm("fail", "Не удалось удалить дополнительное поле.");
                    } else if (data == "fne"){
                        ShowAnswerForm("fail", "Такого поля не существует. Возможно его уже кто-то удалил, обновите страницу и попробуйте ещё раз.");
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
                   alert("Тестовое письмо было успешно отправлено.");
               else if (data == "false")
                   alert("Не удалось отправить тестовое сообщение. Проверьте правильность введённых данных.");
           }
       });
    });

    $(document).ready(MetricSystemGUIPrepare);

    $("#metric-level-btn").on("change", MetricSystemGUIPrepare);

    $("#metric-service-select").on("change", MetricCodeGUIPrepare);

    $("button").on("click", function() {
       if ($(this).data("div-number") != undefined){
           var divNum = $(this).data("div-number");
           $("div.custom-group > div.div-border").hide();
           $("div.custom-group > div.div-border[data-number=" + divNum +"]").show();
           $("button").removeClass("active");
           $("button[data-div-number=" + divNum + "]").addClass("active");
       }
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