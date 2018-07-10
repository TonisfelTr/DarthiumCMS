<?php
if (!defined("TT_AP")){ header("Location: ../adminpanel.php?p=forbidden"); exit; }
$_SESSION["LASTADDR"] = $_SERVER["REQUEST_URI"];

$attrVisible = "hidden";
if (isset($_GET["upload"])){
    if (!empty($_GET["upage"]))
        $page = $_GET["upage"];
    else
        $page = 1;

    $attrVisible = "";
}

$allowedFormats = \Engine\Engine::GetEngineInfo("upf");
$allowedSize = \Engine\Engine::GetEngineInfo("ups");
$allowedSizeMB = round($allowedSize / 1024 / 1024);

$uploadedList = \Engine\Uploader::GetUploadList($user->getId());
$uploadedListSize = count($uploadedList);
$tableBody = "";
if ($uploadedListSize == 0) {
    $tableBody = "<tr><td colspan=\"4\"><span class=\"glyphicons glyphicons-info-sign\"></span> Вы не загрузили ещё ни одного файла.</td></tr>";
}
else {
    for ($i = 0; $i < $uploadedListSize; $i++){
        $fileName = \Engine\Uploader::GetUploadInfo($uploadedList[$i], "name");
        $filePath = \Engine\Uploader::GetUploadInfo($uploadedList[$i], "file_path");
        $fileUploadDate = \Engine\Engine::DateFormatToRead(\Engine\Uploader::GetUploadInfo($uploadedList[$i], "upload_date"));
        $tableBody .= "<tr>";
        $tableBody .= "<td><input type=\"checkbox\" data-file-id=\"$uploadedList[$i]\"></td>";
        $tableBody .= "<td><a href=\"$filePath\\$fileName\">$fileName</a></td>";
        $tableBody .= "<td>$fileUploadDate</td>";
        $tableBody .= "<td>
                            <button class=\"btn btn-default\" type=\"submit\" name=\"uploader-file-delete\" formaction=\"adminpanel/scripts/uploader.php?fid=$uploadedList[$i]\">Удалить файл</button>
                        </td>";
        $tableBody .= "</tr>";
    }
}

if (!empty($_GET["res"])){
    switch($_GET["res"]) {
        case "1s":
            $uploadResponse .= "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> Файл был успешно загружен!</div>";
            break;
        case "1nnf":
            $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> Вы не выбрали файл для загрузки!</div>";
            break;
        case "1nnvft":
            $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Нельзя загрузить файл данного типа.</div>";
            break;
        case "1nnvfs":
            $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Этот файл имеет слишком большой размер!</div>";
            break;
        case "1ndb":
            $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> Ошибка баз данных. Сообщите Администрации.</div>";
            break;
        case "1nnp":
            $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> Не удалось переместить файл. Сообщите Администрации.</div>";
            break;
        case "1n":
            $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Не удалось загрузить файл.</div>";
            break;
        case "1ndsf":
            $uploadResponse .= "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> Выделенные файлы были удалены.</div>";
            break;
        case "1ndnef":
            $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> Не выбраны файлы на удаление.</div>";
            break;
    }
}

?>
<div class="uploader-form" id="uploader-form" <?=$attrVisible?>>
    <form enctype="multipart/form-data" action="adminpanel/scripts/uploader.php" method="post">
        <div class="uploader-block">
            <div class="uploader-header">
                Загрузчик
                <button class="uploader-close-btn" id="uploader-close-btn" type="button">X</button>
            </div>
            <div class="uploader-body">
                <?=$uploadResponse?>
                <p>Разрешённые к загрузке файлы: <?=$allowedFormats?></p>
                <p>Максимальный размер загружаемого файла: <?=$allowedSize?> байт (<?=$allowedSizeMB?> мегабайт).</p>
                <hr>
                <strong>Загрузка файла:</strong>
                <input type="file" name="uploader-file-input">
                <br>
                <button class="btn btn-default" type="submit" name="uploader-upload-file-btn"><span class="glyphicons glyphicons-upload"></span> Загрузить файл</button>
                <hr>
                <div class="alert alert-info" id="uploader-counter-div" hidden>
                    <strong>Выделенно файлов:</strong>
                    <span id="uploader-selected-counter">0</span>
                    <input type="hidden" id="uploader-file-delete-ids" name="uploader-file-delete-ids">
                </div>
                <div class="btn-group" id="uploader-file-manipulator-div">
                    <button class="btn btn-danger" type="submit" id="uploader-delete-files-btn" name="uploader-delete-files-btn" disabled>Удалить выделенные</button>
                </div>
                <hr>
                <table class="uploader-table">
                    <thead>
                    <tr>
                        <td><input type="checkbox" id="uploader-table-select-all" title="Выделить все"></td>
                        <td>Имя файла</td>
                        <td>Дата загрузки</td>
                        <td></td>
                    </tr>
                    </thead>
                    <tbody>
                        <?=$tableBody?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>