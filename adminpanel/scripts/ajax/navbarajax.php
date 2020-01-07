<?php
include_once "../../../engine/main.php";
\Engine\Engine::LoadEngine();


$session = \Users\UserAgent::SessionContinue();
if ($session === TRUE)
    $user = new \Users\User($_SESSION["uid"]);
else
    $user = false;

if ($user !== false){
    if ($user->UserGroup()->getPermission("sc_design_edit") == 1){
        if (isset($_POST["create_btn"])){
            if (\SiteBuilders\NavbarAgent::AddButton(trim($_POST["text"]), trim($_POST["link"])))
            echo "okey";
        }

        if (isset($_POST["create_list"])){
            if (\SiteBuilders\NavbarAgent::AddList(trim($_POST["text"]), trim($_POST["action"])))
                echo "okey";
        }
        if (isset($_POST["create_list_element"])){
            if ($result = \SiteBuilders\NavbarAgent::AddListElement($_POST["id"], $_POST["text"], $_POST["action"]))
                echo $result;
        }
        if (isset($_POST["remove_list_element"])){
            if (\SiteBuilders\NavbarAgent::RemoveElement($_POST["id"]))
                echo "okey";
        }
        if (isset($_POST["change_list_element"])){
            if (\SiteBuilders\NavbarAgent::ChangeElement($_POST["id"], $_POST["content"], $_POST["action"]))
                echo "okey";
        }
        if (isset($_POST["change_list_param"])){
            if (\SiteBuilders\NavbarAgent::ChangeElement($_POST["id"], $_POST["content"], $_POST["action"]))
                echo "okey";
        }
    }
}