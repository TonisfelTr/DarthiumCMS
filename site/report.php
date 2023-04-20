<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 05.06.2018
 * Time: 22:42
 *
 * Так, окей, на это уже можно смотреть с улыбкой и счастьем в душе.
 * Вроде как.
 */

if (!defined("TT_Index")){ header("index.php?page=errors/forbidden"); exit; }
if (!isset($user))
{
    include "errors/nonauth.php";
    exit;
}

$pageName = \Engine\LanguageManager::GetTranslation("reports_site_panel.panel_name");
include_once "site/uploader.php";

function constructReasonsSelector()
{
    if (!$reasons = file_get_contents("engine/config/represes.sfc", FILE_USE_INCLUDE_PATH)) return false;
    $reasons = explode("\n", $reasons);
    $result = "";
    for ($y = 0; $y < count($reasons); $y++) {
        $result .= "<option value=\"$y\">" . trim($reasons[$y]) . "</option>\n";
    }
    return $result;
}

include_once \Engine\Engine::ConstructTemplatePath("reportscript", "report", "js");
$reportJS = getBrick();
$main = str_replace("{REPORT_PAGE:JS}", $reportJS, $main);

$uploaderResponse = "";
if (!empty($_GET["res"])){
    switch ($_GET["res"]) {
        case "2sda":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.your_answer_was_removed_success") . "</div>";
            break;
        case "2nda":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.your_answer_was_removed_failed") . "</div>";
            break;
        case "2ses":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.your_answer_was_edit_success") . "</div>";
            break;
        case "2nes":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.your_answer_was_edit_failed") . "</div>";
            break;
        case "2sap":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.your_answer_was_created_success") . "</div>";
            break;
        case "2nap":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.your_answer_was_create_failed") . "</div>";
            break;
        case "2nmts":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.answer_too_short") . "</div>";
            break;
        case "2nm":
            $uploaderResponse = "<div class=\"alert alert-warning\"><span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.not_entered_text") . "</div>";
            break;
        case "2nidfe":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.not_setted_id") . "</div>";
            break;
        case "2nnas":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.not_setted_id_answer") . "</div>";
            break;
        case "2nmea":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.denied_edit_answer_and_report") . "</div>";
            break;
        case "2nscm":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.change_report_failed") . "</div>";
            break;
        case "2sscm":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicons glyphicons-info-sign\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.report_text_edited_success") . "</div>";
            break;
        case "2nnia":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-stop-sign\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.not_permitted_to_view") . "</div>";
            break;
        case "2nrid":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.not_setted_id_report") . "</div>";
            break;
        case "2nst":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.not_setted_category") . "</div>";
            break;
        case "2ncr":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.create_report_failed") . "</div>";
            break;
        case "2nnsm":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.no_name_report") . "</div>";
            break;
        case "2nnm":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.no_report_problem") . "</div>";
            break;
        case "2naacr":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.answers_create_failed_in_close") . "</div>";
            break;
        case "2scr":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.report_create_success") . "</div>";
            break;
    }
}
echo $uploaderResponse;
if (empty($_GET["preg"])) {
    $reportList = \Guards\ReportAgent::GetReportsListByAuthor($user->getId(), (!empty($_GET["rp"])) ? $_GET["rp"] : 1);
    $reportCount = count($reportList);
    $allReportsCount = \Guards\ReportAgent::GetReportsCountWithUser($user->getId());
    $reportsTable = "";
    if ($reportCount == 0)
        $reportsTable = "<tr>
                            <td style=\"text-align: center;\" colspan=\"6\"><span class=\"glyphicon glyphicon-info-sign\"></span> " . \Engine\LanguageManager::GetTranslation("reports_site_panel.no_your_reports") . "</td>
                         </tr>";
    else {
        foreach ($reportList as $reportId){
            $report = new \Guards\Report($reportId["id"]);

            $reportSolveAnswerAuthor = $report->isClosed() ? \Users\UserAgent::GetUserNick(\Guards\ReportAgent::GetAnswerParam($report->getAnswerId(), "authorId")) : \Engine\LanguageManager::GetTranslation("reports_site_panel.no_solve");
            $reportCloseDate = $report->isClosed() ? \Engine\Engine::DateFormatToRead($report->getCloseDate()) : \Engine\LanguageManager::GetTranslation("reports_site_panel.not_closed");
            $reportsTable .= "<tr>";
            $reportsTable .= "<td>" . $report->getStatus() . "</td>";
            $reportsTable .= "<td>" . htmlentities($report->getTheme()) . "</td>";
            $reportsTable .= "<td><a href=\"?page=report&preg=see&rid=$reportId\">" . htmlentities($report->getShortMessage()) . "</a></td>";
            $reportsTable .= "<td>" . \Engine\Engine::DateFormatToRead($report->getCreateDate()) . "</td>";
            $reportsTable .= "<td>$reportSolveAnswerAuthor</td>";
            $reportsTable .= "<td>$reportCloseDate</td>";
            $reportsTable .= "</tr>";
        }
    }

    include_once \Engine\Engine::ConstructTemplatePath("reportmain", "report");
    $reportMainBlock = getBrick();

    $reportMainBlock = str_replace_once("{REPORTS_PAGE:TABLE}", $reportsTable, $reportMainBlock);

    $reportTablePageBtns = "";
    for ($i = 0; $i < $allReportsCount / 20; $i++){
        $rp = $i +1;
        $reportTablePageBtns .= "<a class=\"btn btn-default\" href=\"?page=report&rp=$rp\">$rp</a>";
    }
    $reportMainBlock = str_replace_once("{REPORT_PAGE:TABLE_PAGE_BTNS}", $reportTablePageBtns, $reportMainBlock);

    echo $reportMainBlock;
} else {
    switch($_GET["preg"]){
        case "add": {
            include_once \Engine\Engine::ConstructTemplatePath("reportcreate", "report");
            $reportCreateBlock = getBrick();

            $reportCreateBlock = str_replace_once("{REPORT_PAGE:SUBJECTS_SELECTOR}", constructReasonsSelector(), $reportCreateBlock);
            echo $reportCreateBlock;
            break;
        }
        case "see": {
            if (empty($_GET["rid"])) {
                header("Location: ../index.php?page=report&res=2nrid");
                exit;
            }

            $report = new \Guards\Report($_GET["rid"]);

            if (!$report->isAdded($user->getId()) && $report->ReportAuthor() != $user) {
                header("Location: ../index.php?page=report&res=2nnia");
                exit;
            }

            include_once \Engine\Engine::ConstructTemplatePath("reportsee", "report");
            $reportSeeBlock = getBrick();

            $reportSeeBlock = str_replace_once("{REPORT_PAGE:SHORT_MESSAGE}", htmlentities($report->getShortMessage()), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:SUBJECT}", htmlentities($report->getTheme()), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:CREATE_DATE}", \Engine\Engine::DateFormatToRead($report->getCreateDate()), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_AVATAR}", $report->ReportAuthor()->getAvatar(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_NICKNAME}", $report->ReportAuthor()->getNickname(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_GROUP_ID}", $report->ReportAuthor()->getUserGroup()->getId(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_GROUP_COLOR}", $report->ReportAuthor()->getUserGroup()->getColor(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_GROUP_NAME}", $report->ReportAuthor()->getUserGroup()->getName(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_REALNAME}", ($report->ReportAuthor()->getRealName() != '') ? \Engine\LanguageManager::GetTranslation("reports_site_panel.name") . " " . htmlentities($report->ReportAuthor()->getRealName()) . "<br>" : "", $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_FROM}", ($report->ReportAuthor()->getFrom() != '') ? \Engine\LanguageManager::GetTranslation("reports_site_panel.from") . " " . htmlentities($report->ReportAuthor()->getFrom()) . "<br>" : "", $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:VK}", ($report->ReportAuthor()->getVK() != '' && $report->ReportAuthor()->IsVKPublic()) ? "VK: <a href=\"http://vk.com/" . htmlentities($report->ReportAuthor()->getVK()) . "\">" . \Engine\LanguageManager::GetTranslation("reports_site_panel.go_to") . "</a><br>" : "", $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:REPORT_TEXT}", \Engine\Engine::MakeUnactiveCodeWords(nl2br(\Engine\Engine::CompileBBCode($report->getMessage()))), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_SIGNATURE}", nl2br(\Engine\Engine::CompileBBCode($report->ReportAuthor()->getSignature())), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:REPORT_STATUS}", $report->getStatus(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_ID}", $report->getId(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:BTNS_PANEL}", "", $reportSeeBlock);
            if (($report->ReportAuthor() == $user || $user->getUserGroup()->getPermission("report_foreign_edit")) && !$report->isClosed()) {
                include_once \Engine\Engine::ConstructTemplatePath("reportpanelbtn", "report");
                $reportBtnBlock = getBrick();
                $reportBtnBlock = str_replace("{REPORT_PAGE:REPORT_ID}", $report->getId(), $reportBtnBlock);

                $reportSeeBlock = str_replace_once("{REPORT_PAGE:BTNS_PANEL}", $reportBtnBlock, $reportSeeBlock);
            } else
                $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_GROUP_NAME}", "", $reportSeeBlock);

            if (!$report->isClosed()){
                include_once \Engine\Engine::ConstructTemplatePath("reportansweraddform", "report");
                $reportAddAnswerForm = getBrick();

                $reportSeeBlock = str_replace_once("{REPORT_PAGE:ANSWER_ADD_FORM}", $reportAddAnswerForm, $reportSeeBlock);
                $reportSeeBlock = str_replace_once("{REPORT_PAGE:SOLVE_ANSWER}", "", $reportSeeBlock);
            } else {
                include_once \Engine\Engine::ConstructTemplatePath("reportsolveanswer", "report");
                $reportSolveAnswer = getBrick();

                $answer = new \Guards\ReportAnswer($report->getAnswerId());
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_AVATAR}", $answer->getAuthor()->getAvatar(), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_NICKNAME}", $answer->getAuthor()->getNickname(), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_GROUP_COLOR}", $answer->getAuthor()->getUserGroup()->getColor(), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_GROUP_NAME}", $answer->getAuthor()->getUserGroup()->getName(), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_REALNAME}", ($answer->getAuthor()->getRealName() != '') ? \Engine\LanguageManager::GetTranslation("reports_site_panel.name") . " " . htmlentities($answer->getAuthor()->getRealName()) . "<br>" : "", $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUHTOR_FROM}", ($answer->getAuthor()->getFrom() != '') ? \Engine\LanguageManager::GetTranslation("reports_site_panel.from") . " " . htmlentities($answer->getAuthor()->getFrom()) . "<br>" : "", $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_VK}", ($answer->getAuthor()->getVK() != '' && $answer->getAuthor()->IsVKPublic()) ? "VK: <a href=\"http://vk.com/" . htmlentities($answer->getAuthor()->getVK()) . "\">" . \Engine\LanguageManager::GetTranslation("reports_site_panel.go_to") . "</a><br>" : "", $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_CREATE_DATE}", \Engine\Engine::DateFormatToRead($answer->getCreateDate()), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_TEXT}", nl2br(trim(\Engine\Engine::CompileBBCode($answer->getMessage()))), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_SIGNATURE}", \Engine\Engine::CompileBBCode($answer->getAuthor()->getSignature()), $reportSolveAnswer);
                $lastEditInfoBlock = "";
                if ($answer->getEditDate() != '') {
                    $lastEditInfoBlock = "<br><em>" . \Engine\LanguageManager::GetTranslation("reports_site_panel.last_time_edited") . " " . $answer->getLastEditor()->getNickname() . ", " . \Engine\Engine::DatetimeFormatToRead($answer->getEditDate());
                    if ($answer->getEditReason() != '')
                        $lastEditInfoBlock .= ", " . \Engine\LanguageManager::GetTranslation("reports_site_panel.by_reason") . " " . htmlentities($answer->getEditReason());
                    else
                        $lastEditInfoBlock .= ".";
                    $lastEditInfoBlock .= "</em>";
                }
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_LAST_EDITED_INFO}", $lastEditInfoBlock, $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:REPORT_CLOSE_DATETIME}", \Engine\Engine::DateFormatToRead($report->getCloseDate()) . ".", $reportSolveAnswer);

                $reportSeeBlock = str_replace_once("{REPORT_PAGE:ANSWER_ADD_FORM}", "", $reportSeeBlock);
                $reportSeeBlock = str_replace_once("{REPORT_PAGE:SOLVE_ANSWER}", $reportSolveAnswer, $reportSeeBlock);
            }

            $ansCount = count($report->getAnswersList((empty($_GET["pn"])) ? 1 : $_GET["pn"]));
            if ($ansCount > 0) {
                $answerList = $report->getAnswersList((empty($_GET["pn"])) ? 1 : $_GET["pn"]);
                $reportAnswers = "";
                foreach ($answerList as $answer) {
                    $answer = new \Guards\ReportAnswer($answer["id"]);

                    include \Engine\Engine::ConstructTemplatePath("reportanswer", "report");
                    $reportAnswer = getBrick();

                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_AVATAR}", $answer->getAuthor()->getAvatar() ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_NICKNAME}", $answer->getAuthor()->getNickname() ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_GROUP_ID}", $answer->getAuthor()->getUserGroup()->getId(), $reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_GROUP_COLOR}", $answer->getAuthor()->getUserGroup()->getColor() ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_GROUP_NAME}", $answer->getAuthor()->getUserGroup()->getName() ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_REALNAME}", ($answer->getAuthor()->getRealName() != '') ? \Engine\LanguageManager::GetTranslation("reports_site_panel.name") . " " . htmlentities($answer->getAuthor()->getRealName()) . "<br>" : "" ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_FROM}", ($answer->getAuthor()->getFrom() != '') ? \Engine\LanguageManager::GetTranslation("reports_site_panel.from") . " " . htmlentities($answer->getAuthor()->getFrom()) . "<br>" : "" ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_VK}", ($answer->getAuthor()->getVK() != '' && $answer->getAuthor()->IsVKPublic()) ? "VK: <a href=\"http://vk.com/" . htmlentities($answer->getAuthor()->getVK()) . "\">" . \Engine\LanguageManager::GetTranslation("reports_site_panel.go_to") . "</a><br>" : "" ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:ANSWER_CREATE_DATETIME}", \Engine\Engine::DateFormatToRead($answer->getCreateDate()) ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:ANSWER_TEXT}", nl2br(trim(\Engine\Engine::CompileBBCode($answer->getMessage()))) ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_SIGNATURE}", nl2br(\Engine\Engine::CompileBBCode($answer->getAuthor()->getSignature())) ,$reportAnswer);
                    $lastEditInfoAnswerBlock = "";
                    if ($answer->getEditDate() != ''){
                        $lastEditInfoAnswerBlock = "<br><em>" . \Engine\LanguageManager::GetTranslation("reports_site_panel.last_time_edited") . " " . $answer->getLastEditor()->getNickname() . ", " . \Engine\Engine::DatetimeFormatToRead($answer->getEditDate());
                        if ($answer->getEditReason() != '')
                            $lastEditInfoAnswerBlock .= ", " . \Engine\LanguageManager::GetTranslation("reports_site_panel.by_reason") . " " . htmlentities($answer->getEditReason());
                        else
                            $lastEditInfoAnswerBlock .= ".";
                        $lastEditInfoAnswerBlock .= "</em>";
                    }
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_LAST_EDIT_DATETIME}", $lastEditInfoAnswerBlock ,$reportAnswer);

                    $reportAnswerBtn = "";
                    if ($answer->getAuthor() == $user && !$report->isClosed()) {
                        include \Engine\Engine::ConstructTemplatePath("reportpanelanswerbtn", "report");
                        $reportAnswerBtn = getBrick();

                        $reportAnswerBtn = str_replace("{REPORT_PAGE:ID}", $report->getId(), $reportAnswerBtn);
                        $reportAnswerBtn = str_replace("{REPORT_PAGE:ANSWER_ID}", $answer->getAnswerId(), $reportAnswerBtn);
                    }
                    $reportAnswer = str_replace_once("{REPORT_PAGE:ANSWER_MANAGE_BTNS}", $reportAnswerBtn ,$reportAnswer);

                    $reportAnswers .= $reportAnswer;
                }
            }
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:ANSWERS}", $reportAnswers, $reportSeeBlock);
            echo $reportSeeBlock;
            break;
        }
        case "edit": {
            if (empty($_GET["rid"]) && empty($_GET["ansid"])) {
                header("Location: ../index.php?page=report&res=2nidfe");
                exit;
            } elseif (!empty($_GET["rid"]) && !empty($_GET["ansid"])){
                header("Location: ../index.php?page=report&res=2nmea");
                exit;
            }

            if (!empty($_GET["rid"])) {
                if (($user->getUserGroup()->getPermission("report_edit") && $user->getId() == \Guards\ReportAgent::GetReportParam($_GET["rid"], "author"))
                    || $user->getUserGroup()->getPermission("report_foreign_edit")
                ) {
                    $report = new \Guards\Report($_GET["rid"]);
                    $message = htmlentities($report->getMessage());
                    $suffixFormaction = "rid=" . $report->getId();
                    $nameBtnEdit = "report-edit-message-edit";
                }
            }

            if (!empty($_GET["ansid"])) {
                if ($user->getId() == \Guards\ReportAgent::GetAnswerParam($_GET["ansid"], "authorId")) {
                    $answer = new \Guards\ReportAnswer($_GET["ansid"]);
                    $message = htmlentities($answer->getMessage());
                    $suffixFormaction = "ansid=" . $answer->getAnswerId();
                    $nameBtnEdit = "report-edit-answer-edit";
                }
            }

            $reportEditFormLabel = (!empty($_GET["rid"])) ? \Engine\LanguageManager::GetTranslation("reports_site_panel.of_report") : \Engine\LanguageManager::GetTranslation("reports_site_panel.of_answer");
            if (!empty($_GET["rid"])) {
                $reportEditBasicInfo = "<div class=\"input-group\">
                                            <div class=\"input-group-addon\">" . \Engine\LanguageManager::GetTranslation("reports_site_panel.name_report") . "</div>
                                            <div class=\"form-control\">" . htmlentities($report->getShortMessage()) . "</div>
                                        </div>";
            } else {
                $reportEditBasicInfo = "<div class=\"input-group\">
                                            <div class=\"input-group-addon\">" . \Engine\LanguageManager::GetTranslation("reports_site_panel.edit_reason") . "</div>
                                            <input type=\"text\" name=\"report-edit-reason\" class=\"form-control\">
                                        </div>";
            }

            include_once \Engine\Engine::ConstructTemplatePath("reportedit", "report");
            $reportEditBlock = getBrick();

            $reportEditBlock = str_replace_once("{REPORT_PAGE:EDIT_OF_LABEL}", $reportEditFormLabel, $reportEditBlock);
            $reportEditBlock = str_replace_once("{REPORT_PAGE:EDIT_BASIC_INFO}", $reportEditBasicInfo, $reportEditBlock);
            $reportEditBlock = str_replace_once("{REPORT_PAGE:EDIT_SUFFIX_FORMACTION}", $suffixFormaction, $reportEditBlock);
            $reportEditBlock = str_replace_once("{REPORT_PAGE:EDIT_BTN_NAME}", $nameBtnEdit, $reportEditBlock);

            echo \Engine\Engine::MakeUnactiveCodeWords($reportEditBlock);
            break;
        }
        default: {
            header("Location: ../../index.php?page=errors/notperm");
            exit;
        }
    }
}