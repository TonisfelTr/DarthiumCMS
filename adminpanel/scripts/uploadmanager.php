<?php
require_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) {
    $user = new \Users\Models\User((new \Users\Services\Session(\Users\Services\FlashSession::getSessionId()))->getContent()["uid"]);
}
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if (isset($_POST["search-btn"])) {

    if (!isset($_POST["uploadedlist-search-input"])) {
        header("Location: ../../adminpanel.php?p=uploadedlist");
        exit;
    }

    if (isset($_POST["search-type"]) && $_POST["search-type"] != "") {
        if ($_POST["search-type"] == "nickname") {
            header("Location: ../../adminpanel.php?p=uploadedlist&filter=author&sf=" . $_POST["uploadedlist-search-input"]);
            exit;
        }

        if ($_POST["search-type"] == "ref"){
            header("Location: ../../adminpanel.php?p=uploadedlist&filter=ref&sf=" . $_POST["uploadedlist-search-input"]);
            exit;
        }


    }
}

if (isset($_POST["delete-btn"])){
    if (!isset($_REQUEST["fdfi"]) || empty($_REQUEST["fdfi"])){
        header("Location: ../../adminpanel.php?p=uploadedlist&res=9ndfs");
        exit;
    }
    $forDelete = explode(",", $_REQUEST["fdfi"]);
    foreach($forDelete as $file){
        \Engine\Uploader::DeleteFile($file);
    }
    \Guards\Logger::LogAction($user->getId(), " " . \Engine\LanguageManager::GetTranslation("uploader_panel.delete_files_log")
    . count($forDelete) . \Engine\LanguageManager::GetTranslation("uploader_panel.delete_files_log_2"));
    header("Location: ../../adminpanel.php?p=uploadedlist&res=9ssfd");
    exit;
}

if (isset($_POST["file-delete-btn"])){
    if (!isset($_REQUEST["fidd"]) || empty($_REQUEST["fidd"])){
        header("Location: ../../adminpanel.php?p=uploadedlist&res=9nsfi");
        exit;
    }
    if (\Engine\Uploader::DeleteFile($_REQUEST["fidd"])){
        header("Location: ../../adminpanel.php?p=uploadedlist&res=9sfd");
        exit;
    }
}

header("Location: ../../adminpanel.php?res=1");
exit;