<?php
if (!defined("TT_AP")){ header("Location: ../adminapanel.php?p=forbidden"); exit; }
//Проверка на наличие прав.
if (!$user->UserGroup()->getPermission("category_create") &&
    !$user->UserGroup()->getPermission("category_edit") &&
    !$user->UserGroup()->getPermission("category_delete")){ header("Location: ../../adminpanel.php?res=1"); exit; }
else {
    $canCategoryCreate = $user->UserGroup()->getPermission("category_create");
    $canCategoryEdit = $user->UserGroup()->getPermission("category_edit");
    $canCategoryDelete = $user->UserGroup()->getPermission("category_delete");

    $categoryList = Forum\ForumAgent::GetCategoryList(false);
    $categoryCount = count($categoryList);

    if (!empty($_GET["reqtype"]) && $_GET["reqtype"] == 2){
        if (empty($_GET["cid"])){
            header("Location: ../adminpanel.php?p=categories&res=6ncid");
            exit;
        }

        $category = new \Forum\Category($_GET["cid"]);
        if ($category === 32){
            header("Location: ../adminpanel.php?p=categories&res=6ntc");
            exit;
        }
    }
    ?>
<div class="inner cover">
    <h1 class="cover-heading">Категории</h1>
    <p class="lead">Управление категориями сайта: их создание, удаление и манипуляции.</p>
    <div id="btn-show-panel" class="btn-group">
        <button id="btn-show-table" class="btn btn-default <?php echo (empty($_GET["reqtype"])) ? "active" : ""; ?>" onclick="showToBelow('category_table_div', 'btn-show-table');"><span class="glyphicons glyphicons-show-thumbnails"></span> Управление категориями</button>
        <?php if ($canCategoryCreate) { ?>
        <button id="btn-show-add" class="btn btn-default <?php echo (!empty($_GET["reqtype"]) && $_GET["reqtype"] == 1) ? "active" : ""; ?>" onclick="showToBelow('category_add_div', 'btn-show-add');"><span class="glyphicons glyphicons-folder-plus"></span> Добавление категорий</button>
        <?php } if ($canCategoryEdit){
        if (isset($_GET["reqtype"]) && $_GET["reqtype"] == 2){ ?>
        <button id="btn-show-edit" class="btn btn-info <?php echo (!empty($_GET["reqtype"]) && $_GET["reqtype"] == 2) ? "active" : ""; ?>" onclick="showToBelow('category_edit_div', 'btn-show-edit');"><span class="glyphicons glyphicons-folder-new"></span> <?php echo $category->getName(); ?> - Редактирование категории</button> <?php } } ?>
    </div>
    <form method="post" action="adminpanel/scripts/categories.php" enctype="multipart/form-data">
        <div class="custom-group">
            <div class="div-border" id="category_table_div" <?php if (!empty($_GET["reqtype"])) echo "hidden"; ?>>
                <h3>Управление категориями</h3>
                <p class="helper">Просмотр существующих категорий с возможностью редактирования.</p>
                <hr>
                <p>Здесь Вы можете найти уже созданные категории и информацию по ним. Также, функционал позволяет изменять
                категории, их параметры и удалять их совсем.</p>
                <?php if ($canCategoryDelete) { ?>
                <button class="btn btn-danger" name="categories-table-delete" id="categories-table-delete" style="width: 100%;" disabled><span class="glyphicons glyphicons-erase"></span> Удалить категории</button>
                <?php } ?>
                <hr>
                <div class="alert alert-info" id="categories-table-count-div" hidden>
                    <span class="glyphicon glyphicon-info-sign"></span> Выделено категорий:
                    <span id="categories-table-count-span"></span>
                </div>
                <h3>Список категорий</h3>
                <table class="table">
                    <thead style="background: radial-gradient(at right, #11ee11, #0b401c); color: white; text-shadow: 1px 1px 3px black;">
                        <tr>
                            <td><input type="checkbox" id="categories-all-selector"></td>
                            <td>ID</td>
                            <td>Название</td>
                            <td>Описание</td>
                            <td>Публичность</td>
                            <td>Без коментариев</td>
                            <td>Без тем</td>
                            <td>Кол-во тем</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($categoryCount == 0) { ?>
                            <tr><td class="alert-info" colspan="9" style="text-align: center;"><span class="glyphicon glyphicon-info-sign"></span> Не создано ни одной категории.</td></tr>
                        <?php } else {
                                for ($i = 0; $i <= $categoryCount-1; $i++){
                                    $category = new \Forum\Category($categoryList[$i]); ?>
                                <tr>
                                    <td><input type="checkbox" data-cid-selected="<?php echo $categoryList[$i]; ?>"></td>
                                    <td><?php echo $categoryList[$i]; ?></td>
                                    <td><?php echo $category->getName(); ?></td>
                                    <td><?php echo $category->getDescription(); ?></td>
                                    <td><?php echo \Engine\Engine::BooleanToWords($category->isPublic()); ?></td>
                                    <td><?php echo \Engine\Engine::BooleanToWords($category->CanCreateComments()); ?></td>
                                    <td><?php echo \Engine\Engine::BooleanToWords($category->CanCreateTopic()); ?></td>
                                    <td><?php echo $category->getTopicsCount(); ?></td>
                                    <td><button type="submit" class="btn btn-default" name="category_edit_btn" formaction="adminpanel/scripts/categories.php?cid=<?php echo $category->getId(); ?>" style="width:100%;">Редактировать</button></td>
                                </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
            <?php if ($canCategoryCreate) { ?>
            <div class="div-border" id="category_add_div" <?php if (!isset($_GET["reqtype"]) || $_GET["reqtype"] != 1) echo "hidden"; ?>>
                <h3>Добавление категорий</h3>
                <p class="h-helper">Добавление категорий для тем.</p>
                <hr>
                <p>Здесь Вы можете создать новую категорию и сразу, здесь же, настроить для неё параметры. Название и описание категории
                может иметь любые символы, однако, не поддерживает BB-code или HTML.</p>
                <input class="form-control" type="text" maxlength="50" name="category-add-name" placeholder="Введите имя новой категории.">
                <textarea class="form-control" maxlength="350" name="category-add-description" style="resize: vertical; max-height: 350px;" placeholder="Введите описание категории. Здесь Вы можете пояснить, какие именно темы должны создаваться."></textarea>
                <br>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Длина названия категории и её описания не может быть короче 4 символов и длиннее 50 и 350 символов соответственно.</div>
                <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> Заполнение данных полей обязательно.</div>
                <hr>
                <h4>Дополнительные параметры</h4>
                <p>Вы можете управлять правилами категории, путём постановки определённых параметров.</p>
                <label for="category_public_checker">
                    <input type="checkbox" id="category_public_checker" name="category_add_public" title="Если эта категория не публичная, то взаимодействовать с ней смогут лишь те, у кого есть специфические права доступа." checked>
                    Публичная категория
                </label><br>
                <label for="category_nocomments_checker">
                    <input type="checkbox" id="category_nocomments_checker" name="category_add_nocomments" title="Если включено, то комментарии в темах этой категории смогут оставлять лишь те, у кого есть специфические права доступа.">
                    Запретить оставлять комментарии
                </label><br>
                <label for="category_notopics_checker">
                    <input type="checkbox" id="category_notopics_checker" name="category_add_notopics" title="Если включено, то создавать темы в этой категории смогут лишь те, у кого есть специфические права доступа. Не распространяется на перенос тем из одной категории в другую.">
                    Запретить создавать темы
                </label>
                <hr>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" name="category-add-btn"><span class="glyphicons glyphicons-folder-plus"></span> Создать категорию</button>
                    <button class="btn btn-default" type="reset" name="category-add-formreset-btn"><span class="glyphicons glyphicons-erase"></span> Отчистить форму</button>
                </div>
            </div>
            <?php }
            if ($canCategoryEdit){
            if (!empty($_GET["reqtype"]) && $_GET["reqtype"] == 2){
                $category = new \Forum\Category($_GET["cid"]); ?>
            <div class="div-border" id="category_edit_div">
                <h3><?php echo $category->getName(); ?></h3>
                <p class="h-helper">Редактирование параметров и идентификаторов категории.</p>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon">ID категории:</div>
                    <div class="form-control alert-info"><?php echo $category->getId(); ?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Кол-во тем в категории:</div>
                    <div class="form-control alert-info"><?php echo $category->getTopicsCount(); ?></div>
                </div>
                <hr>
                <p class="h-helper">Редактирование итендификационной информации.</p>
                <div class="input-group">
                    <div class="input-group-addon">Название категории:</div>
                    <input class="form-control" type="text" maxlength="50" value="<?php echo $category->getName();?>" name="category_edit_name">
                </div>
                <div class="input-group">
                    <div class="input-group-addon">Описание категории:</div>
                    <textarea class="form-control" maxlength="350" style="resize: vertical; max-height: 350px;" name="category_edit_descript"><?php echo $category->getDescription(); ?></textarea>
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> Длина названия категории и её описания не может быть короче 4 символов и длиннее 50 и 350 символов соответственно.</div>
                <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> Название и описание категории не может быть пустым.</div>
                <hr>
                <p class="h-helper">Редактирование параметров.</p>
                <label class="label" for="category_edit_public_checker">
                    <input type="checkbox" name="category_edit_public_checker" id="category_edit_public_checker" <?php if ($category->isPublic()) echo "checked";?> title="Если эта категория не публичная, то взаимодействовать с ней смогут лишь те, у кого есть специфические права доступа."> Публичная категория
                </label><br>
                <label class="label" for="category_edit_nocomments_checker">
                    <input type="checkbox" name="category_edit_nocomments_checker" id="category_edit_nocomments_checker" <?php if ($category->CanCreateComments()) echo "checked";?> title="Если включено, то комментарии в темах этой категории смогут оставлять лишь те, у кого есть специфические права доступа."> Запретить оставлять комментарии
                </label><br>
                <label class="label" for="category_edit_notopics_checker">
                    <input type="checkbox" name="category_edit_notopics_checker" id="category_edit_notopics_checker" <?php if ($category->CanCreateTopic()) echo "checked";?> title="Если включено, то создавать темы в этой категории смогут лишь те, у кого есть специфические права доступа. Не распространяется на перенос тем из одной категории в другую."> Запретить создавать новые темы
                </label><br>
                <hr>
                <div class="btn-group">
                    <button class="btn btn-default" name="category_edit_save" type="submit" formaction="adminpanel/scripts/categories.php?cid=<?php echo $category->getId();?>"><span class="glyphicons glyphicons-edit"></span> Сохранить изменения</button>
                    <button class="btn btn-default" name="category_edit_reset" type="reset"><span class="glyphicons glyphicons-unchecked"></span> Отменить изменения</button>
                    <?php if ($canCategoryDelete) { ?>
                    <button class="btn btn-danger" name="category_edit_dalete" type="submit" formaction="adminpanel/scripts/categories.php?cid=<?php echo $category->getId();?>"><span class="glyphicons glyphicons-folder-minus"></span> Удалить категорию</button><?php } ?>
                </div>
            </div>
            <?php } } ?>
        </div>
    </form>
</div>
<script type="text/javascript">
    var settingDivs = [];
    settingDivs[0] = document.getElementById("category_table_div");
    settingDivs[1] = document.getElementById("category_add_div");
    settingDivs[2] = document.getElementById("category_edit_div");

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

    $('#categories-all-selector').change(function() {
        var checkboxes = $(this).closest('table').find(':checkbox');
        if($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });

    $("td input:checkbox").change(function(){
        var counter = 0;
        if ($("#categories-all-selector").is(":checked")) counter = counter-1;
        $("td input:checkbox:checked").each(function(){
            counter = counter+1;
        });
        if (counter > 0){
            $("#categories-table-delete").prop("disabled", false);
            $("#categories-table-count-div").show();
            $("#categories-table-count-span").html(counter);
        }
        else {
            $("#categories-table-delete").prop("disabled", true);
            $("#categories-table-count-div").hide();
        }
    });

    $("#categories-table-delete").on("click", function() {
        var formActionLink = "adminpanel/scripts/categories.php?cid=";
        var comma = "";
        $("input:checkbox:checked").each(function () {
            if ($( this ).attr("data-cid-selected") != undefined){
                if (formActionLink.charAt(formActionLink.length-1) != "=") comma = ",";
                formActionLink = formActionLink + comma + $( this ).attr("data-uid-selected");
            }
        });
        $( "#categories-table-delete" ).attr("formaction", formActionLink);
        $( "#categories-table-delete").attr("type", "submit");
        $( "#categories-table-delete").click();
    });

</script>
<?php } ?>

