<?php

use Engine\Engine;
use Guards\SocietyGuard;
use Users\UserAgent;
use Users\Models\User;
use Users\Services\FlashSession;

include_once "../../engine/engine.php";
Engine::LoadEngine();

if ($sessionRes = UserAgent::SessionContinue()) {
    $user = new \Users\Models\User((new \Users\Services\Session(\Users\Services\FlashSession::getSessionId()))->getContent()["uid"]);
} else {
    header("Location: ../../index.php?page=errors/nonauth");
    exit;
}

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if (!strstr($_SESSION["LASTADDR"], "?")) $symbol = "?";
else $symbol = "&";

if (isset($_POST["uploader-upload-file-btn"])){
    if($user->UserGroup()->getPermission("upload_add")){
        $result = \Engine\Uploader::UploadFile($user->getId(),"uploader-file-input");
        if ($result === TRUE) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1s&upload");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 28) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol .  "res=1nnf&upload");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 13) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1nnvft&upload");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 27) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1nnvfs&upload");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 2) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1ndb&upload");
            $_SESSION["LASTADDR"] = null;
            exit;
        } elseif ($result == 12) {
            header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1nnp&upload");
            $_SESSION["LASTADDR"] = null;
            exit;
        } else {
            header("Location: ". $_SESSION["LASTADDR"] . $symbol . "res=1n&upload");
            $_SESSION["LASTADDR"] = null;
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?res=1");
        $_SESSION["LASTADDR"] = null;
        exit;
    }
}

if (isset($_POST["uploader-file-delete"]) || isset($_POST["uploader-delete-files-btn"])){
    if ($user->UserGroup()->getPermission("upload_delete")){
        if (isset($_POST["uploader-delete-files-btn"])) {
            if (empty($_POST["uploader-file-delete-ids"])) {
                header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1ndnef&upload");
                $_SESSION["LASTADDR"] = null;
                exit;
            } else {
                $filesToDelete = explode(",", $_POST["uploader-file-delete-ids"]);
                for ($i = 0; $i <= count($filesToDelete) - 1; $i++) {
                    if (!\Engine\Uploader::DeleteFile($filesToDelete[$i])) {
                        header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1ndsf&upload");
                        $_SESSION["LASTADDR"] = null;
                        exit;
                    }
                }
                header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1sdf&upload");
                $_SESSION["LASTADDR"] = null;
                exit;
            }
        }
        if (isset($_POST["uploader-file-delete"])){
            if (empty($_POST["fid"])) {
                header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1ndnef&upload");
                $_SESSION["LASTADDR"] = null;
                exit;
            } else {
                if (!\Engine\Uploader::DeleteFile($_POST["fid"])) {
                    header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1ndsf&upload");
                    $_SESSION["LASTADDR"] = null;
                    exit;
                }
                header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1sdf&upload");
                $_SESSION["LASTADDR"] = null;
                exit;
            }
        }
    } else {
        header("Location: " . $_SESSION["LASTADDR"] . $symbol . "res=1npdof&upload");
        $_SESSION["LASTADDR"] = null;
        exit;
    }
}

//header("Location: ../../adminpanel.php?p=forbidden");