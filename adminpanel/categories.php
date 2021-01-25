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
    <h1 class="cover-heading"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.page_name"); ?></h1>
    <p class="lead"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.page_description"); ?></p>
    <div id="btn-show-panel" class="btn-group">
        <button id="btn-show-table" class="btn btn-default <?php echo (empty($_GET["reqtype"])) ? "active" : ""; ?>" onclick="showToBelow('category_table_div', 'btn-show-table');"><span class="glyphicons glyphicons-show-thumbnails"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.categories_managment"); ?></button>
        <?php if ($canCategoryCreate) { ?>
        <button id="btn-show-add" class="btn btn-default <?php echo (!empty($_GET["reqtype"]) && $_GET["reqtype"] == 1) ? "active" : ""; ?>" onclick="showToBelow('category_add_div', 'btn-show-add');"><span class="glyphicons glyphicons-folder-plus"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.category_add"); ?></button>
        <?php } if ($canCategoryEdit){
        if (isset($_GET["reqtype"]) && $_GET["reqtype"] == 2){ ?>
        <button id="btn-show-edit" class="btn btn-info <?php echo (!empty($_GET["reqtype"]) && $_GET["reqtype"] == 2) ? "active" : ""; ?>" onclick="showToBelow('category_edit_div', 'btn-show-edit');"><span class="glyphicons glyphicons-folder-new"></span> <?php echo $category->getName(); ?> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.edit_btn"); ?></button> <?php } } ?>
    </div>
    <form method="post" action="adminpanel/scripts/categories.php" enctype="multipart/form-data">
        <div class="custom-group">
            <div class="div-border" id="category_table_div" <?php if (!empty($_GET["reqtype"])) echo "hidden"; ?>>
                <h3><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.categories_managment"); ?></h3>
                <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.categories_managment_tip"); ?></p>
                <hr>
                <p><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.panel_tip"); ?></p>
                <?php if ($canCategoryDelete) { ?>
                <button class="btn btn-danger" name="categories-table-delete" id="categories-table-delete" style="width: 100%;" disabled><span class="glyphicons glyphicons-erase"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.remove_categories_btn"); ?></button>
                <?php } ?>
                <hr>
                <div class="alert alert-info" id="categories-table-count-div" hidden>
                    <span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.selected_count"); ?>
                    <span id="categories-table-count-span"></span>
                </div>
                <h3><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.categories_list"); ?></h3>
                <div class="table-responsive">
                <table class="table">
                    <thead style="background: radial-gradient(at right, #11ee11, #0b401c); color: white; text-shadow: 1px 1px 3px black;">
                        <tr>
                            <td><input type="checkbox" id="categories-all-selector"></td>
                            <td>ID</td>
                            <td><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.category_name"); ?></td>
                            <td><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.category_description"); ?></td>
                            <td><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.category_public"); ?></td>
                            <td><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.category_no_comment"); ?></td>
                            <td><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.category_no_topics"); ?></td>
                            <td><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.topics_count"); ?></td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($categoryCount == 0) { ?>
                            <tr><td class="alert-info" colspan="9" style="text-align: center;"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_managment.no_categories"); ?></td></tr>
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
                                    <td><button type="submit" class="btn btn-default" name="category_edit_btn" formmethod="post" formaction="adminpanel/scripts/categories.php?cid=<?php echo $category->getId(); ?>" style="width:100%;"><?php echo \Engine\LanguageManager::GetTranslation("edit"); ?></button></td>
                                </tr>
                        <?php } } ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?php if ($canCategoryCreate) { ?>
            <div class="div-border" id="category_add_div" <?php if (!isset($_GET["reqtype"]) || $_GET["reqtype"] != 1) echo "hidden"; ?>>
                <h3><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.category_add"); ?></h3>
                <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.category_add_tip"); ?></p>
                <hr>
                <p><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.panel_tip"); ?></p>
                <input class="form-control" type="text" maxlength="50" name="category-add-name" placeholder="<?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.placeholder_new_category_name"); ?>">
                <input class="form-control" type="text" maxlength="255" name="category_add_keywords" placeholder="<?=\Engine\LanguageManager::GetTranslation("categories_panel.category_edit.category_keywords")?>">
                <textarea class="form-control" maxlength="350" name="category-add-description" style="resize: vertical; max-height: 350px;" placeholder="<?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.placeholder_new_category_description"); ?>"></textarea>
                <br>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.data_tip"); ?></div>
                <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.warning"); ?></div>
                <hr>
                <h4><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.additive_params"); ?></h4>
                <p><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.additive_params_tip"); ?></p>
                <label for="category_public_checker">
                    <input type="checkbox" id="category_public_checker" name="category_add_public" title="<?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.public_tip"); ?>" checked>
                    Публичная категория
                </label><br>
                <label for="category_nocomments_checker">
                    <input type="checkbox" id="category_nocomments_checker" name="category_add_nocomments" title="<?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.comments_tip"); ?>">
                    Запретить оставлять комментарии
                </label><br>
                <label for="category_notopics_checker">
                    <input type="checkbox" id="category_notopics_checker" name="category_add_notopics" title="<?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.topics_tip"); ?>">
                    Запретить создавать темы
                </label>
                <hr>
                <div class="btn-group">
                    <button class="btn btn-default" type="submit" name="category-add-btn"><span class="glyphicons glyphicons-folder-plus"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.create_category_btn"); ?></button>
                    <button class="btn btn-default" type="reset" name="category-add-formreset-btn"><span class="glyphicons glyphicons-erase"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_add.clear_form_btn"); ?></button>
                </div>
            </div>
            <?php }
            if ($canCategoryEdit){
            if (!empty($_GET["reqtype"]) && $_GET["reqtype"] == 2){
                $category = new \Forum\Category($_GET["cid"]); ?>
            <div class="div-border" id="category_edit_div">
                <h3><?php echo $category->getName(); ?></h3>
                <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.page_tip"); ?></p>
                <hr>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.id_category"); ?></div>
                    <div class="form-control alert-info"><?php echo $category->getId(); ?></div>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.count_topics_in"); ?></div>
                    <div class="form-control alert-info"><?php echo $category->getTopicsCount(); ?></div>
                </div>
                <hr>
                <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.itendefication_info"); ?>.</p>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.category_name"); ?></div>
                    <input class="form-control" type="text" maxlength="50" value="<?php echo $category->getName();?>" name="category_edit_name">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?=\Engine\LanguageManager::GetTranslation("categories_panel.category_edit.category_keywords")?></div>
                    <input class="form-control" type="text" maxlength="255" name="category_edit_keywords" value="<?=$category->getKeyWords();?>">
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.category_description"); ?></div>
                    <textarea class="form-control" maxlength="350" style="resize: vertical; max-height: 350px;" name="category_edit_descript"><?php echo $category->getDescription(); ?></textarea>
                </div>
                <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.name_and_description_tip"); ?></div>
                <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.warning"); ?></div>
                <hr>
                <p class="h-helper"><?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.edit_params"); ?></p>
                <label class="label" for="category_edit_public_checker">
                    <input type="checkbox" name="category_edit_public_checker" id="category_edit_public_checker" <?php if ($category->isPublic()) echo "checked";?> title="<?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.public_tip"); ?>"> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.public_label"); ?>
                </label><br>
                <label class="label" for="category_edit_nocomments_checker">
                    <input type="checkbox" name="category_edit_nocomments_checker" id="category_edit_nocomments_checker" <?php if ($category->CanCreateComments()) echo "checked";?> title="<?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.comments_tip"); ?>"> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.comments_label"); ?>
                </label><br>
                <label class="label" for="category_edit_notopics_checker">
                    <input type="checkbox" name="category_edit_notopics_checker" id="category_edit_notopics_checker" <?php if ($category->CanCreateTopic()) echo "checked";?> title="<?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.topics_tip"); ?>"> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.topics_label"); ?>
                </label><br>
                <hr>
                <div class="btn-group">
                    <button class="btn btn-default" name="category_edit_save" type="submit" formaction="adminpanel/scripts/categories.php?cid=<?php echo $category->getId();?>"><span class="glyphicons glyphicons-edit"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.save_category_btn"); ?></button>
                    <button class="btn btn-default" name="category_edit_reset" type="reset"><span class="glyphicons glyphicons-unchecked"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.clear_form_btn"); ?></button>
                    <?php if ($canCategoryDelete) { ?>
                    <button class="btn btn-danger" name="category_edit_dalete" type="submit" formaction="adminpanel/scripts/categories.php?cid=<?php echo $category->getId();?>"><span class="glyphicons glyphicons-folder-minus"></span> <?php echo \Engine\LanguageManager::GetTranslation("categories_panel.category_edit.category_remove_btn"); ?></button><?php } ?>
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

