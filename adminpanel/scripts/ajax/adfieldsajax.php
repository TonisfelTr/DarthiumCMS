<?php

include "../../../engine/main.php";

function BoolToInt($i){
    if ($i == "true") return 1;
    else return 0;
}

function Add(){
    if (true === \Engine\DataKeeper::isExistsIn("tt_adfields", "name", $_POST["field-name"])) {
        echo "fae"; //field already exists.
        return;
    }
    $insert = \Engine\DataKeeper::InsertTo("tt_adfields", array(
        "name" => $_POST["field-name"],
        "description" => $_POST["field-description"],
        "link" => $_POST["field-link"],
        "isRequied" => BoolToInt($_POST["field-isreq"]),
        "inRegister" => BoolToInt($_POST["field-inregister"]),
        "type" => $_POST["field-type"],
        "canBePrivate" => BoolToInt($_POST["field-privatestat"])
    ));
    if ($insert){
        echo $insert;
    } else return;
}

function Edit(){
    if (false === \Engine\DataKeeper::isExistsIn("tt_adfields", "id", $_POST["field-id"])) {
        echo "fne"; //field not exists.
        return;
    }

    $vars = array(
        "name" => $_POST["field-name"],
        "description" => $_POST["field-description"],
        "type" => $_POST["field-type"],
        "isRequied" => BoolToInt($_POST["field-isreq"]),
        "inRegister" => BoolToInt($_POST["field-inregister"]),
        "canBePrivate" => BoolToInt($_POST["field-privatestat"]),
        "link" => $_POST["field-link"]
    );

    if (\Engine\DataKeeper::Update("tt_adfields", $vars, array("id" => $_POST["field-id"])))
        echo "sef";
    else
        echo "fef";
    return;
}

function Remove() {
    if (false === \Engine\DataKeeper::isExistsIn("tt_adfields", "id", $_POST["field-id"])) {
        echo "fne"; //field not exists.
        return;
    }

    if (\Engine\DataKeeper::Delete("tt_adfields", ["id" => $_POST["field-id"]]))
        echo "sdf";
    else
        echo "fdf";
    return;
}

function Get(){
    if (false === \Engine\DataKeeper::isExistsIn("tt_adfields", "id", $_POST["field-id"])) {
        echo "fne"; //field not exists.
        return;
    }
    echo json_encode(\Engine\DataKeeper::Get("tt_adfields", ["*"], ["id" => $_POST["field-id"]])[0]);
    return;
}

\Engine\Engine::LoadEngine();
if (\Users\UserAgent::SessionContinue() === true) {
    $user = new \Users\User($_SESSION["uid"]);
    if ($user->UserGroup()->getPermission("change_engine_settings")){
        if ($_POST["action"] == "add"){
            if (strlen($_POST["field-name"]) > 16 || strlen($_POST["field-name"]) < 4) {
                echo "in";
                exit;
            }
            Add();
            exit;
        }

        if ($_POST["action"] == "edit"){
            if (strlen($_POST["field-name"]) > 16 || strlen($_POST["field-name"]) < 4) {
                echo "in";
                exit;
            }
            Edit();
            exit;
        }

        if ($_POST["action"] == "delete"){
            Remove();
            exit;
        }

        if ($_POST["action"] == "get"){
            Get();
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

