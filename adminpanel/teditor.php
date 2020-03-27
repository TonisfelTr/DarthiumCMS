<?php
if (!defined("TT_AP")){ header("Location: ../adminpanel.php?p=forbidden"); exit; }
if (!$user->UserGroup()->getPermission("change_template_design")){
    header("Location: ../adminpanel.php?res=1");
    exit;
}
$templates = scandir("site/templates");
$templatesForSelect = [];
for ($i = 0; $i < count($templates); $i++)
    if ($templates[$i] != "." && $templates[$i] != "..") {
        array_push($templatesForSelect, $templates[$i]);
    }

?>
<h1 class="cover-heading"><?=\Engine\LanguageManager::GetTranslation("adminpanel.site_design")?></h1>
<p class="lead"><?=\Engine\LanguageManager::GetTranslation("adminpanel.site_design_description")?></p>
<form method="post" action="adminpanel/scripts/design.php">
    <div class="custom-group">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
            <label for="templates_select">Выберите шаблон:</label>
            <select id="templates_select" name="templates_select" class="form-control">
                <?php for ($i = 0; $i < count($templatesForSelect); $i++){
                    echo "<option". ((\Engine\Engine::GetEngineInfo("stp") == $templatesForSelect[$i]) ? " selected" : "") . ">$templatesForSelect[$i]</option>";
                }?>
            </select>
            <br>
            <select class="form-control" id="template_files_edit_selector" size="20">
                <?php
                $filesIn = scandir("site/templates/" . \Engine\Engine::GetEngineInfo("stp"));
                $fileForEnum = [];
                for ($i = 0; $i < count($filesIn); $i++){
                    if ($filesIn[$i] != "." && $filesIn[$i] != ".."){
                        if (is_dir("site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/" . $filesIn[$i])){
                            echo "<option style='color: #fba33a'> $filesIn[$i]</option>";
                        }
                        else {
                            if (strstr($filesIn[$i], "html"))
                                echo "<option style='color: #2c832e'> $filesIn[$i]</option>";
                            if (strstr($filesIn[$i], "css"))
                                echo "<option style='color: #4b65ff'> $filesIn[$i]</option>";
                            if (strstr($filesIn[$i], "js"))
                                echo "<option style='color: #834624'> $filesIn[$i]</option>";
                            if (strstr($filesIn[$i], "phtml"))
                                echo "<option style='color: #40a1a1'> $filesIn[$i]</option>";
                            if (strstr($filesIn[$i], "png") || strstr($filesIn[$i], "ico")
                            || strstr($filesIn[$i], "jpeg" || strstr($filesIn[$i], "jpg")))
                                echo "<option style='color: #833180'> $filesIn[$i]</option>";
                        }
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-9">
            <textarea class="form-control template-editor" id="template_file_editor" name="template_file_editor"></textarea>
        </div>
        <div class="btn-group" style="margin: 14px;">
            <button class="btn btn-default" type="submit" name="save_template_file_btn"><span class="glyphicon glyphicon-save-file"></span> <?=\Engine\LanguageManager::GetTranslation("save")?></button>
            <a class="btn btn-default" href="adminpanel.php"><span class="glyphicon glyphicon glyphicon-chevron-left"></span> <?=\Engine\LanguageManager::GetTranslation("cancel")?></a>
        </div>
    </div>
    <input type="hidden" id="template_path" name="template_path">
    <input type="hidden" id="template_file_name" name="template_file_name">
</form>
<script>
    var codeMirror = CodeMirror.fromTextArea(document.getElementById("template_file_editor"), {
        lineNumbers: true,
        matchBrackets: true,
        mode: 'text/html',
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: "keep",
        tabMode: "shift"
    });

    $("#templates_select").on("change", function(){
        $("#template_files_edit_selector").children().remove();
        $("#template_path").val("");
        $.ajax({
            url: "adminpanel/scripts/ajax/filemanagerajax.php",
            type: "POST",
            data: "get_content&template_name=" + $("#templates_select").text().trim() + "&enddir=" + $("#template_path").val() + "/" + $("#template_files_edit_selector").children("option:selected").val(),
            success: function (data) {
                var result = $.parseJSON(data);
                if (result === false)
                    return;
                result = result.toString().split(',');
                $("#template_path").val($("#template_files_edit_selector").children("option:selected").val());
                $("#template_files_edit_selector").children().remove();
                for (i = 1; i < result.length; i++) {
                    var option = document.createElement("option");
                    if ($("#template_path").val() == "..") {
                        $("#template_path").val("");
                        continue;
                    }
                    if (result[i].indexOf(".") > 0) {
                        if (result[i].indexOf("css") > 0)
                            $(option).css("color", "#4b65ff");
                        if (result[i].indexOf("html") > 0)
                            $(option).css("color", "#2c832e");
                        if (result[i].indexOf("js") > 0)
                            $(option).css("color", "#834624");
                        if (result[i].indexOf("phtml") > 0)
                            $(option).css("color", "#40a1a1");
                        if (result[i].indexOf("png") > 0 || result[i].indexOf("ico") > 0 || result[i].indexOf("jpg") > 0 || result[i].indexOf("jpeg") > 0)
                            $(option).css("color", "#833180");
                    } else {
                        $(option).css("color", "#fba33a");
                    }
                    $(option).text(result[i]);
                    $("#template_files_edit_selector").append($(option));
                }
            }
        });
    });
    $("#template_files_edit_selector").dblclick(function() {
        if ($("#template_files_edit_selector").children("option:selected").val().indexOf(".") < 0 ||
            $("#template_files_edit_selector").children("option:selected").val() == "..") {
            $.ajax({
                url: "adminpanel/scripts/ajax/filemanagerajax.php",
                type: "POST",
                data: "get_content&template_name=" + $("#templates_select").text().trim() + "&enddir=" + $("#template_path").val() + "/" + $("#template_files_edit_selector").children("option:selected").val(),
                success: function (data) {
                    var result = $.parseJSON(data);
                    if (result === false)
                        return;
                    result = result.toString().split(',');
                    $("#template_path").val($("#template_files_edit_selector").children("option:selected").val());
                    $("#template_files_edit_selector").children().remove();
                    for (i = 1; i < result.length; i++) {
                        var option = document.createElement("option");
                        if ($("#template_path").val() == "..") {
                            $("#template_path").val("");
                            continue;
                        }
                        if (result[i].indexOf(".") > 0) {
                            if (result[i].indexOf("css") > 0)
                                $(option).css("color", "#4b65ff");
                            if (result[i].indexOf("html") > 0)
                                $(option).css("color", "#2c832e");
                            if (result[i].indexOf("js") > 0)
                                $(option).css("color", "#834624");
                            if (result[i].indexOf("phtml") > 0)
                                $(option).css("color", "#40a1a1");
                            if (result[i].indexOf("png") > 0 || result[i].indexOf("ico") > 0 || result[i].indexOf("jpg") > 0 || result[i].indexOf("jpeg") > 0)
                                $(option).css("color", "#833180");
                        } else {
                            $(option).css("color", "#fba33a");
                        }
                        $(option).text(result[i]);
                        $("#template_files_edit_selector").append($(option));
                    }
                }
            });
        } else {
            $.ajax({
                url: "adminpanel/scripts/ajax/filemanagerajax.php",
                type: "POST",
                data: "get_file_content&template_name=" + $("#templates_select").text().trim() + "&filename=" + $("#template_path").val() + "/" + $("#template_files_edit_selector").children("option:selected").val(),
                success: function (data){
                    var currentFile = $("#template_files_edit_selector").children("option:selected").val();
                    if (currentFile.split('.').indexOf("png") == -1 &&
                        currentFile.split('.').indexOf("ico") == -1 &&
                        currentFile.split('.').indexOf("jpeg") == -1 &&
                        currentFile.split('.').indexOf("jpg") == -1) {
                        $("#template_file_name").val(currentFile);
                        if (currentFile.split(".")[1] == "css")
                            codeMirror.setOption("mode", "text/css");
                        if (currentFile.split(".")[1] == "html")
                            codeMirror.setOption("mode", "text/html");
                        if (currentFile.split(".")[1] == "js")
                            codeMirror.setOption("mode", "text/javascript");
                        if (currentFile.split(".")[1] == "phtml")
                            codeMirror.setOption("mode", "text/php");
                        codeMirror.setValue(data);
                    }
                }
            })
        }
    });

</script>