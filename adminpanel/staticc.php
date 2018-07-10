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

}

?>

<div class="inner cover">
    <h1 class="cover-heading">Управление статическим контентом</h1>
    <p class="lead">Добавление, удаление и редактирование статического контента сайта.</p>
    <div class="btn-group" id="staticc-btn-panel">
        <?php if ($editPPerm || $removePPerm){ ?><button class="btn btn-default" type="button" id="staticc-pages-btn" data-div="staticc-pages-div" onclick="showDiv($('#staticc-pages-btn'));"><span class="glyphicons glyphicons-pencil"></span> Управление страницами</button><?php } ?>

        <?php if ($createPPerm) { ?><button class="btn btn-default" type="button" id="staticc-page-create-btn" data-div="staticc-page-create-div" onclick="showDiv($('#staticc-page-create-btn'));"><span class="glyphicons glyphicons-pencil"></span> Создание страниц</button><?php } ?>

    </div>
    <form enctype="multipart/form-data" action="adminpanel/scripts/staticc.php" method="post">
        <div class="custom-group" id="staticc-panel">
            <?php if ($editPPerm || $removePPerm){ ?>

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
                    <?php if ($removePPerm) { ?><button class="btn btn-default alert-danger" type="submit" name="staticc-search-remove-btn" disabled><span class="glyphicons glyphicons-bin"></span> Удалить страницу</button><?php }?>

                </div>
                <h3>Список созданных статических страниц</h3>
                <table class="table">
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
                            <td><button class="btn btn-default alert-info" type="submit" >Редактировать</button></td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>
            <?php } if ($createPPerm) { ?>

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
                    <p>Этот адрес статичен: его нельзя поменять.</p>
                </div>
                <input class="form-control" name="staticc-page-create-name-input" type="text" maxlength="25" placeholder="Название страницы">
                <br>
                <input class="form-control" name="staticc-page-create-description-input" type="text" maxlength="100" placeholder="Описание страницы">
                <br>
                <div class="btn-group">
                    <button class="btn btn-default" type="button" title="Жирный шрифт" id="bb_b"><strong>B</strong></button>
                    <button class="btn btn-default" type="button" title="Курсив" id="bb_i"><i>I</i></button>
                    <button class="btn btn-default" type="button" title="Подчёркивание" id="bb_u"><u>U</u></button>
                    <button class="btn btn-default" type="button" title="Зачёркивание" id="bb_s"><s>S</s></button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default" type="button" title="Ротация влево" id="bb_left"><span class="glyphicon glyphicon-align-left"></span></button>
                    <button class="btn btn-default" type="button" title="Ротация по центру" id="bb_center"><span class="glyphicon glyphicon-align-center"></span></button>
                    <button class="btn btn-default" type="button" title="Ротация вправо" id="bb_right"><span class="glyphicon glyphicon-align-right"></span></button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default" type="button" title="Разделитель" id="bb_hr"><span class="glyphicon glyphicon-minus"></span></button>
                    <button class="btn btn-default" type="button" title="Перечисление" id="bb_ol"><span class="glyphicon glyphicon-th-list"></span></button>
                    <button class="btn btn-default" type="button" title="Элемент списка" id="bb_item" style="background: #c0ffb4;"><span class="glyphicon glyphicon-star"></span></button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default" type="button" title="Ссылка" id="bb_a"><span class="glyphicon glyphicon-link"></span></button>
                    <button class="btn btn-default" type="button" title="Вставить картинку" id="bb_img"><span class="glyphicon glyphicon-picture"></span></button>
                    <button class="btn btn-default" type="button" title="Вставить ролик YouTube" id="bb_youtube"><span class="glyphicon glyphicon-play"></span></button>
                </div>
                <div class="btn-group">
                    <select class="btn btn-default" title="Цвет шрифта" id="bb_color">
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
                    <select class="btn btn-default" title="Размер шрифта" id="bb_size">
                        <option value="12">12</option>
                        <option value="14">14</option>
                        <option value="16">16</option>
                        <option value="18">18</option>
                        <option value="20">20</option>
                    </select>
                </div>
                <hr/>
                <textarea class="form-control" placeholder="Введите содержимое страницы." style="width: 100%; min-height: 250px; resize: vertical; " id="staticc-page-create-textarea" name="staticc-page-create-textarea"></textarea>
                <div class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span> Здесь можно использовать HTML код.</div>
                <hr/>
                <div class="center">
                    <div class="btn-group">
                        <button class="btn btn-default" type="submit" name="staticc-page-create-create-btn"><span class="glyphicon glyphicon-ok"></span> Опубликовать страницу</button>
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
    function showDiv(element){
        $("#staticc-btn-panel > *").removeClass("active");
        element.addClass("active");
        $("#staticc-panel > *").hide();
        $("#" + element.data("div")).show();
    }

    <?php if (!empty($_REQUEST["reqtype"]) && $_REQUEST["reqtype"] == 1){ ?>

    showDiv($('#staticc-page-create-btn'));
    <?php } ?>

    function insertBBCode(openTag, notNeedClose){
        var texter = document.getElementById("staticc-page-create-textarea");
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

    $('#bb_b').click(function(){
        insertBBCode('b', false);
    });
    $('#bb_s').click(function(){
        insertBBCode('s', false);
    });
    $('#bb_u').click(function(){
        insertBBCode('u', false);
    });
    $('#bb_i').click(function(){
        insertBBCode('i', false);
    });
    $('#bb_hr').click(function(){
        insertBBCode('hr', null);
    });
    $('#bb_ol').click(function(){
        insertBBCode('ol', false);
    });
    $('#bb_item').click(function(){
        insertBBCode('*', null);
    });
    $('#bb_a').click(function(){
        insertBBCode('link', false);
    });
    $('#bb_img').click(function(){
        insertBBCode('img=', null);
    });
    $('#bb_youtube').click(function(){
        insertBBCode('youtube=', null);
    });
    $('#bb_size').on("change",function(){
        insertBBCode('size='+this.options[this.selectedIndex].value, 'size');
    });
    $('#bb_color').on("change",function(){
        insertBBCode('color='+this.options[this.selectedIndex].value, 'color');
    });
</script>

