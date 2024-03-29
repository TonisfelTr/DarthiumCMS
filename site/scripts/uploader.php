<?php

include_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

if ($user === false) exit;

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\Models\User($user->getSession()->getContent()["uid"]);
else { header("Location: ../../index.php?page=errors/nonauth"); exit;}

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true) || $user->isBanned()){
    header("Location: banned.php");
    exit;
}

if (!strstr($user->getSession()->getContent()["LASTADDR"], "?")) $symbol = "?";
else $symbol = "&";

if (isset($_POST["uploader-upload-file"])){
    if($user->UserGroup()->getPermission("upload_add")){
        $result = \Engine\Uploader::UploadFile($user->getId(), $_FILES['uploader-file']);
        if ($result === TRUE) {
            header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1s&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        } elseif ($result == 28) {
            header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol .  "res=1nnf&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        } elseif ($result == 13) {
            header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1nnvft&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        } elseif ($result == 27) {
            header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1nnvfs&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        } elseif ($result == 2) {
            header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1ndb&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        } elseif ($result == 12) {
            header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1nnp&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        } else {
            header("Location: ". $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1n&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        }
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        $user->getSession()->getContent()["LASTADDR"] = null;
        exit;
    }
}

if (isset($_POST["uploader-delete-file"])){
    if ($user->UserGroup()->getPermission("upload_delete")){
        if (empty($_REQUEST["fdelete"])){
            header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1ndnef&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        } else {
            $filesToDelete = explode(",", $_REQUEST["fdelete"]);
            for ($i = 0; $i <= count($filesToDelete) - 1; $i++) {
                if (!\Engine\Uploader::DeleteFile($filesToDelete[$i])){
                    header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1ndsf&uploaderVisible");
                    $user->getSession()->getContent()["LASTADDR"] = null;
                    exit;
                }
            }
            header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1sdf&uploaderVisible");
            $user->getSession()->getContent()["LASTADDR"] = null;
            exit;
        }
    } else {
        header("Location: " . $user->getSession()->getContent()["LASTADDR"] . $symbol . "res=1npdof&uploaderVisible");
        $user->getSession()->getContent()["LASTADDR"] = null;
        exit;
    }
}

header("Location: ../../index.php?page=errors/forbidden");