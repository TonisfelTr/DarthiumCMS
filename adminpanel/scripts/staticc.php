<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 20.05.2018
 * Time: 23:38
 */

/* Negative:
 * 7nsn - [not] [sended] [name] of page.
 * 7nbn - [name] is[n't] [big]
 * 7nst - [not] [sended] [text] of page.
 * 7nbt - [text] is[n't] [big].
 * 7ntbd - [discription] is [too] [big] ([negative])
 * 7ncp - page has [not] been [created].
 * 7npe - [page] is [not] [exist].
 * 7npse - page save error.
 * 7ndsp - not deleted some page.
 * Positive:
 * 7scp - successfull create page.
 * 7sphbe - page has been successfull edited.
 * 7srsp - successfull remove selected pages.
*/
require_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\Models\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

$createSPPerm = $user->UserGroup()->getPermission("sc_create_pages");
$editSPPerm = $user->UserGroup()->getPermission("sc_edit_pages");
$removeSPPerm = $user->UserGroup()->getPermission("sc_remove_pages");
$designSCPerm = $user->UserGroup()->getPermission("sc_design_edit");

if (isset($_POST["staticc-page-create-create-btn"]) && $createSPPerm) {
    if (empty($_POST["staticc-page-create-name-input"])) {
        header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7nsn");
        exit;
    }
    if (strlen($_POST["staticc-page-create-name-input"]) < 4) {
        header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7nbn");
        exit;
    }

    if (empty($_POST["staticc-page-create-textarea"])) {
        header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7nst");
        exit;
    }
    if (strlen($_POST["staticc-page-create-textarea"]) < 20) {
        header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7nbt");
        exit;
    }
    if (!empty($_POST["staticc-page-create-description-input"]) && strlen($_POST["staticc-page-create-description-input"]) > 100) {
        header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7ntbd");
        exit;
    }

    if (\Forum\StaticPagesAgent::CreatePage($_POST["staticc-page-create-name-input"], $user->getId(),
        (!empty($_POST["staticc-page-create-description-input"])) ? $_POST["staticc-page-create-description-input"] : "", $_POST["staticc-page-create-textarea"], $_POST["staticc-page-create-keywords"])) {
        \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("static_editor.logs.created_static_page_log") . "\"" . $_POST["staticc-page-create-name-input"] . "\"");
        header("Location: ../../adminpanel.php?p=staticc&res=7scp&reqtype=1");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7ncp");
        exit;
    }
}
elseif (isset($_POST["staticc-search-btn"])){
    if (empty($_POST["staticc-search-input"])){
        if (@$_POST["staticc-search-type"] == "name" ) {
            header("Location: ../../adminpanel.php?p=staticc&res=7nssn");
            exit;
        }
        elseif (@$_POST["staticc-search-type"] == "author"){
            header("Location: ../../adminpanel.php?p=staticc&res=7nssan");
            exit;
        }
    }

    if (@$_POST["staticc-search-type"] == "name"){
        header("Location: ../../adminpanel.php?p=staticc&search-name=" . $_POST["staticc-search-input"]);
        exit;
    }

    if (@$_POST["staticc-search-type"] == "author"){
        header("Location: ../../adminpanel.php?p=staticc&search-author=" . $_POST["staticc-search-input"]);
        exit;
    }
}
elseif (isset($_POST["staticc-page-edit-btn"]) && $editSPPerm) {
    if (\Forum\StaticPagesAgent::isPageExists($_REQUEST["id"])) {
        header("Location: ../../adminpanel.php?p=staticc&reqtype=3&editpage=" . $_REQUEST["id"]);
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=staticc&res=7npe");
        exit;
    }
}
elseif (isset($_POST["staticc-page-edit-edit-btn"]) && $editSPPerm) {
    if (!\Forum\StaticPagesAgent::isPageExists($_POST["staticc-page-edit-id"])) {
        header("Location: ../../adminpanel.php?p=staticc&res=7npe");
        exit;
    }
    $pageId = $_POST["staticc-page-edit-id"];
    $result = \Forum\StaticPagesAgent::ChangePageData($pageId, "name", $_POST["staticc-page-edit-name-input"]);
    $result = \Forum\StaticPagesAgent::ChangePageData($pageId, "description", $_POST["staticc-page-edit-description-input"]);
    $result = \Forum\StaticPagesAgent::ChangePageData($pageId, "keywords", $_POST["staticc-page-edit-keywords"]);
    $result = \Forum\StaticPagesAgent::EditPage($pageId, $_POST["staticc-page-edit-textarea"]);
    if ($result) {
        \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("static_editor.logs.edited_static_page_log") . "\"" . $_POST["staticc-page-edit-name-input"] . "\".");
        header("Location: ../../adminpanel.php?p=staticc&res=7sphbe&reqtype=1");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=staticc&res=7npse&reqtype=3&editpage=$pageId");
        exit;
    }
}
elseif (isset($_POST["staticc-search-remove-btn"]) && $removeSPPerm){
    $pagesId = explode(",", $_POST["staticc-page-delete"]);
    foreach ($pagesId as $id){
        if (!\Forum\StaticPagesAgent::isPageExists($id)) {
            header("Location: ../../adminpanel.php?p=staticc&res=7nspe");
            exit;
        }
        $pageName = \Forum\StaticPagesAgent::GetPage($id)->getPageName();
        $result = \Forum\StaticPagesAgent::RemovePage($id);
    }
    if ($result === true){
        \Guards\Logger::LogAction($user->getId(), \Engine\LanguageManager::GetTranslation("static_editor.logs.remove_static_page_log") . "\"$pageName\".");
        header("Location: ../../adminpanel.php?p=staticc&res=7srsp");
        exit;
    } else {
        header("Location: ../../adminpanel.php?p=staticc&res=7mdsp");
        exit;
    }
}
else {
    header("Location: ../../adminpanel.php?p=staticc&res=1");
    exit;
}