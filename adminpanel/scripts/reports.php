<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

#Удаление жалоб(ы) из страницы просмотра таблицы жалоб.
if (isset($_POST["reports-table-delete-btn"])){
    if ($user->UserGroup()->getPermission("report_foreign_remove")){
        $backRequest = "Location: ../../adminpanel.php?p=reports";
        if (empty($_POST["reports-ids-for-delete"])){
            header($backRequest . "&res=5nsrd");
            exit;
        }
        $ids = explode(",", $_POST["reports-ids-for-delete"]);
        foreach($ids as $item){
            $report = \Guards\ReportAgent::GetReportParam($item, "theme");
            if (!\Guards\ReportAgent::DeleteReport($item)){
                \Guards\Logger::LogAction($user->getId(), " удалил(а) жалобу \"$report\" из списка жалоб.");
                header($backRequest . "&res=5ndsr");
                exit;
            }
        }
        header($backRequest . "&res=5sdsr");
        exit;
    }
}

# Просмотр жалобы.
if (isset($_POST["reports-see-btn"])){
    if ($user->UserGroup()->getPermission("report_talking")){
        if (empty($_GET["rid"])){
            header("Location: ../../adminpanel.php?p=reports&res=5nrid");
            exit;
        }
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=". $_GET["rid"]);
        exit;
    } else { header("Location: ../../adminpanel.php?p=reports&res=1"); exit; }
}

# Отметить ответ решением проблемы.
if (isset($_POST["reports-answer-accept"])){
    if ($user->UserGroup()->getPermission("report_close")){
        if (empty($_GET["rid"])){
            header("Location: ../../adminpanel.php?p=reports&res=5nrid");
            exit;
        }
        if (empty($_GET["ansid"])){
            header("Location: ../../adminpanel.php?p=reports&res=5nnas");
            exit;
        }
        $result = \Guards\ReportAgent::SetAsSolveOfReportTheAnswer($_GET["rid"], $_GET["ansid"]);
        if ($result === True) {
            $reportName = \Guards\ReportAgent::GetReportParam($_GET["rid"], "theme");
            \Guards\Logger::LogAction($user->getId(), " отметил(а) ответ решением проблемы \"$reportName\".");
            $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetReportParam($_GET["rid"], "author"));
            $ntf->createNotify("15", $user->getId(), $_GET["rid"]);
            $list = \Guards\ReportAgent::GetReport($_GET["rid"])->getAddedToDiscuse();
            foreach ($list as $atdUser){
                \Users\UserAgent::GetUser($atdUser)->Notifications()->createNotify(20, $user->getId(),
                    $_GET["rid"] . "," . \Guards\ReportAgent::GetReportParam($_GET["rid"], "author"));
            }
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5scr");
            exit;
        } elseif ($result == 30) {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5nta");
            exit;
        } elseif ($result == 29){
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5ntr");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5ncr");
            exit;
        }
    } else { header("Location: ../../adminpanel.php?p=reports&res=1"); exit; }
}

#Перенаправление на редактирование конкретного ответа (не жалобы)
if (isset($_POST["reports-answer-edit"])){
    if (empty($_GET["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }
    if (empty($_GET["ansid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nnas");
        exit;
    }
    if (\Guards\ReportAgent::GetReportParam($_GET["rid"], "status") != 2) {
        if (($user->getId() == \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "authorId") && $user->UserGroup()->getPermission("report_answer_edit")) ||
            ($user->getId() != \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "authorId") && $user->UserGroup()->getPermission("report_foreign_answer_edit"))){
                header("Location: ../../adminpanel.php?p=reports&reqtype=edit&ansid=" . $_GET["ansid"]);
                exit;
            } else {
                header("Location: ../../adminpanel.php?p=reports&res=1");
                exit;
            }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5naacr");
        exit;
    }
}

#Перенаправление на редактирование жалобы
if (isset($_POST["reports-report-edit"])){
    if (empty($_GET["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }

    if (\Guards\ReportAgent::GetReportParam($_GET["rid"], "status") != 2) {
        if (($user->getId() == \Guards\ReportAgent::GetReportParam($_GET["rid"], "author") && $user->UserGroup()->getPermission("report_edit")) ||
            ($user->getId() != \Guards\ReportAgent::GetReportParam($_GET["rid"], "author") && $user->UserGroup()->getPermission("report_foreign_edit"))){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&rid=" . $_GET["rid"]);
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=reports&res=1");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5naacr");
        exit;
    }
}

# Удаление жалобы
if (isset($_POST["reports-report-delete"])){
    if (empty($_GET["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }

    if (\Guards\ReportAgent::GetReportParam($_GET["rid"], "status") != 2) {
        if (($user->getId() == \Guards\ReportAgent::GetReportParam($_GET["rid"], "author") && $user->UserGroup()->getPermission("report_remove")) ||
            ($user->getId() != \Guards\ReportAgent::GetReportParam($_GET["rid"], "author") && $user->UserGroup()->getPermission("report_foreign_remove"))){
            $reportName = \Guards\ReportAgent::GetReportParam($_GET["rid"], "theme");
            $result = \Guards\ReportAgent::DeleteReport($_GET["rid"]);
            if ($result === TRUE){
                if ($user->getId() != \Guards\ReportAgent::GetReportParam($_GET["rid"], "author")) {
                    $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetReportParam($_GET["rid"], "author"));
                    $ntf->createNotify("11", $user->getId(), \Guards\ReportAgent::GetReportParam($_GET["rid"], "short_message"));
                }

                \Guards\Logger::LogAction($user->getId(), " удалил(а) жалобу \"$reportName\".");
                header("Location: ../../adminpanel.php?p=reports&res=5sdr");
                exit;
            } elseif ($result == 29){
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5ntr");
                exit;
            } else {
                header("Location: ../../adminpanel.php?p=reports&res=5ndr");
                exit;
            }
        } else {
            header("Location: ../../adminpanel.php?p=reports&res=1");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5naacr");
        exit;
    }
}

#Удаление ответа к репорту.
if (isset($_POST["reports-answer-delete"])){
    if (empty($_GET["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }
    if (empty($_GET["ansid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nnas");
        exit;
    }

    $a = false;
    if ($user->getId() == \Guards\Report::GetAnswerParam($_GET["ansid"], "authorId")){
        if ($user->UserGroup()->getPermission("report_answer_edit")) $a = true;
    } elseif ($user->UserGroup()->getPermission("report_foreign_answer_edit")) $a = true;
    if (\Guards\Report::GetReportParam($_GET["rid"], "status") != 2) {
        if ($a === true) {
            $reportName = \Guards\ReportAgent::GetReportParam($user->getId(), "theme");
            $result = \Guards\ReportAgent::DeleteAnswer($_GET["ansid"]);
            if ($result === TRUE) {
                \Guards\Logger::LogAction($user->getId(),  " удалил(а) ответ к жалобе \"$reportName\".");
                $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "authorId"));
                $ntf->createNotify("16", $user->getId(), $_GET["rid"]);
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5sda");
                exit;
            } elseif ($result == 31) {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5ncds");
                exit;
            } elseif ($result == 30) {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5nta");
                exit;
            } else {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5nda");
                exit;
            }
        } else {
            header("Location: ../../adminpanel.php?p=reports&res=1");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5naacr");
        exit;
    }
}

# Опубликовать ответ к реппорту (не решение, а именно ответ!)
if (isset($_POST["reports-answer-send"])){
    if ($user->UserGroup()->getPermission("report_talking")){
        if (empty($_GET["rid"])){
            header("Location: ../../adminpanel.php?p=reports&res=5nrid");
            exit;
        }
        if (\Guards\Report::GetReportParam($_GET["rid"], "status") != 2) {
            if (empty($_POST["reports-answer-text"])) {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5nmt");
                exit;
            }
            if (strlen($_POST["reports-answer-text"]) < 4) {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5ntsm");
                exit;
            }
            $result = \Guards\ReportAgent::CreateAnswer($user->getId(), $_POST["reports-answer-text"], $_GET["rid"]);
            if ($result === TRUE){
                \Guards\ReportAgent::ChangeReportParam($_GET["rid"], "viewed", 1);
                $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetReportParam($_GET["rid"], "author"));
                $ntf->createNotify("5", $user->getId(), $_GET["rid"]);
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5sad");
                exit;
            } elseif ($result == 29){
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5ntr");
                exit;
            } else {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5nad");
                exit;
            }
        } else {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5naacr");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=1");
        exit;
    }
}

# Изменить ответ.
if (isset($_POST["report-edit-answer-edit"])){
    if (empty($_GET["ansid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nna");
        exit;
    }

    if (($user->getId() == \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "authorId") && $user->UserGroup()->getPermission("report_answer_edit") ||
        ($user->getId() != \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "authorId") && $user->UserGroup()->getPermission("report_foreign_answer_edit")))){
        if (empty($_POST["reports-edit-message-text"])){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&ansid=" . $_GET["ansid"]. "&res=5nmt");
            exit;
        }
        if (strlen($_POST["reports-edit-message-text"]) < 4){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&ansid=" . $_GET["ansid"] . "&res=5ntsm");
            exit;
        }
        $result = \Guards\ReportAgent::ChangeAnswerText($_GET["ansid"], $_POST["reports-edit-message-text"], $_POST["reports-edit-reason"], $user->getId());
        if ($result === TRUE){
            $reportName = \Guards\ReportAgent::GetReportParam($_GET["rid"], "theme");
            \Guards\Logger::LogAction($user->getId(), " именил(а) ответ в теме \"$reportName\".");
            $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "authorId"));
            $ntf->createNotify("17", $user->getId(), $_GET["rid"]);
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "reportId") . "&res=5sea");
            exit;
        } elseif ($result == 30){
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "reportId") . "&res=5nta");
            exit;
        } elseif ($result == 31){
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "reportId") . "&res=5ncds");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "reportId") . "&res=5nea");
            exit;
        }
    }
}

if (isset($_POST["reports-edit-reports-edit"])){
    if (empty($_GET["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }

    if (($user->getId() == \Guards\ReportAgent::GetReportParam($_GET["rid"], "author") && $user->UserGroup()->getPermission("report_edit") ||
        ($user->getId() != \Guards\ReportAgent::GetReportParam($_GET["rid"], "author") && $user->UserGroup()->getPermission("report_foreign_edit")))){
        if (empty($_POST["reports-edit-message-text"])){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&rid=" . $_GET["rid"]. "&res=5nmt");
            exit;
        }
        if (strlen($_POST["reports-edit-message-text"]) < 4){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&rid=" . $_GET["rid"] . "&res=5ntsm");
            exit;
        }

        $result = True;
        $o = 0;
        while($result === True){
            $o++;
            switch($o) {
                case 1:
                    $result = \Guards\ReportAgent::ChangeReportParam($_GET["rid"], "short_message", $_POST["reports-edit-shortmessage"]);
                    break;
                case 2:
                    $result = \Guards\ReportAgent::ChangeReportParam($_GET["rid"], "message", $_POST["reports-edit-message-text"]);
                    break;
            }
            if ($o == 3) break;
        }

        if ($result === TRUE){
            $reportName = \Guards\ReportAgent::GetReportParam($_GET["rid"], "theme");
            \Guards\Logger::LogAction($user->getId(), " изменил(а) текст жалобы \"$reportName\".");
            $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetReportParam($_GET["rid"], "author"));
            $ntf->createNotify("10", $user->getId(), $_GET["rid"]);
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=". $_GET["rid"] . "&res=5ser");
            exit;
        } elseif ($result == 29){
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5ntr");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_GET["rid"] . "&res=5ner");
            exit;
        }
    }
}

header("Location: ../../adminpanel.php?p=forbidden");