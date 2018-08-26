<?php

require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

#Удаление жалоб(ы) из страницы просмотра таблицы жалоб.
if (isset($_REQUEST["reports-table-delete-btn"])){
    if ($user->UserGroup()->getPermission("report_foreign_remove")){
        $backRequest = "Location: ../../adminpanel.php?p=reports";
        if (empty($_POST["reports-ids-for-delete"])){
            header($backRequest . "&res=5nsrd");
            exit;
        }
        $ids = explode(",", $_POST["reports-ids-for-delete"]);
        foreach($ids as $item){
            if (!\Guards\ReportAgent::DeleteReport($item)){
                header($backRequest . "&res=5ndsr");
                exit;
            }
        }
        header($backRequest . "&res=5sdsr");
        exit;
    }
}

# Просмотр жалобы.
if (isset($_REQUEST["reports-see-btn"])){
    if ($user->UserGroup()->getPermission("report_talking")){
        if (empty($_REQUEST["rid"])){
            header("Location: ../../adminpanel.php?p=reports&res=5nrid");
            exit;
        }
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=". $_REQUEST["rid"]);
        exit;
    } else { header("Location: ../../adminpanel.php?p=reports&res=1"); exit; }
}

# Отметить ответ решением проблемы.
if (isset($_REQUEST["reports-answer-accept"])){
    if ($user->UserGroup()->getPermission("report_close")){
        if (empty($_REQUEST["rid"])){
            header("Location: ../../adminpanel.php?p=reports&res=5nrid");
            exit;
        }
        if (empty($_REQUEST["ansid"])){
            header("Location: ../../adminpanel.php?p=reports&res=5nnas");
            exit;
        }
        $result = \Guards\ReportAgent::SetAsSolveOfReportTheAnswer($_REQUEST["rid"], $_REQUEST["ansid"]);
        if ($result === True) {
            $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author"));
            $ntf->createNotify("15", $user->getId(), $_REQUEST["rid"]);
            $list = \Guards\ReportAgent::GetReport($_REQUEST["rid"])->getAddedToDiscuse();
            foreach ($list as $atdUser){
                \Users\UserAgent::GetUser($atdUser)->Notifications()->createNotify(20, $user->getId(),
                    $_REQUEST["rid"] . "," . \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author"));
            }
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5scr");
            exit;
        } elseif ($result == 30) {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5nta");
            exit;
        } elseif ($result == 29){
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5ntr");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5ncr");
            exit;
        }
    } else { header("Location: ../../adminpanel.php?p=reports&res=1"); exit; }
}

#Перенаправление на редактирование конкретного ответа (не жалобы)
if (isset($_REQUEST["reports-answer-edit"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }
    if (empty($_REQUEST["ansid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nnas");
        exit;
    }
    if (\Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "status") != 2) {
        if (($user->getId() == \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId") && $user->UserGroup()->getPermission("report_answer_edit")) ||
            ($user->getId() != \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId") && $user->UserGroup()->getPermission("report_foreign_answer_edit"))){
                header("Location: ../../adminpanel.php?p=reports&reqtype=edit&ansid=" . $_REQUEST["ansid"]);
                exit;
            } else {
                header("Location: ../../adminpanel.php?p=reports&res=1");
                exit;
            }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5naacr");
        exit;
    }
}

#Перенаправление на редактирование жалобы
if (isset($_REQUEST["reports-reports-edit"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }

    if (\Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "status") != 2) {
        if (($user->getId() == \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author") && $user->UserGroup()->getPermission("report_edit")) ||
            ($user->getId() != \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author") && $user->UserGroup()->getPermission("report_foreign_edit"))){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&rid=" . $_REQUEST["rid"]);
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=reports&res=1");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5naacr");
        exit;
    }
}

# Удаление жалобы
if (isset($_REQUEST["reports-reports-delete"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }

    if (\Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "status") != 2) {
        if (($user->getId() == \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author") && $user->UserGroup()->getPermission("report_remove")) ||
            ($user->getId() != \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author") && $user->UserGroup()->getPermission("report_foreign_remove"))){
            $result = \Guards\ReportAgent::DeleteReport($_REQUEST["rid"]);
            if ($result === TRUE){
                if ($user->getId() != \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author")) {
                    $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author"));
                    $ntf->createNotify("11", $user->getId(), \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "short_message"));
                }
                header("Location: ../../adminpanel.php?p=reports&res=5sdr");
                exit;
            } elseif ($result == 29){
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5ntr");
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
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5naacr");
        exit;
    }
}

#Удаление ответа к репорту.
if (isset($_REQUEST["reports-answer-delete"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }
    if (empty($_REQUEST["ansid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nnas");
        exit;
    }

    $a = false;
    if ($user->getId() == \Guards\Report::GetAnswerParam($_REQUEST["ansid"], "authorId")){
        if ($user->UserGroup()->getPermission("report_answer_edit")) $a = true;
    } elseif ($user->UserGroup()->getPermission("report_foreign_answer_edit")) $a = true;
    if (\Guards\Report::GetReportParam($_REQUEST["rid"], "status") != 2) {
        if ($a === true) {
            $result = \Guards\ReportAgent::DeleteAnswer($_REQUEST["ansid"]);
            if ($result === TRUE) {
                $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId"));
                $ntf->createNotify("16", $user->getId(), $_REQUEST["rid"]);
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5sda");
                exit;
            } elseif ($result == 31) {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5ncds");
                exit;
            } elseif ($result == 30) {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5nta");
                exit;
            } else {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5nda");
                exit;
            }
        } else {
            header("Location: ../../adminpanel.php?p=reports&res=1");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5naacr");
        exit;
    }
}

# Опубликовать ответ к реппорту (не решение, а именно ответ!)
if (isset($_REQUEST["reports-answer-send"])){
    if ($user->UserGroup()->getPermission("report_talking")){
        if (empty($_REQUEST["rid"])){
            header("Location: ../../adminpanel.php?p=reports&res=5nrid");
            exit;
        }
        if (\Guards\Report::GetReportParam($_REQUEST["rid"], "status") != 2) {
            if (empty($_REQUEST["reports-answer-text"])) {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5nmt");
                exit;
            }
            if (strlen($_REQUEST["reports-answer-text"]) < 4) {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5ntsm");
                exit;
            }
            $result = \Guards\ReportAgent::CreateAnswer($user->getId(), $_REQUEST["reports-answer-text"], $_REQUEST["rid"]);
            if ($result === TRUE){
                \Guards\ReportAgent::ChangeReportParam($_REQUEST["rid"], "viewed", 1);
                $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author"));
                $ntf->createNotify("5", $user->getId(), $_REQUEST["rid"]);
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5sad");
                exit;
            } elseif ($result == 29){
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5ntr");
                exit;
            } else {
                header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5nad");
                exit;
            }
        } else {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5naacr");
            exit;
        }
    } else {
        header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=1");
        exit;
    }
}

# Изменить ответ.
if (isset($_REQUEST["reports-edit-answer-edit"])){
    if (empty($_REQUEST["ansid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nna");
        exit;
    }

    if (($user->getId() == \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId") && $user->UserGroup()->getPermission("report_answer_edit") ||
        ($user->getId() != \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId") && $user->UserGroup()->getPermission("report_foreign_answer_edit")))){
        if (empty($_REQUEST["reports-edit-message-text"])){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&ansid=" . $_REQUEST["ansid"]. "&res=5nmt");
            exit;
        }
        if (strlen($_REQUEST["reports-edit-message-text"]) < 4){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&ansid=" . $_REQUEST["ansid"] . "&res=5ntsm");
            exit;
        }
        $result = \Guards\ReportAgent::ChangeAnswerText($_REQUEST["ansid"], $_REQUEST["reports-edit-message-text"], $_REQUEST["reports-edit-reason"], $user->getId());
        if ($result === TRUE){
            $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "authorId"));
            $ntf->createNotify("17", $user->getId(), $_REQUEST["rid"]);
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "reportId") . "&res=5sea");
            exit;
        } elseif ($result == 30){
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "reportId") . "&res=5nta");
            exit;
        } elseif ($result == 31){
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "reportId") . "&res=5ncds");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . \Guards\ReportAgent::GetAnswerParam($_REQUEST["ansid"], "reportId") . "&res=5nea");
            exit;
        }
    }
}

if (isset($_REQUEST["reports-edit-reports-edit"])){
    if (empty($_REQUEST["rid"])){
        header("Location: ../../adminpanel.php?p=reports&res=5nrid");
        exit;
    }

    if (($user->getId() == \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author") && $user->UserGroup()->getPermission("report_edit") ||
        ($user->getId() != \Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author") && $user->UserGroup()->getPermission("report_foreign_edit")))){
        if (empty($_REQUEST["reports-edit-message-text"])){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&rid=" . $_REQUEST["rid"]. "&res=5nmt");
            exit;
        }
        if (strlen($_REQUEST["reports-edit-message-text"]) < 4){
            header("Location: ../../adminpanel.php?p=reports&reqtype=edit&rid=" . $_REQUEST["rid"] . "&res=5ntsm");
            exit;
        }

        $result = True;
        $o = 0;
        while($result === True){
            $o++;
            switch($o) {
                case 1:
                    $result = \Guards\ReportAgent::ChangeReportParam($_REQUEST["rid"], "short_message", $_REQUEST["reports-edit-shortmessage"]);
                    break;
                case 2:
                    $result = \Guards\ReportAgent::ChangeReportParam($_REQUEST["rid"], "message", $_REQUEST["reports-edit-message-text"]);
                    break;
            }
            if ($o == 3) break;
        }

        if ($result === TRUE){
            $ntf = new \Users\UserNotificator(\Guards\ReportAgent::GetReportParam($_REQUEST["rid"], "author"));
            $ntf->createNotify("10", $user->getId(), $_REQUEST["rid"]);
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=". $_REQUEST["rid"] . "&res=5ser");
            exit;
        } elseif ($result == 29){
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5ntr");
            exit;
        } else {
            header("Location: ../../adminpanel.php?p=reports&reqtype=discusion&rid=" . $_REQUEST["rid"] . "&res=5ner");
            exit;
        }
    }
}

header("Location: ../../adminpanel.php?p=forbidden");