<?php

include_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($user === false) exit;

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../index.php?page=errors/nonauth"); exit;}

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if (!strstr($_SESSION["LASTADDR"], "?")) $symbol = "?";
else $symbol = "&";

if (isset($_POST["uploader-upload-file"])){
    if($user->UserGroup()->getPermission("upload_add")){
        $result = \Engine\Uploader::UploadFile($user->getId(), $_FILES['uploader-file']);
        if ($result === TRUE) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1s&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 28) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol .  "res=1nnf&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 13) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1nnvft&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 27) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1nnvfs&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 2) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1ndb&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 12) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1nnp&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        } else {
            header("Location: ". $_SESSION["LASTADDR"] . $symbol . "res=1n&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        }
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        $_SESSION["LASTADDR"] = null;
        exit;
    }
}

if (isset($_POST["uploader-delete-file"])){
    if ($user->UserGroup()->getPermission("upload_delete")){
        if (empty($_REQUEST["fdelete"])){
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1ndnef&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        } else {
            $filesToDelete = explode(",", $_REQUEST["fdelete"]);
            for ($i = 0; $i <= count($filesToDelete) - 1; $i++) {
                if (!\Engine\Uploader::DeleteFile($filesToDelete[$i])){
                    header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1ndsf&uploaderVisible");
                    $_SESSION["LASTADDR"] = null;
                    exit;
                }
            }
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1sdf&uploaderVisible");
            $_SESSION["LASTADDR"] = null;
            exit;
        }
    } else {
        header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1npdof&uploaderVisible");
        $_SESSION["LASTADDR"] = null;
        exit;
    }
}

header("Location: ../../index.php?page=errors/forbidden");