<?php
define("TT_Uploader", true);#
if ($user != false) {
    $uploadList = \Engine\Uploader::GetUploadList($user->getId());
    $uploadCount = count($uploadList);
    $_SESSION["LASTADDR"] = $_SERVER["REQUEST_URI"];

    if (!isset($_GET["uploaderVisible"]))
        $uploaderVisible = "hidden";
    else
        $uploaderVisible = "";

    $uploadResponse = "";
    if (!empty($_GET["res"])) {
        switch ($_GET["res"]) {
            case "1s":
                $uploadResponse .= "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.file_upload_success") . "</div>";
                break;
            case "1nnf":
                $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.not_selected_for_update") . "</div>";
                break;
            case "1nnvft":
                $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.invalid_file_format") . "</div>";
                break;
            case "1nnvfs":
                $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.too_big_file_size") . "</div>";
                break;
            case "1ndb":
                $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.db_error") . "</div>";
                break;
            case "1nnp":
                $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.move_file_error") . "</div>";
                break;
            case "1n":
                $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.file_upload_failed") . "</div>";
                break;
            case "1ndsf":
                $uploadResponse .= "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.selected_files_removed") . "</div>";
                break;
            case "1ndnef":
                $uploadResponse .= "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.no_selected_files_for_remove") . "</div>";
                break;
        }
    }

    $uploadFormats = \Engine\Engine::GetEngineInfo("upf");
    $uploadMaxSize = \Engine\Engine::GetEngineInfo("ups") . " " . \Engine\LanguageManager::GetTranslation("uploader.bytes") . " (" . (\Engine\Engine::GetEngineInfo("ups") / 1024 / 1024) . " " . \Engine\LanguageManager::GetTranslation("uploader.megabytes") . ")";

    $uploadFilesTable = "";
    if ($uploadCount == 0) {
        $uploadFilesTable = "<tr>
                            <td colspan=\"4\" style=\"text-align: center;\"><span class=\"glyphicon glyphicon-info-sign\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.no_file_uploaded") . "</td>
                        </tr>";
    } else {
        for ($i = 0; $i <= $uploadCount - 1; $i++) {
            $uploadName = \Engine\Uploader::GetUploadInfo($uploadList[$i], "name");
            $uploadFilePath = \Engine\Uploader::GetUploadInfo($uploadList[$i], "file_path");
            $uploadDate = Engine\Engine::DateFormatToRead(\Engine\Uploader::GetUploadInfo($uploadList[$i], "upload_date"));
            $uploadFilesTable .= "<tr>
                                <td><input type=\"checkbox\" data-fid-selected=\"$uploadList[$i]\"></td>
                                <td>$uploadName</td>
                                <td>
                                    <a href=\"$uploadFilePath\\$uploadName\">
                                        <span class=\"glyphicons glyphicons-link\"></span> " . \Engine\LanguageManager::GetTranslation("uploader.link") . "
                                    </a>
                                </td>
                                <td>$uploadDate</td>
                             </tr>";
        }
    }

    include_once \Engine\Engine::ConstructTemplatePath("main", "uploader", "html");
    $uploaderBlock = getBrick();

    include_once \Engine\Engine::ConstructTemplatePath("script", "uploader", "js");
    $uploaderJS = getBrick();

    $uploaderBlock = str_replace_once("{PROFILE_UPLOADER:HIDDEN_ATTR}", $uploaderVisible, $uploaderBlock);
    $uploaderBlock = str_replace_once("{PROFILE_UPLOADER:RESPONSE}", $uploadResponse, $uploaderBlock);
    $uploaderBlock = str_replace_once("{PROFILE_UPLOADER:FILES_ACCEPTED_EXTS}", $uploadFormats, $uploaderBlock);
    $uploaderBlock = str_replace_once("{PROFILE_UPLOADER:FILES_ACCEPTED_MAX_SIZE}", $uploadMaxSize, $uploaderBlock);
    $uploaderBlock = str_replace_once("{PROFILE_UPLOADER:FILES_TABLE}", $uploadFilesTable, $uploaderBlock);

    $main = str_replace_once("{PROFILE_UPLOADER:STYLESHEET}", "<link rel=\"stylesheet\" href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/uploader-style.css\">", $main);
    $main = str_replace_once("{PROFILE_UPLOADER_BLOCK}", $uploaderBlock, $main);
    $main = str_replace_once("{PROFILE_UPLOADER:JS}", $uploaderJS, $main);
}
?>