<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";;

function BoolToInt($i){
    if ($i == "true") return 1;
    else return 0;
}

function IsCorrectName($str){
    $utfString = mb_convert_encoding($str, "WINDOWS-1251");
    if (strlen($utfString) > 16 || strlen($utfString) < 4) return false;
    if (preg_match("/[a-zA-ZА-Яа-я0-9_\-&\s]+/", $str) === 1) return true;
    else return false;
}

function Add(){
    if (true === \Engine\DataKeeper::exists("tt_adfields", "name", $_POST["field-name"])) {
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
        "canBePrivate" => BoolToInt($_POST["field-privatestat"]),
        "custom" => $_POST["field-custom"])
    );
    if ($insert){
        echo $insert;
    } else return;
}

function Edit(){
    if (false === \Engine\DataKeeper::exists("tt_adfields", "id", $_POST["field-id"])) {
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
        "link" => $_POST["field-link"],
        "custom" => $_POST["field-custom"]
    );

    if (\Engine\DataKeeper::Update("tt_adfields", $vars, array("id" => $_POST["field-id"])))
        echo "sef";
    else
        echo "fef";
    return;
}

function Remove() {
    if (false === \Engine\DataKeeper::exists("tt_adfields", "id", $_POST["field-id"])) {
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
    if (false === \Engine\DataKeeper::exists("tt_adfields", "id", $_POST["field-id"])) {
        echo "fne"; //field not exists.
        return;
    }
    echo json_encode(\Engine\DataKeeper::Get("tt_adfields", ["*"], ["id" => $_POST["field-id"]])[0]);
    return;
}

\Engine\Engine::LoadEngine();
if (\Users\UserAgent::SessionContinue() === true) {
    $user = new \Users\Models\User($_SESSION["uid"]);
    if ($user->getUserGroup()->getPermission("change_engine_settings")){
        if ($_POST["action"] == "add"){
            if (!IsCorrectName($_POST["field-name"])) {
                echo "in";
                exit;
            }
            Add();
            exit;
        }

        if ($_POST["action"] == "edit"){
            if (!IsCorrectName($_POST["field-name"])) {
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

