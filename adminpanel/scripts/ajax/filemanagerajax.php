<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";
\Engine\Engine::LoadEngine();


$session = \Users\UserAgent::SessionContinue();
if ($session === TRUE)
    $user = new \Users\Models\User($_SESSION["uid"]);
else
    $user = false;

if ($user->UserGroup()->getPermission("change_template_design")){
    if (isset($_POST["get_content"])){
        $json = json_encode(scandir("../../../site/templates/" . $_POST["template_name"] . "/" . (($_POST["enddir"] != "/undefined") ? $_POST["enddir"] : "")));
        echo $json;
        exit;
    }
    if (isset($_POST["get_file_content"])){
        echo file_get_contents("../../../site/templates/" . $_POST["template_name"] . "/" . $_POST["filename"]);
        exit;
    }
} else {
    header("Location: ../../../adminpanel.php?res=1");
    exit;
}