<?php
include_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

$session = \Users\UserAgent::SessionContinue();
if ($session === TRUE) {
    $user = new \Users\Models\User((new \Users\Services\Session(\Users\Services\FlashSession::getSessionId()))->getContent()["uid"]);
    if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
        header("Location: banned.php");
        exit;
    }
}
else
    $user = false;

if (isset($_POST["save_template_file_btn"])) {
    if (file_exists("../../site/templates/" . $_POST["templates_select"] . "/" . $_POST["template_path"] . "/" . $_POST["template_file_name"])) {
        if (file_put_contents("../../site/templates/" . $_POST["templates_select"] . "/" . $_POST["template_path"] . "/" . $_POST["template_file_name"], $_POST["template_file_editor"])) {
            header("Location: ../../adminpanel.php?p=teditor&res=10scf");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=teditor&res=10fcf");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=teditor&res=10fne");
        exit;
    }

}

header("Location: ../../adminpanel.php?p=forbidden"); exit;