<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";;
\Engine\Engine::LoadEngine();
if (\Users\UserAgent::SessionContinue() === true){
    $user = new \Users\Models\User($_SESSION["uid"]);
    if ($user->getUserGroup()->getPermission("report_foreign_edit") || $user->getUserGroup()->getPermission("report_edit")){
        if (isset($_POST["atd"])){
            if (!empty($_POST["uid"])){
                $_POST["uid"] = \Users\UserAgent::GetUserId($_POST["uid"]);
                if ($_POST["uid"] === false) {
                    echo "User is not exist.";
                    exit;
                }
                if (!empty($_POST["rid"])) {
                    if (\Users\GroupAgent::IsHavePerm(\Users\UserAgent::GetgetUserGroupId($_POST["uid"]), "profile_foreign_edit")) {
                        echo "Not need to add.";
                        exit;
                    }
                    if ($_POST["uid"] == $user->getId()) {
                        echo "Not need to add yourself.";
                        exit;
                    }
                    if (\Guards\ReportAgent::AddToDiscusse($_POST["rid"], $_POST["uid"], $user->getId()) === false) {
                        echo "User is added."; exit;
                    } else {
                        $thisUser = new \Users\Models\User($_POST["uid"]);
                        $thisUser->Notifications()->createNotify(1, $user->getId(), $_POST["rid"]);
                        $report = new \Guards\Report($_POST["rid"]);
                        foreach ($report->getAddedToDiscuse() as $c){
                            if ($c == $_POST["uid"]) continue;
                            $n = new \Users\UserNotificator($c);
                            $n->createNotify(18, $user->getId(), $_POST["rid"] . "," . $_POST["uid"]);
                        }
                        echo $_POST["uid"] . " " . \Users\UserAgent::GetUserNick($_POST["uid"]);
                        exit;
                    }
                } else { echo "Report id not set."; exit; }
            } else { echo "User id not set."; exit; }
        }
        if (isset($_POST["rfd"])){
            if (!empty($_POST["uid"])) {
                if (\Users\UserAgent::IsUserExist($_POST["uid"]) === false) {
                    echo "User is not exist.";
                    exit;
                }
                if (!empty($_POST["rid"])) {
                    if (!\Guards\ReportAgent::isAddedToDiscusse($_POST["rid"], $_POST["uid"])){
                        echo "User is not in discusse.";
                        exit;
                    }

                    $report = new \Guards\Report($_POST["rid"]);
                    if ($report->getAuthorID() == $_POST["uid"]){
                        echo "Cannot add the author of report.";
                        exit;
                    }

                    if (\Guards\ReportAgent::RemoveFromDiscusse($_POST["rid"], $_POST["uid"]) > 0){
                        $thisUser = new \Users\Models\User($_POST["uid"]);
                        $thisUser->Notifications()->createNotify(1, $user->getId(), $_POST["rid"]);
                        $report = new \Guards\Report($_POST["rid"]);
                        foreach ($report->getAddedToDiscuse() as $c){
                            if ($c == $_POST["uid"]) continue;
                            $n = new \Users\UserNotificator($c);
                            $n->createNotify(19, $user->getId(), $_POST["rid"] . "," . $_POST["uid"]);
                        }
                        echo "Removed from discusse.";
                        exit;
                    } else {
                        echo "Something wrong with deleting.";
                        exit;
                    }
                } else {
                    echo "Report id not set.";
                    exit;
                }
            } else { echo "User is not setted."; exit; }

        }

    } else {
        echo "Permission denied";
        exit;
    }
}

echo "Not auth.";