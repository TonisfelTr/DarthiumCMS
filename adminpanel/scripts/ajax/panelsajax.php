<?php
include "../../../engine/main.php";

function CreatePanel($title, $content, $visibility, $side){
    $result = \SiteBuilders\SidePanelsAgent::AddSidePanel($side, $title, $content, $visibility);
    if ($result === false){
        return "failed";
    } else {
        return $result;
    }
}

function EditPanel($id, array $newContent){
    $result = (\SiteBuilders\SidePanelsAgent::EditSidePanel($id, $newContent));
    if ($result === 35){
        return "pne";
    } elseif ($result === false){
        return "failed";
    } else return "okey";
}

function GetPanel($id){
    $result = \SiteBuilders\SidePanelsAgent::GetPanel($id);
    if ($result === 35){
        return "pne";
    } elseif ($result === false){
        return "failed";
    } else return json_encode($result);
}

function RemovePanel($id){
    $result = \SiteBuilders\SidePanelsAgent::DeleteSidePanel($id);
    if ($result === 35){
        return "pne";
    } elseif ($result === false){
        return "failed";
    } else return "okey";
}

\Engine\Engine::LoadEngine();
if (\Users\UserAgent::SessionContinue() === true) {
    $user = new \Users\User($_SESSION["uid"]);
    if ($user->UserGroup()->getPermission("sc_design_edit")){
        if (isset($_POST["addpanel"])){
            echo CreatePanel($_POST["panel-name"], $_POST["panel-content"], $_POST["panel-visibility"], ($_POST["panel-side"] == "left") ? SiteBuilders\SB_LEFTSIDE : SiteBuilders\SB_RIGHTSIDE);
            exit;
        }
        if (isset($_POST["deletepanel"])){
            echo RemovePanel($_POST["panel-id"]);
            exit;
        }
        if (isset($_POST["getpanel"])){
            echo GetPanel($_POST["panel-id"]);
            exit;
        }
        if (isset($_POST["editpanel"])){
            $toChange = [];
            if (isset($_POST["panel-name"])) $toChange["name"] = $_POST["panel-name"];
            if (isset($_POST["panel-content"])) $toChange["content"] = $_POST["panel-content"];
            if (isset($_POST["panel-visibility"])) $toChange["isVisible"] = $_POST["panel-visibility"];
            if (isset($_POST["panel-side"])) $toChange["type"] = ($_POST["panel-side"] == "left") ? SiteBuilders\SB_LEFTSIDE : SiteBuilders\SB_RIGHTSIDE;
            if (!empty($toChange))
                echo EditPanel($_POST["panel-id"], $toChange);
            else
                echo "okey";
            exit;
        }
    } else {
        header("Location: ../../../adminpanel.php?res=1");
        exit;
    }
} else {
    header("Location: ../../../index.php?res=1");
    exit;
}