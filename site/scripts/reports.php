<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../index.php?page=errors/nonauth"); exit;}

if (isset($_REQUEST["reports-create"])) {
    if ($user->UserGroup()->getPermission("report_create")) {
        header("Location: ../../index.php?page=report&preg=add");
        exit;
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

if (isset($_REQUEST["reports-send-btn"])){
    if ($user->UserGroup()->getPermission("report_create")) {
        $backRequest = "Location: ../../index.php?page=report&preg=add";
        if (isset($_REQUEST["reports-theme-selector"]) && $_REQUEST["reports-theme-selector"] == "other"){
            if (!empty($_REQUEST["reports-select-other-theme"])) $theme = $_REQUEST["reports-select-other-theme"];
            else {
                $backRequest .= "&res=2nst";
                header($backRequest);
                exit;
            }
        } else $theme = $_REQUEST["reports-theme-selector"];

        if ($_REQUEST["reports-add-short-message"] == ''){
            $backRequest .= "&res=2nnsm";
            header($backRequest);
            exit;
        }

        if ($_REQUEST["reports-add-message"] == ''){
            $backRequest .= "&res=2nnm";
            header($backRequest);
            exit;
        }

        $result = \Guards\ReportAgent::CreateReport($user->getId(), $theme, $_REQUEST["reports-add-short-message"], $_REQUEST["reports-add-message"]);

        if ($result === FALSE){
            $backRequest .= "&res=2ncr";
            header($backRequest);
            exit;
        }

        echo $backRequest = "Location: ../../index.php?page=report&preg=see&rid=$result&res=2scr";
        header($backRequest);
        exit;

    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

if (isset( $_REQUEST["reports-edit"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../index.php?page=report&res=2nrid");
        exit;
    }
    if (($user->UserGroup()->getPermission("report_edit") && $user->getId() == \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author"))
        || $user->UserGroup()->getPermission("report_foreign_edit")){
        header("Location: ../../index.php?page=report&preg=edit&rid=" . $_REQUEST["rid"]);
        exit;
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

if (isset( $_REQUEST["reports-edit"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../index.php?page=report&res=2nrid");
        exit;
    }
    if (($user->UserGroup()->getPermission("report_edit") && $user->getId() == \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author"))
        || $user->UserGroup()->getPermission("report_foreign_edit")){
        header("Location: ../../index.php?page=report&preg=edit&rid=" . $_REQUEST["rid"]);
        exit;
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

if (isset( $_REQUEST["reports-edit-message-edit"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../index.php?page=report&res=2nrid");
        exit;
    }
    if ($user->UserGroup()->getPermission("report_edit") && $user->getId() == \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author")){
        if (\Guards\ReportAgent::ChangeReportParam($_REQUEST["rid"], "message", $_REQUEST["reports-edit-message-text"]) !== TRUE){
            header("Location: ../../index.php?page=report&res=2nscm&preg=edit&rid=".$_REQUEST["rid"]);
            exit;
        } else {
            header("Location: ../../index.php?page=report&res=2sscm&preg=see&rid=".$_REQUEST["rid"]);
            exit;
        }
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

if (isset ($_REQUEST["reports-edit-answer-edit"])){
    if (empty($_REQUEST["ansid"])){
        header("Location: ../../index.php?page=report&res=2nnas");
        exit;
    }
    if ($user->getId() == \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId") && $user->UserGroup()->getPermission("report_answer_edit")){
        if (!empty($_REQUEST["reports-edit-message-text"])){
            if (strlen($_REQUEST["reports-edit-message-text"]) > 4) {
                $result = \Guards\ReportAgent::ChangeAnswerText($_REQUEST["ansid"], $_REQUEST["reports-edit-message-text"], $_REQUEST["reports-edit-reason"], $user->getId());
                if ($result === true){
                    header("Location: ../../index.php?page=report&preg=see&res=2ses&rid=" . \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "reportId"));
                    exit;
                } else {
                    header("Location: ../../index.php?page=report&preg=edit&res=2nes&ansid =" . $_REQUEST["ansid"]);
                    exit;
                }

            } else {
                header("Location: ../../index.php?page=report&preg=edit&res=2nmts&ansid=" . $_REQUEST["ansid"]);
                exit;
            }
        } else {
            header("Location: ../../index.php?page=report&preg=edit&res=2nm&ansid=" . $_REQUEST["ansid"]);
            exit;
        }
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

if (isset( $_REQUEST["reports-answer-send"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../index.php?page=report&res=2nrid");
        exit;
    }
    if ($user->UserGroup()->getPermission("report_talking") &&
       ($user->getId() == \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author") ||
       in_array($user->getId(), explode(",", \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "added"))))){
        if (\Guards\Report::GetReportParam($_REQUEST["rid"], "status") != 2) {
            if (!empty($_REQUEST["reports-answer-text"])) {
                if (strlen($_REQUEST["reports-answer-text"]) > 4) {
                    $result = \Guards\ReportAgent::CreateAnswer($user->getId(), $_REQUEST["reports-answer-text"], $_REQUEST["rid"]);
                    if ($result === true) {
                        $report = new \Guards\Report($_REQUEST["rid"]);
                        if ($report->getAuthorID() != $user->getId()){
                            \Users\UserAgent::GetUser($report->getAuthorID())->Notifications()->createNotify(5, $user->getId(), $report->getId());
                            foreach ($report->getAddedToDiscuse() as $atdUser){
                                if ($atdUser != $user->getId()){
                                    \Users\UserAgent::GetUser($atdUser)->Notifications()->createNotify(5, $user->getId(), $report->getId());
                                }
                            }
                        }
                        header("Location: ../../index.php?page=report&preg=see&res=2sap&rid=" . $_REQUEST["rid"]);
                        exit;
                    } else {
                        header("Location: ../../index.php?page=report&preg=see&res=2nap&rid=" . $_REQUEST["rid"]);
                        exit;
                    }

                } else {
                    header("Location: ../../index.php?page=report&preg=see&res=2nmts&rid=" . $_REQUEST["rid"]);
                    exit;
                }
            } else {
                header("Location: ../../index.php?page=report&preg=see&res=2nm&rid=" . $_REQUEST["rid"]);
                exit;
            }
        } else {
            header("Location: ../../index.php?p=report&preg=see&res=2naacr&rid=" . $_REQUEST["rid"]);
            exit;
        }
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

if (isset( $_REQUEST["reports-answer-edit"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../index.php?page=report&res=2nrid");
        exit;
    }
    if (empty($_REQUEST["ansid"])){
        header("Location: ../../index.php?page=report&rid=" . $_REQUEST["rid"] . "&res=2nnas");
        exit;
    }
    if ($user->getId() == \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId") && $user->UserGroup()->getPermission("report_answer_edit")){
        header("Location: ../../index.php?page=report&preg=edit&ansid=".$_REQUEST["ansid"]);
        exit;
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

if (isset( $_REQUEST["reports-answer-delete"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../index.php?page=report&res=2nrid");
        exit;
    }
    if (empty($_REQUEST["ansid"])){
        header("Location: ../../index.php?page=report&rid=" . $_REQUEST["rid"] . "&res=2nnas");
        exit;
    }
    if ($user->getId() == \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId") && $user->UserGroup()->getPermission("report_answer_edit")){
        $result = \Guards\ReportAgent::DeleteAnswer($_REQUEST["ansid"]);
        if ($result === TRUE){
            header("Location: ../../index.php?page=report&preg=see&rid=".$_REQUEST["rid"]."&res=2sda");
            exit;
        } else {
            header("Location: ../../index.php?page=report&preg=see&rid=".$_REQUEST["rid"]."&res=2nda");
            exit;
        }
    } else {
        header("Location: ../../index.php?page=errors/notperm");
        exit;
    }
}

header("Location: ../../index.php?page=errors/forbidden");