<?php
if (!defined("TT_AP")){ header("Location: ../adminpanel.php?p=forbidden"); exit; }
if (!$user->UserGroup()->getPermission("sc_edit_pages") &&
    !$user->UserGroup()->getPermission("sc_create_pages") &&
    !$user->UserGroup()->getPermission("sc_remove_pages") &&
    !$user->UserGroup()->getPermission("sc_design_edit")){
    header("Location: ../adminpanel.php?res=1");
    exit;
}

$editPPerm = $user->UserGroup()->getPermission("sc_edit_pages");
$createPPerm = $user->UserGroup()->getPermission("sc_create_pages");
$removePPerm = $user->UserGroup()->getPermission("sc_remove_pages");
$editSContentPerm = $user->UserGroup()->getPermission("sc_design_edit");

if ($editPPerm || $createPPerm || $removePPerm) {
    $tablePage = \Forum\StaticPagesAgent::GetPagesList((!empty($_REQUEST["pl"])) ? $_REQUEST["pl"] : 1);
    $tablePageCount = count($tablePage);
    $page = "";
}

if ($editPPerm && isset($_GET["editpage"]) && \Forum\StaticPagesAgent::isPageExists($_GET["editpage"])){
    $page = new \Forum\StaticPage($_GET["editpage"]);
    $isEditMode = true;
} else {
    $isEditMode = false;
}

if ($editSContentPerm){
    $firstSmallBannerContent = \SiteBuilders\BannerAgent::GetBannersByName("firstbanner")["firstbanner"]["content"];
    $secondSmallBannerContent = \SiteBuilders\BannerAgent::GetBannersByName("smallbanner")["smallbanner"]["content"];
    $bigbanners = \SiteBuilders\BannerAgent::GetBanners("banner");
    $buttons = array();
    foreach ($bigbanners as $bigbanner){
        $bannerId = $bigbanner["id"];
        $bannerName = $bigbanner["name"];
        $class = ($bigbanner["isVisible"] == 1) ? "btn-success" : "btn-danger";
        $buttons[] = "<button class=\"btn $class\" type=\"button\" data-banner-id=\"$bannerId\">$bannerName</button>";
    }
}

?>

<div class="inner cover">
    <h1 class="cover-heading">Управление статическим контентом</h1>
    <p class="lead">Добавление, удаление и редактирование статического контента сайта.</p>
    <div class="btn-group" id="staticc-btn-panel">
        <?php if ($editPPerm || $removePPerm){ ?><button class="btn btn-default" type="button" id="staticc-pages-btn" data-div="staticc-pages-div"><span class="glyphicons glyphicons-pencil"></span> Управление страницами</button><?php } ?>
        <?php if ($createPPerm) { ?>             <button class="btn btn-default" type="button" id="staticc-page-create-btn" data-div="staticc-page-create-div"><span class="glyphicons glyphicons-file-plus"></span> Создание страниц</button><?php } ?>
        <?php if ($editSContentPerm) { ?>        <button class="btn btn-default" type="button" id="staticc-content-edit-btn" data-div="staticc-content-edit-div"><span class="glyphicons glyphicons-puzzle-2"></span> Редактирование статических компонентов</button><?php } ?>
        <?php if ($isEditMode && $editPPerm) { ?><button class="btn btn-info" type="button" id="staticc-page-edit-btn" data-div="staticc-page-edit-div"><span class="glyphicons glyphicons-edit"></span> Редактирование страницы - "<?php echo $page->getPageName(); ?>"</button><?php } ?>
    </div>
    <form enctype="multipart/form-data" action="adminpanel/scripts/staticc.php" method="post">
        <div class="custom-group" id="staticc-panel">
            <?php if ($editPPerm || $removePPerm) { ?>
            <div class="div-border" id="staticc-pages-div" hidden>
                <h2>Управление страницами</h2>
                <p class="helper">Осуществление управления статическими страницами.</p>
                <hr>
                <p>Статические страницы - это страницы, вшитые в сам сайт. Они не являются топиками, в них нельзя оставлять коментарии, в чём и заключается их удобство.
                Здесь Вы можете создавать таковые, редактировать и удалять их. Вы можете искать нужные Вам страницы по их названию и по никнейму их автора. Для переключения
                режима поиска воспользуйтесь кнопками в конце поля ввода. Неизвестные места можно отмечать знаком звёздочки (*).</p>
                <input type="hidden" id="staticc-search-type" name="staticc-search-type">
                <div class="input-group">
                    <input class="form-control" type="text" id="staticc-search-input" name="staticc-search-input" placeholder="Название страницы">
                    <div class="input-group-btn">
                        <button class="btn btn-default active" type="button" id="staticc-search-byname-btn" title="Искать по названию страницы"><span class="glyphicons glyphicons-subtitles"></span></button>
                        <button class="btn btn-default" type="button" id="staticc-search-byauthor-btn" title="Искать по никнейму автора"><span class="glyphicons glyphicons-nameplate"></span></button>
                    </div>
                </div>
                <br>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" name="staticc-search-btn"><span class="glyphicons glyphicons-search"></span> Искать</button>
                    <button class="btn btn-default" type="submit" name="staticc-search-reset-btn"><span class="glyphicons glyphicons-book"></span> Сбросить фильтр</button>
                    <?php if ($removePPerm) { ?><button class="btn btn-default alert-danger" type="submit" name="staticc-search-remove-btn" id="staticc-search-remove-btn" disabled><span class="glyphicons glyphicons-bin"></span> Удалить выделенные страницы</button><?php }?>
                </div>
                <h3>Список созданных статических страниц</h3>
                <div class="alert alert-info" id="staticc-selected-div" style="display: none;"><strong>Выделено страниц:</strong> <span>0</span></div>
                <table class="table" id="staticc-pages-table">
                    <thead>
                        <tr class="staticc-table-header">
                            <td><input type="checkbox" id="staticc-table-select-all-checkbox"></td>
                            <td>Название страницы</td>
                            <td>Описание страницы</td>
                            <td>Автор</td>
                            <td>Время создания</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($tablePageCount == 0) { ?>

                        <tr>
                            <td colspan="6" class="alert-info" style="text-align: center;"><span class="glyphicons glyphicons-info-sign"></span> Пока что не создано ни одной статической страницы.</td>
                        </tr>
                    <?php } else ?>
                    <?php foreach($tablePage as $item){
                        $p = new \Forum\StaticPage($item); ?>

                        <tr>
                            <td><input type="checkbox" data-spi="<?php echo $p->getPageID(); ?>"></td>
                            <td><a href="/?sp=<?php echo $p->getPageID(); ?>"><?php echo $p->getPageName(); ?></a></td>
                            <td><?php echo $p->getPageDescription(); ?></td>
                            <td><?php echo \Users\UserAgent::GetUserNick($p->getPageAuthorId()); ?></td>
                            <td><?php echo \Engine\Engine::DateFormatToRead($p->getPageCreateDate()); ?></td>
                            <td><button class="btn btn-default alert-info" name="staticc-page-edit-btn" type="submit" formaction="adminpanel/scripts/staticc.php?id=<?php echo $p->getPageID(); ?>" style="width: 100%;">Редактировать</button></td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
                <input type="hidden" id="staticc-page-delete" name="staticc-page-delete">
            </div>
            <?php }
            if ($createPPerm) { ?>
            <div class="div-border" id="staticc-page-create-div" hidden>
                <h2>Создание статической страницы</h2>
                <p class="helper">Редактор новой статической страницы.</p>
                <hr>
                <p><strong>Все поля</strong>, кроме описания, требуют заполнения. Минимальная длина названия страницы - 4 символа, а текст страницы должен быть не менее 20 символов.</p>
                <div class="alert alert-info">
                    <p><span class="glyphicons glyphicons-info-sign"></span> Адрес созданной Вами страницы будет следующий:</p>
                    <hr>
                    <input class="form-control" type="text" readonly value="http://<?php echo $_SERVER["HTTP_HOST"]; ?>/?sp=<?php echo \Forum\StaticPagesAgent::GetLastPageID()+1; ?>">
                    <hr>
                    <p>Этот адрес статичен, его нельзя поменять.</p>
                </div>
                <input class="form-control" name="staticc-page-create-name-input" type="text" maxlength="25" placeholder="Название страницы">
                <br>
                <input class="form-control" name="staticc-page-create-description-input" type="text" maxlength="100" placeholder="Описание страницы">
                <br>
                <div class="btn-group">
                    <button class="btn btn-default" type="button" title="Жирный шрифт" name="bb_b"><strong>B</strong></button>
                    <button class="btn btn-default" type="button" title="Курсив" name="bb_i"><i>I</i></button>
                    <button class="btn btn-default" type="button" title="Подчёркивание" name="bb_u"><u>U</u></button>
                    <button class="btn btn-default" type="button" title="Зачёркивание" name="bb_s"><s>S</s></button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default" type="button" title="Ротация влево" name="bb_left"><span class="glyphicon glyphicon-align-left"></span></button>
                    <button class="btn btn-default" type="button" title="Ротация по центру" name="bb_center"><span class="glyphicon glyphicon-align-center"></span></button>
                    <button class="btn btn-default" type="button" title="Ротация вправо" name="bb_right"><span class="glyphicon glyphicon-align-right"></span></button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default" type="button" title="Разделитель" name="bb_hr"><span class="glyphicon glyphicon-minus"></span></button>
                    <button class="btn btn-default" type="button" title="Перечисление" name="bb_ol"><span class="glyphicon glyphicon-th-list"></span></button>
                    <button class="btn btn-default" type="button" title="Элемент списка" name="bb_item" style="background: #c0ffb4;"><span class="glyphicon glyphicon-star"></span></button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default" type="button" title="Ссылка" name="bb_a"><span class="glyphicon glyphicon-link"></span></button>
                    <button class="btn btn-default" type="button" title="Вставить картинку" name="bb_img"><span class="glyphicon glyphicon-picture"></span></button>
                    <button class="btn btn-default" type="button" title="Вставить ролик YouTube" name="bb_youtube"><span class="glyphicon glyphicon-play"></span></button>
                </div>
                <div class="btn-group">
                    <select class="btn btn-default" title="Цвет шрифта" name="bb_color">
                        <option value="black" style="color: black;">Чёрный</option>
                        <option value="red" style="color: red;">Красный</option>
                        <option value="green" style="color: green;">Зелёный</option>
                        <option value="yellow" style="color: yellow;">Жёлтый</option>
                        <option value="orange" style="color: orange;">Оранжевый</option>
                        <option value="blue" style="color: blue;">Синий</option>
                        <option value="grey" style="color: grey;">Серый</option>
                        <option value="darkgrey" style="color: #545454;">Тёмносерый</option>
                        <option value="white" style="color: white; text-shadow: 1px 1px 1px black;">Белый</option>
                    </select>
                    <select class="btn btn-default" title="Размер шрифта" name="bb_size">
                        <option value="12">12</option>
                        <option value="14">14</option>
                        <option value="16">16</option>
                        <option value="18">18</option>
                        <option value="20">20</option>
                    </select>
                </div>
                <hr/>
                <textarea class="form-control" placeholder="Введите содержимое страницы." style="width: 100%; min-height: 250px; resize: vertical; " id="staticc-page-create-textarea" name="staticc-page-create-textarea"></textarea>
                <hr/>
                <div class="center">
                    <div class="btn-group">
                        <button class="btn btn-default" type="submit" name="staticc-page-create-create-btn"><span class="glyphicon glyphicon-ok"></span> Опубликовать страницу</button>
                    </div>
                </div>
            </div>
            <?php }
            if ($isEditMode && $editPPerm) { ?>
                <div class="div-border" id="staticc-page-edit-div" hidden>
                    <h2>"<?php echo $page->getPageName(); ?>"</h2>
                    <p class="helper">Редактирование статической страницы.</p>
                    <input type="hidden" value="<?php echo $page->getPageID(); ?>" name="staticc-page-edit-id">
                    <div class="alert alert-info">
                        <p><span class="glyphicons glyphicons-info-sign"></span> Адрес редактируемой страницы:</p>
                        <hr>
                        <input class="form-control" type="text" readonly="" value="http://<?php echo $_SERVER["HTTP_HOST"] . "/?sp=" . $page->getPageID(); ?>">
                        <hr>
                        <p>Этот адрес статичен: его нельзя поменять.</p>
                    </div>
                    <input class="form-control" type="text" maxlength="25" placeholder="Название страницы" name="staticc-page-edit-name-input" value="<?php echo $page->getPageName(); ?>">
                    <br>
                    <input class="form-control" type="text" maxlength="100" placeholder="Описание страницы" name="staticc-page-edit-description-input" value="<?php echo $page->getPageDescription(); ?>">
                    <br>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="Жирный шрифт" name="bb_b"><strong>B</strong></button>
                        <button class="btn btn-default" type="button" title="Курсив" name="bb_i"><i>I</i></button>
                        <button class="btn btn-default" type="button" title="Подчёркивание" name="bb_u"><u>U</u></button>
                        <button class="btn btn-default" type="button" title="Зачёркивание" name="bb_s"><s>S</s></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="Ротация влево" name="bb_left"><span class="glyphicon glyphicon-align-left"></span></button>
                        <button class="btn btn-default" type="button" title="Ротация по центру" name="bb_center"><span class="glyphicon glyphicon-align-center"></span></button>
                        <button class="btn btn-default" type="button" title="Ротация вправо" name="bb_right"><span class="glyphicon glyphicon-align-right"></span></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="Разделитель" name="bb_hr"><span class="glyphicon glyphicon-minus"></span></button>
                        <button class="btn btn-default" type="button" title="Перечисление" name="bb_ol"><span class="glyphicon glyphicon-th-list"></span></button>
                        <button class="btn btn-default" type="button" title="Элемент списка" name="bb_item" style="background: #c0ffb4;"><span class="glyphicon glyphicon-star"></span></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" type="button" title="Ссылка" name="bb_a"><span class="glyphicon glyphicon-link"></span></button>
                        <button class="btn btn-default" type="button" title="Вставить картинку" name="bb_img"><span class="glyphicon glyphicon-picture"></span></button>
                        <button class="btn btn-default" type="button" title="Вставить ролик YouTube" name="bb_youtube"><span class="glyphicon glyphicon-play"></span></button>
                    </div>
                    <div class="btn-group">
                        <select class="btn btn-default" title="Цвет шрифта" name="bb_color">
                            <option value="black" style="color: black;">Чёрный</option>
                            <option value="red" style="color: red;">Красный</option>
                            <option value="green" style="color: green;">Зелёный</option>
                            <option value="yellow" style="color: yellow;">Жёлтый</option>
                            <option value="orange" style="color: orange;">Оранжевый</option>
                            <option value="blue" style="color: blue;">Синий</option>
                            <option value="grey" style="color: grey;">Серый</option>
                            <option value="darkgrey" style="color: #545454;">Тёмносерый</option>
                            <option value="white" style="color: white; text-shadow: 1px 1px 1px black;">Белый</option>
                        </select>
                        <select class="btn btn-default" title="Размер шрифта" name="bb_size">
                            <option value="12">12</option>
                            <option value="14">14</option>
                            <option value="16">16</option>
                            <option value="18">18</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                    <hr/>
                    <textarea class="form-control" placeholder="Введите содержимое страницы." style="width: 100%; min-height: 250px; resize: vertical; " id="staticc-page-edit-textarea" name="staticc-page-edit-textarea"><?php echo $page->getContent(); ?></textarea>
                    <hr/>
                    <div class="center">
                        <div class="btn-group">
                            <button class="btn btn-default" type="submit" name="staticc-page-edit-edit-btn"><span class="glyphicon glyphicon-ok"></span> Принять правки</button>
                            <button class="btn btn-default" type="reset"><span class="glyphicon glyphicon-erase"></span> Сбросить изменения</button>
                        </div>
                    </div>
                </div>
            <?php }
            if ($editSContentPerm) { ?>
            <div class="div-border" id="staticc-content-edit-div" hidden>
                <h2>Редактирование статических компонентов</h2>
                <p class="helper">Изменение контента боковых панелей, баннеров и навигационной панели.</p>
                <hr>
                <p>Здесь Вы можете редактировать нижние и верхний баннер, контент боковых панелей, в том числе их название. Здесь же, можно управлять полями, которые будут в навигационной панели главной страницы.</p>
                <div class="btn-group" id="staticc-content-btn-panel">
                    <button class="btn btn-default active" type="button" data-subpanel-id="staticc-content-banners"><span class="glyphicons glyphicons-drop"></span> Баннеры</button>
                    <button class="btn btn-default" type="button" data-subpanel-id="staticc-content-sidepanels"><span class="glyphicons glyphicons-more-items"></span> Боковые панели</button>
                    <button class="btn btn-default" type="button" data-subpanel-id="staticc-content-navbar"><span class="glyphicons glyphicons-map"></span> Навигационная панель</button>
                </div>
                <hr>
                <div id="staticc-content-error-div" hidden><span id="staticc-content-error-span"></span></div>
                <div id="staticc-content-container">
                    <div id="staticc-content-banners" hidden>
                        <p>На сайте стандартно присутствуют четыре баннера: два размером 88х31 и два 468х60. Последние два не появляются, если нет ни одной созданной темы.
                        Большие баннеры появляются в случайном порядке.</p>
                        <div class="input-group">
                            <div class="input-group-addon">Первый баннер</div>
                            <input class="form-control" type="text" id="staticc-firstsm-html-input" placeholder="HTML-код для первого баннера 88х31" value="<?php echo $firstSmallBannerContent; ?>">
                            <div class="input-group-addon">Размер: 88х31</div>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Действия <span class="caret"></span></button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a id="staticc-smbanner-first-save" title="Сохранить код баннера."><span class="glyphicons glyphicons-ok"></span> Сохранить</a></li>
                                    <li><a id="staticc-smbanner-first-remove" title="Удалить баннер. При этом отчистится поле."><span class="glyphicons glyphicons-remove"></span> Удалить</a></li>
                                    <li><a id="staticc-smbanner-first-clear" title="Отчистить поле. Удаления баннера не произойдёт."><span class="glyphicons glyphicons-erase"></span> Отчистить</a></li>
                                </ul>
                            </div>
                        </div>
                        <br>
                        <div class="input-group">
                            <div class="input-group-addon">Второй баннер</div>
                            <input class="form-control" type="text" id="staticc-secondsm-html-input" placeholder="HTML-код для второго баннера 88х31" value="<?php echo $secondSmallBannerContent; ?>">
                            <div class="input-group-addon">Размер: 88х31</div>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Действия <span class="caret"></span></button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a id="staticc-smbanner-second-save" title="Сохранить код баннера."><span class="glyphicons glyphicons-ok"></span> Сохранить</a></li>
                                    <li><a id="staticc-smbanner-second-remove" title="Удалить баннер. При этом отчистится поле."><span class="glyphicons glyphicons-remove"></span> Удалить</a></li>
                                    <li><a id="staticc-smbanner-second-clear" title="Отчистить поле. Удаления баннера не произойдёт."><span class="glyphicons glyphicons-erase"></span> Отчистить</a></li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <div class="container-fluid">
                            <div class="btn-group-vertical col-lg-3 col-md-6 col-sm-6 col-xs-12" id="staticc-banner-btns">
                                Больших баннеров: <span id="staticc-banners-counter"><?php echo \SiteBuilders\BannerAgent::GetBigBannersCount(); ?></span>
                                <button class="btn btn-default" type="button" id="staticc-create-banner-btn"><span class="glyphicons glyphicons-plus-sign"></span> Добавить баннер</button>
                                <?php foreach($buttons as $b){
                                    echo $b;
                                } ?>
                            </div>
                            <div class="div-border col-lg-9 col-md-6 col-sm-6 col-xs-12" id="staticc-create-banner-div" style="display: none;">
                                <input type="hidden" id="staticc-banner-current-id">
                                <p>В данной форме создаются большие баннеры. Их размер должен быть точно 468х60.</p>
                                <input class="form-control" type="text" id="staticc-create-banner-name-input" placeholder="Название баннера">
                                <p class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> Название нигде не будет отображаться, оно нужно для удобства Вашей координации между созданными баннерами.</p>
                                <input class="form-control" type="text" id="staticc-create-banner-link-input" placeholder="HTML-код баннера">
                                <br>
                                <label for="staticc-create-banner-visibility-input">Включить баннер: </label>
                                <input type="checkbox" id="staticc-create-banner-visibility-input">
                                <p class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> Если баннер отключен, то он не будет выводится.</p>
                                <div class="btn-group">
                                    <button class="btn btn-default" type="button" id="staticc-create-banner-send-btn"><span class="glyphicons glyphicons-ok"></span> </button>
                                        <button class="btn btn-default" type="button" id="staticc-remove-banner-send-btn"><span class="glyphicons glyphicons-erase"></span> Удалить баннер</button>
                                    <button class="btn btn-default" type="button" id="staticc-create-banner-cancel-btn"><span class="glyphicons glyphicons-remove"></span> Отмена</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="staticc-content-sidepanels" hidden>
                        <p>Здесь Вы можете редактировать заголовок и содержание колонок, а также их количество.</p>
                        <div class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> Если Вы удалите все колонки с одной стороны, блоки сайта не сдвинутся.</div>
                        <div class="container-fluid">
                            <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" id="staticc-sp-left-div" style="display: none;">
                                <div class="side-block">
                                    <div class="side-block-header-left">{PANEL_TITLE}</div>
                                    <div class="side-block-body">
                                        {PANEL_CONTENT}
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">

                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" id="staticc-sp-right-div" style="display: none;">
                                <div class="side-block">
                                    <div class="side-block-header-left">{PANEL_TITLE}</div>
                                    <div class="side-block-body">
                                        {PANEL_CONTENT}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="staticc-content-navbar" hidden>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </form>
</div>
<script type="text/javascript">
    $("#staticc-panel :first-child").show();
    $("#staticc-btn-panel :first-child").addClass("active");

    $("div#staticc-btn-panel > button").on("click", function() {
        var data = $(this).data("div");
        $("div#staticc-panel > div").hide();
        $("div#" + data).show();
        $(this).parent("div").children("button.active").removeClass("active");
        $(this).addClass("active");
    });

    <?php if ($editSContentPerm) { ?>
    function ShowSCErrorBox(type, message){
        var div = $("div#staticc-content-error-div");
        var span = $("span#staticc-content-error-span");
        switch (type){
            case 1:
            case "okey":
            case "ok":
            case "true":
            case "success":
            case true:
                $(div).text("");
                $(div).prop("class", "alert alert-success");
                $(span).prop("class", "glyphicon glyphicon-ok");
                $(div).append($(span));
                $(div).append(" " + message);
                $(div).show();
                break;
            case 0:
            case "error":
            case "failed":
            case "fail":
            case false:
                $(div).text("");
                $(div).prop("class", "alert alert-danger");
                $(span).prop("class", "glyphicon glyphicon-remove");
                $(div).append($(span));
                $(div).append(" " + message);
                $(div).show();
                break;
            default:
                return false;
        }
        $('html, body').animate({
            scrollTop: $(div).offset().top-100
        }, 1000);
    }
    function HideSCErrorBox(){
        $("div#staticc-content-error-div").hide("slow");
    }

    $("div#staticc-content-btn-panel > button").on("click", function(){
        var data = $(this).data("subpanel-id");
        $("div#staticc-content-container > div").hide();
        $("div#" + data).show();
        $(this).parent("div").children("button").removeClass("active");
        $(this).addClass("active");
    });

    //First small banner actions
    $("a#staticc-smbanner-first-save").on("click", function(){
        var dataInfo = "savefsb&link=" + $("input#staticc-firstsm-html-input").val();
       $.ajax({
           url: "adminpanel/scripts/ajax/bannersajax.php",
           type: "POST",
           data: dataInfo,
           success: function(data){
               if ($.isNumeric(data) || data === "okey") {
                   ShowSCErrorBox("okey", "Первый баннер был успешно сохранён!");
                   $("input#staticc-firstsm-html-input").data("fsbid", data);
               }
               else if (data === "failed")
                   ShowSCErrorBox("fail", "Не удалось сохранить первый баннер.");
               else
                   ShowSCErrorBox("fail", "Не был отослан HTML код первого баннера.");
           }
       });
    });
    $("a#staticc-smbanner-first-remove").on("click", function(){
        var dataInfo = "removefsb&id=" + $("input#staticc-firstsm-html-input").data("fsbid");
        $.ajax({
            url: "adminpanel/scripts/ajax/bannersajax.php",
            type: "POST",
            data: dataInfo,
            success: function(data){
                if (data === "okey")
                    ShowSCErrorBox("okey", "Первый баннер был успешно удалён.");
                else if (data === "failed")
                    ShowSCErrorBox("fail", "Не удалось удалить первый баннер.");
            }
        });
    });
    $("a#staticc-smbanner-first-clear").on("click", function(){
        $("input#staticc-firstsm-html-input").val("");
    });

    //Second small banner actions
    $("a#staticc-smbanner-second-save").on("click", function(){
        var dataInfo = "savessb&link=" + $("input#staticc-secondsm-html-input").val();
        $.ajax({
            url: "adminpanel/scripts/ajax/bannersajax.php",
            type: "POST",
            data: dataInfo,
            success: function(data){
                if ($.isNumeric(data) || data === "okey") {
                    ShowSCErrorBox("okey", "Второй баннер был успешно сохранён!");
                    $("input#staticc-secondsm-html-input").data("ssbid", data);
                }
                else if (data === "failed")
                    ShowSCErrorBox("fail", "Не удалось сохранить второй баннер.");
                else
                    ShowSCErrorBox("fail", "Не был отослан HTML код второго баннера.");
            }
        });
    });
    $("a#staticc-smbanner-second-remove").on("click", function(){
        var dataInfo = "removessb&id=" + $("input#staticc-secondsm-html-input").data("ssbid");
        $.ajax({
            url: "adminpanel/scripts/ajax/bannersajax.php",
            type: "POST",
            data: dataInfo,
            success: function(data){
                if (data === "okey")
                    ShowSCErrorBox("okey", "Второй баннер был успешно удалён.");
                else if (data === "failed")
                    ShowSCErrorBox("fail", "Не удалось удалить второй баннер.");
            }
        });
    });
    $("a#staticc-smbanner-second-clear").on("click", function(){
        $("input#staticc-secondsm-html-input").val("");
    });

    //Big banner edit or create.
    $("button#staticc-create-banner-send-btn").on("click", function() {
        if ($("input#staticc-banner-current-id").val() == ""){
            $.ajax({
                type: "POST",
                url : "adminpanel/scripts/ajax/bannersajax.php",
                data: "addbbaner&banner-name=" + $("input#staticc-create-banner-name-input").val() +
                        "&banner-content=" + $("input#staticc-create-banner-link-input").val() +
                        "&banner-visibility=" + (($("input#staticc-create-banner-visibility-input").is(":checked")) ? 1 : 0),
                success: function (data){
                    if (data === "failed")
                        ShowSCErrorBox("error", "Не удалось создать баннер.");
                    else if (data === "nns")
                        ShowSCErrorBox("error", "Вы не указали имя баннера.");
                    else if (data === "cns")
                        ShowSCErrorBox("error", "Вы не указали HTML-код баннера.");
                    else if ($.isNumeric(data)){
                        var button = document.createElement("button");
                        if ($("input#staticc-create-banner-visibility-input").is(":checked"))
                            $(button).prop("class", "btn btn-success");
                        else
                            $(button).prop("class", "btn btn-danger");
                        $(button).text($("input#staticc-create-banner-name-input").val());
                        $(button).prop("type", "button");
                        $(button).attr("data-banner-id", data);
                        $("div#staticc-banner-btns").append(button);
                        ShowSCErrorBox("success", "Баннер \"" + $("input#staticc-create-banner-name-input").val() + "\" был успешно создан!");
                        $("span#staticc-banners-counter").val($("span#staticc-banners-counter").val()+1);
                        $("button#staticc-create-banner-cancel-btn").click();
                    }
                }
            });
        } else {
            var clicked = $("div#staticc-banner-btns button[data-banner-id=" + $("input#staticc-banner-current-id").val() + "]");
            $.ajax({
                type: "POST",
                url : "adminpanel/scripts/ajax/bannersajax.php",
                data: "editbbaner&banner-id=" + $("input#staticc-banner-current-id").val() +
                    "&banner-name=" + $("input#staticc-create-banner-name-input").val() +
                    "&banner-content=" + $("input#staticc-create-banner-link-input").val() +
                    "&banner-visibility=" + (($("input#staticc-create-banner-visibility-input").is(":checked")) ? 1 : 0),
                success: function (data){
                    if (data === "failed")
                        ShowSCErrorBox("error", "Не удалось сохранить баннер.");
                    else if (data === "nns")
                        ShowSCErrorBox("error", "Вы не указали имя баннера.");
                    else if (data === "cns")
                        ShowSCErrorBox("error", "Вы не указали HTML-код баннера.");
                    else if (data === "okey"){
                        ShowSCErrorBox("success", "Баннер \"" + $("input#staticc-create-banner-name-input").val() + "\" был успешно сохранён!");
                        if ($("input#staticc-create-banner-visibility-input").is(":checked"))
                            $(clicked).prop("class", "btn btn-success");
                        else
                            $(clicked).prop("class", "btn btn-danger");
                        $(clicked).text($("input#staticc-create-banner-name-input").val());
                        $("button#staticc-create-banner-cancel-btn").click();
                    }
                }
            });
        }
    });

    //Big banner cancel form.
    $("button#staticc-create-banner-cancel-btn").on("click", function() {
        $(this).parent("div").parent("div").children("input[type=text]").val("");
        $(this).parent("div").parent("div").children("input[type=checkbox]").prop("checked", false);
        $(this).parent("div").parent("div").hide("slow");
    });

    //Click on buttons in side frame. Here is handler "Create banner".
    $("div#staticc-banner-btns > button").on("click", function() {
        $("div#staticc-create-banner-div").show("slow");
        var span = $("button#staticc-create-banner-send-btn > span");
        $("button#staticc-create-banner-send-btn").val("");
        if ($(this).data("banner-id") === undefined){
            $("button#staticc-remove-banner-send-btn").hide("slow");
            $("button#staticc-create-banner-send-btn").html("");
            $("button#staticc-create-banner-send-btn").append($(span));
            $("button#staticc-create-banner-send-btn").append(" Создать баннер");
            $("div#staticc-create-banner-div > input[type=text]").val("");
            $("div#staticc-create-banner-div > input[type=checkbox]").prop("checked", false);
        } else {
            $("button#staticc-remove-banner-send-btn").show("slow");
            $("input#staticc-banner-current-id").val($(this).data("banner-id"));
            $("button#staticc-create-banner-send-btn").html("");
            $("button#staticc-create-banner-send-btn").append($(span));
            $("button#staticc-create-banner-send-btn").append(" Изменить баннер");
            $("input#staticc-create-banner-name-input").val($(this).text());
            if ($(this).hasClass("btn-success"))
                $("input#staticc-create-banner-visibility-input").prop("checked", true);
            else
                $("input#staticc-create-banner-visibility-input").prop("checked", false);
            $.ajax({
                type: "POST",
                url : "adminpanel/scripts/ajax/bannersajax.php",
                data: "getbbanner&banner-id=" + $("input#staticc-banner-current-id").val(),
                success: function(data){
                    if (data !== "failed") {
                        $("input#staticc-create-banner-link-input").val(data);
                        HideSCErrorBox();
                    } else {
                        ShowSCErrorBox("failed", "Не удалось получить HTML-код баннера.");
                    }
                }
            });
        }
    });

    $("button#staticc-remove-banner-send-btn").on("click", function() {
       $.ajax({
           type: "POST",
           url: "adminpanel/scripts/ajax/bannersajax.php",
           data: "removebbanner&banner-id=" + $("input#staticc-banner-current-id").val(),
           success: function (data){
               if (data === "okey"){
                   ShowSCErrorBox("okey", "Баннер \"" + $("input#staticc-create-banner-name-input").val() + "\" был успешно удалён.");
                   $("div#staticc-banner-btns > button[data-banner-id=" + $("input#staticc-banner-current-id").val() + "]").remove();
                   $("span#staticc-banners-counter").val($("span#staticc-banners-counter").val()-1);
                   $("button#staticc-create-banner-cancel-btn").click();
               }
               else if (data === "failed"){
                   ShowSCErrorBox("failed", "Не удалось удалить баннер.");
               }
               else if (data === "bne"){
                   ShowSCErrorBox("failed", "Такого баннера не существует.");
               }
           }
       });
    });

    
    <?php } ?>

    function popup(){
        var counter = $("table#staticc-pages-table > tbody input[type=checkbox]:checked").length;
        if (counter > 0 ) {
            $("button#staticc-search-remove-btn").prop("disabled", false);
            $("div#staticc-selected-div").show();
            $("div#staticc-selected-div > span").html(counter);
            var idForDeleting = "";
            $("table#staticc-pages-table > tbody input[type=checkbox]:checked").each(function() {
                idForDeleting += $(this).data("spi") + ",";
            });
            idForDeleting = idForDeleting.substring(0, idForDeleting.length-1);
            $("input#staticc-page-delete").val(idForDeleting);
        } else {
            $("button#staticc-search-remove-btn").prop("disabled", true);
            $("div#staticc-selected-div").hide();
            $("div#staticc-selected-div > span").html(counter);
            $("input#staticc-page-delete").val("");
        }
    }

    $("#staticc-table-select-all-checkbox").on("change", function() {
        if ($(this).prop("checked") == true) {
            $("table#staticc-pages-table > tbody input[type=checkbox]").prop("checked", true);
            popup();
        } else {
            $("table#staticc-pages-table > tbody input[type=checkbox]").prop("checked", false);
            popup();
        }
    });

    $("table#staticc-pages-table > tbody input[type=checkbox]").on("change", function(){
        popup();
    });

    <?php if (isset($_REQUEST["reqtype"])){
    switch ($_REQUEST["reqtype"]){
    case 1:
    ?>$("div#staticc-btn-panel > button:nth-child(2)").click();
    <?php break;
    case 2:
    ?>$("div#staticc-btn-panel > button:nth-child(3)").click();
    <?php break;
    case 3:
    ?>$("div#staticc-btn-panel > button:nth-child(4)").click();
    <?php break;
    }
    } ?>

    function insertBBCode(openTag, notNeedClose){
        if ($("#staticc-page-create-div").css("display") !== "none") {
            var texter = document.getElementById("staticc-page-create-textarea");
        }
        if ($("#staticc-page-edit-div").css("display") !== "none") {
            var texter = document.getElementById("staticc-page-edit-textarea");
        }

        startText = texter.value.substring(0, texter.selectionStart);
        endText = texter.value.substring(texter.selectionEnd, texter.value.length);
        tagingText = texter.value.substring(texter.selectionStart, texter.selectionEnd);
        startPos = texter.selectionStart;
        endPos = texter.selectionEnd;
        startText += '[' + openTag + ']';
        if( notNeedClose != null) {
            if (!notNeedClose) endText = '[\/' + openTag + ']' + endText;
            else endText = '[\/' + notNeedClose + ']' + endText;
        }
        texter.value = startText + tagingText + endText;
        texter.focus();
        texter.setSelectionRange(startPos + (2 + openTag.length), endPos + (2 + openTag.length));
    }

    $('button[name=bb_b]').click(function(){
        insertBBCode('b', false);
    });
    $('button[name=bb_s]').click(function(){
        insertBBCode('s', false);
    });
    $('button[name=bb_u]').click(function(){
        insertBBCode('u', false);
    });
    $('button[name=bb_i]').click(function(){
        insertBBCode('i', false);
    });
    $('button[name=bb_hr]').click(function(){
        insertBBCode('hr', null);
    });
    $('button[name=bb_ol]').click(function(){
        insertBBCode('ol', false);
    });
    $('button[name=bb_item]').click(function(){
        insertBBCode('*', null);
    });
    $('button[name=bb_a]').click(function(){
        insertBBCode('link', false);
    });
    $('button[name=bb_img]').click(function(){
        insertBBCode('img=', null);
    });
    $('button[name=bb_youtube]').click(function(){
        insertBBCode('youtube=', null);
    });
    $('select[name=bb_size]').on("change",function(){
        insertBBCode('size='+this.options[this.selectedIndex].value, 'size');
    });
    $('select[name=bb_color]').on("change",function(){
        insertBBCode('color='+this.options[this.selectedIndex].value, 'color');
    });
</script>

