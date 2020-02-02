<?php
if (!defined("TT_AP")){ header("Location: ../adminapanel.php?p=forbidden"); exit; }

if (!$user->UserGroup()->getPermission("upload_see_all")) { header("Location: ../adminpanel.php?res=1"); exit; }
if ($user->UserGroup()->getPermission("upload_delete")) $fileDeleteOwn = true; else $fileDeleteOwn = false;
if ($user->UserGroup()->getPermission("upload_delete_foreign")) $fileDeleteForeign = true; else $fileDeleteForeign = false;
if (isset($_GET["filter"])){
    if ($_GET["filter"] == "author"){
        $filter = "author";
        $placeholderInput = \Engine\LanguageManager::GetTranslation("uploaded_panel.js_search_files_by_author");
        $fileList = \Engine\Uploader::GetUploadedFilesListByAuthor(@$_GET["sf"], (isset($_GET["page"]) ? $_GET["page"] : 1));
    }
    if ($_GET["filter"] == "ref"){
        $filter = "ref";
        $placeholderInput = \Engine\LanguageManager::GetTranslation("uploaded_panel.js_search_files_by_ref");
        $fileList = \Engine\Uploader::GetUploadedFilesListByReference(@$_GET["sf"], (isset($_GET["page"]) ? $_GET["page"] : 1));
    }
} else {
    $fileList = \Engine\Uploader::GetUploadedFilesList((isset($_GET["page"]) ? $_GET["page"] : 1));
    $placeholderInput = \Engine\LanguageManager::GetTranslation("uploaded_panel.js_search_files_by_author");
}

?>

<div class="inner cover">
    <h1 class="cover-heading"><span class="glyphicons glyphicons-file-cloud-upload"></span> <?=\Engine\LanguageManager::GetTranslation("uploaded_panel.panel_name")?></h1>
    <p class="lead"><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.panel_description")?></p>
    <form action="adminpanel/scripts/uploadmanager.php" method="post" name="file-form" id="file-form">
        <div class="div-border">
            <input type="hidden" value="<?php if ($filter == null || $filter == "author") echo "nickname"; if (@$filter == "ref") echo "ref";?>" name="search-type" id="search-type">
            <p><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.panel_tip")?></p>
            <?php if ($filter != null){ ?>
                <hr>
                <div class="alert alert-info">
                    <span class="glyphicons glyphicons-filter"></span> <strong><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.applied_filter")?></strong>
                    <?php if ($filter == "author") {
                      echo \Engine\LanguageManager::GetTranslation("uploaded_panel.search_by_author");
                     }
                     if ($filter == "ref") {
                         echo \Engine\LanguageManager::GetTranslation("uploaded_panel.search_by_ref");
                     } ?>
                </div>
            <?php } ?>
            <div class="input-group">
                <input class="form-control" type="text" name="uploadedlist-search-input" value="<?=@$_GET["sf"]?>" placeholder="<?=$placeholderInput?>" id="uploadedlist-search-input">
                <div class="input-group-btn" id="filter-btn-group">
                    <button class="btn btn-default <?php if ($filter == null || $filter == "author") echo "active";?>" type="button" id="author-searching-btn" title="<?=\Engine\LanguageManager::GetTranslation("uploaded_panel.search_by_author_title")?>"><span class="glyphicons glyphicons-user"></span></button>
                    <button class="btn btn-default <?php if ($filter == "ref") echo "active";?>" type="button" id="ref-searching-btn" title="<?=\Engine\LanguageManager::GetTranslation("uploaded_panel.search_by_ref_title")?>"><span class="glyphicons glyphicons-file"></span></button>
                </div>
            </div>
            <br>
            <div class="btn-group">
                <button class="btn btn-default" type="submit" name="search-btn"><span class="glyphicons glyphicons-search"></span> <?=\Engine\LanguageManager::GetTranslation("uploaded_panel.searching_btn")?></button>
                <button class="btn btn-danger" type="button" name="delete-btn" id="delete-btn" disabled><span class="glyphicons glyphicons-delete"></span> <?=\Engine\LanguageManager::GetTranslation("uploaded_panel.delete_selected")?></button>
                <?php if (isset($_GET["filter"])){?><a class="btn btn-default" href="adminpanel.php?p=uploadedlist"><span class="glyphicons glyphicons-filter-remove"></span> <?=\Engine\LanguageManager::GetTranslation("uploaded_panel.reset_filter")?></a><?php } ?>
            </div>
            <hr>
            <div class="alert alert-info"><span class="glyphicons glyphicons-info-sign"></span>
                <?=\Engine\LanguageManager::GetTranslation("uploaded_panel.table_page_tip")?>
            </div>
            <h2><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.uploaded_files")?></h2>
            <div class="alert alert-info" id="selected-files-count" hidden>
                <strong><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.selected_files_count")?></strong> <span id="selected-files-count-span"></span>
            </div>
            <table class="table">
                <thead style="background: radial-gradient(at right, #151333, #1ba397); color: white;">
                    <tr>
                        <td><input type="checkbox" id="upload-select-all" name="allselectorcheck" title="<?=\Engine\LanguageManager::GetTranslation("uploaded_panel.select_all")?>"></td>
                        <td>ID</td>
                        <td><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.file_name_table")?></td>
                        <td><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.date_uploading_table")?></td>
                        <td><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.file_path_table")?></td>
                        <td><?=\Engine\LanguageManager::GetTranslation("uploaded_panel.author_table")?></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($fileList) == 0){ ?>
                        <tr>
                            <td colspan="7" style="text-align: center"><span class="glyphicons glyphicons-info-sign"></span> <?=\Engine\LanguageManager::GetTranslation("uploaded_panel.no_uploaded_files")?></td>
                        </tr>
                    <?php } else {
                        for($i = 0; $i < count($fileList); $i++){ ?>
                        <tr>
                            <td><input type="checkbox" data-fid-selected="<?=$fileList[$i]["id"]?>"</td>
                            <td><?=$fileList[$i]["id"]?></td>
                            <td><?=$fileList[$i]["name"]?></td>
                            <td><?=\Engine\Engine::DateFormatToRead($fileList[$i]["upload_date"])?></td>
                            <td><?=$fileList[$i]["file_path"]?></td>
                            <td><?=\Users\UserAgent::GetUserNick($fileList[$i]["author"])?></td>
                            <td><?php if (($fileList[$i]["author"] == $user->getId() && $fileDeleteOwn) || $fileDeleteForeign) { ?>
                                <button style="width: 100%" class="btn btn-danger" name="file-delete-btn" type="submit" formaction="adminpanel/scripts/uploadmanager.php?fidd=<?=$fileList[$i]["id"]?>">
                                    <span class="glyphicons glyphicons-delete"></span> <?=\Engine\LanguageManager::GetTranslation("uploaded_panel.delete_file_btn")?>
                                </button><?php } ?>
                            </td>
                        </tr>
                        <?php }
                    }?>
                </tbody>
            </table>
        </div>
    </form>
</div>
<script type="text/javascript">
    $("#filter-btn-group > button").click(function() {
        $("#filter-btn-group > button").removeClass("active");
        $(this).addClass("active");
    });
    $("#author-searching-btn").click(function() {
        $("#uploadedlist-search-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("uploaded_panel.js_search_files_by_author")?>");
        $("#search-type").val("nickname");
    });
    $("#ref-searching-btn").click(function () {
        $("#uploadedlist-search-input").attr("placeholder", "<?=\Engine\LanguageManager::GetTranslation("uploaded_panel.js_search_files_by_ref")?>");
        $("#search-type").val("ref");
    });
    $("#upload-select-all").on("change", function(){
        var checkboxes = $(this).closest('table').find(':checkbox');
        if ($(this).is(":checked")) {
            checkboxes.prop("checked", true);
        } else {
            checkboxes.prop("checked", false);
        }
    });
    $("input:checkbox").change(function(){
        var counter = 0;
        if ($(this).attr("data-fid-selected") != undefined){
            $("#delete-btn").attr("disabled", false);
        } else {
            $("#delete-btn").attr("disabled", true);
        }
        if ($("#upload-select-all").is(":checked")) counter = counter-1;
        $("input:checkbox:checked").each(function(){
            counter = counter+1;
        });
        if (counter > 0){
            $("#delete-btn").prop("disabled", false);
            $("#selected-files-count").show();
            $("#selected-files-count-span").html(counter);
        }
        else {
            $("#delete-btn").prop("disabled", true);
            $("#selected-files-count").hide();
        }
    });
    $("#delete-btn").on("click", function() {
        var formActionLink = "adminpanel/scripts/uploadmanager.php?fdfi=";
        var comma = "";
        $("input:checkbox:checked").each(function () {
            if ($( this ).attr("data-fid-selected") != undefined){
                if (formActionLink.charAt(formActionLink.length-1) != "=") comma = ",";
                formActionLink = formActionLink + comma + $( this ).attr("data-fid-selected");
            }
        });
        $( "#delete-btn" ).attr("formaction", formActionLink);
        $( "#delete-btn").attr("type", "submit");
        $( "#delete-btn").click();
    });
</script>
