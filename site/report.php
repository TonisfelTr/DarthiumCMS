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

$pageName = "Жалобы";
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

include_once \Engine\Engine::ConstructTemplatePath("script", "report", "js");
$reportJS = getBrick();
$main = str_replace("{REPORT_PAGE:JS}", $reportJS, $main);

$uploaderResponse = "";
if (!empty($_GET["res"])){
    switch ($_GET["res"]) {
        case "2sda":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> Ваш ответ к жалобе был успешно удалён.</div>";
            break;
        case "2nda":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Не удалось удалить Ваш ответ к жалобе.</div>";
            break;
        case "2ses":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> Ваш ответ к жалобе был отредактирован.</div>";
            break;
        case "2nes":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Не удалось отредактировать Ваш ответ.</div>";
            break;
        case "2sap":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> Ваш ответ к жалобе был опубликован.</div>";
            break;
        case "2nap":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Ваш ответ к жалобе опубликовать не удалось.</div>";
            break;
        case "2nmts":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> Длина ответа должна быть больше 4 символов.</div>";
            break;
        case "2nm":
            $uploaderResponse = "<div class=\"alert alert-warning\"><span class=\"glyphicon glyphicon-warning-sign\"></span> Вы не ввели текст Вашего ответа.</div>";
            break;
        case "2nidfe":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> Не указан уникальный номер ответа или жалобы для редактирования.</div>";
            break;
        case "2nnas":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-warning-sign\"></span> Не указан уникальный номер ответа для данного действия.</div>";
            break;
        case "2nmea":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Нельзя использовать для редактирования жалобы и ответа одно окно одновременно.</div>";
            break;
        case "2nscm":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Не получилось изменить текст жалобы.</div>";
            break;
        case "2sscm":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicons glyphicons-info-sign\"></span> Текст жалобы был изменён! Обратите внимание, что статус вашей жалобы не изменился.
            Администрация в любой момент может перепроверить Вашу жалобу.</div>";
            break;
        case "2nnia":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-stop-sign\"></span> У вас недостаточно привелегий для просмотра данной жалобы.</div>";
            break;
        case "2nrid":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> Не указан уникальный номер жалобы для данного действия.</div>";
            break;
        case "2nst":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> Вы не указали тему.</div>";
            break;
        case "2ncr":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> Не удалось создать жалобу.</div>";
            break;
        case "2nnsm":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> Вы не назвали жалобу.</div>";
            break;
        case "2nnm":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> Вы не написали подробного содержания жалобы.</div>";
            break;
        case "2naacr":
            $uploaderResponse = "<div class=\"alert alert-danger\"><span class=\"glyphicons glyphicons-delete\"></span> Вы не можете добавлять ответы к закрытой жалобе.</div>";
            break;
        case "2scr":
            $uploaderResponse = "<div class=\"alert alert-success\"><span class=\"glyphicon glyphicon-ok\"></span> Вы успешно создали жалобу. В течение некоторого времени, она
            будет рассмотрена Администрацией и будет принято решение. Спасибо за Ваш отзыв!</div>";
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
                            <td style=\"text-align: center;\" colspan=\"6\"><span class=\"glyphicon glyphicon-info-sign\"></span> Не найденно ни одной Вашей жалобы.</td>
                         </tr>";
    else {
        for ($i = 0; $i <= $reportCount-1; $i++) {
            $report = new \Guards\Report($reportList[$i]);
            $reportSolveAnswerAuthor = $report->isClosed() ? \Users\UserAgent::GetUserNick(\Guards\ReportAgent::GetAnswerParam($report->getAnswerId(), "authorId")) : "Ответа пока не дано.";
            $reportCloseDate = $report->isClosed() ? \Engine\Engine::DateFormatToRead($report->getCloseDate()) : "не закрыта";
            $reportsTable .= "<tr>";
            $reportsTable .= "<td>" . $report->getStatus() . "</td>";
            $reportsTable .= "<td>" . htmlentities($report->getTheme()) . "</td>";
            $reportsTable .= "<td><a href=\"?page=report&preg=see&rid=$reportList[$i]\">" . htmlentities($report->getShortMessage()) . "</a></td>";
            $reportsTable .= "<td>" . \Engine\Engine::DateFormatToRead($report->getCreateDate()) . "</td>";
            $reportsTable .= "<td>$reportSolveAnswerAuthor</td>";
            $reportsTable .= "<td>$reportCloseDate</td>";
            $reportsTable .= "</tr>";
        }
    }

    include_once \Engine\Engine::ConstructTemplatePath("main", "report", "html");
    $reportMainBlock = getBrick();

    $reportMainBlock = str_replace_once("{REPORTS_PAGE:TABLE}", $reportsTable, $reportMainBlock);

    $reportTablePageBtns = "";
    for ($i = 0; $i < $allReportsCount/20; $i++){
        $rp = $i +1;
        $reportTablePageBtns .= "<a class=\"btn btn-default\" href=\"?page=report&rp=$rp\">$rp</a>";
    }
    $reportMainBlock = str_replace_once("{REPORT_PAGE:TABLE_PAGE_BTNS}", $reportTablePageBtns, $reportMainBlock);

    echo $reportMainBlock;
} else {
    switch($_GET["preg"]){
        case "add": {
            include_once \Engine\Engine::ConstructTemplatePath("create", "report", "html");
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

            include_once \Engine\Engine::ConstructTemplatePath("see", "report", "html");
            $reportSeeBlock = getBrick();

            $reportSeeBlock = str_replace_once("{REPORT_PAGE:SHORT_MESSAGE}", htmlentities($report->getShortMessage()), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:SUBJECT}", htmlentities($report->getTheme()), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:CREATE_DATE}", \Engine\Engine::DateFormatToRead($report->getCreateDate()), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_AVATAR}", $report->ReportAuthor()->getAvatar(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_NICKNAME}", $report->ReportAuthor()->getNickname(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_GROUP_ID}", $report->ReportAuthor()->UserGroup()->getId(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_GROUP_COLOR}", $report->ReportAuthor()->UserGroup()->getColor(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_GROUP_NAME}", $report->ReportAuthor()->UserGroup()->getName(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_REALNAME}", ($report->ReportAuthor()->getRealName() != '') ? "Имя: " . htmlentities($report->ReportAuthor()->getRealName()) . "<br>" : "", $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_FROM}", ($report->ReportAuthor()->getFrom() != '') ? "Откуда: " . htmlentities($report->ReportAuthor()->getFrom()) . "<br>" : "", $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:VK}", ($report->ReportAuthor()->getVK() != '' && $report->ReportAuthor()->IsVKPublic()) ? "VK: <a href=\"http://vk.com/" . htmlentities($report->ReportAuthor()->getVK()) . "\">перейти</a><br>" : "", $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:REPORT_TEXT}", nl2br(\Engine\Engine::CompileBBCode($report->getMessage())), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_SIGNATURE}", nl2br(\Engine\Engine::CompileBBCode($report->ReportAuthor()->getSignature())), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:REPORT_STATUS}", $report->getStatus(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_ID}", $report->getId(), $reportSeeBlock);
            $reportSeeBlock = str_replace_once("{REPORT_PAGE:BTNS_PANEL}", "", $reportSeeBlock);
            if (($report->ReportAuthor() == $user || $user->UserGroup()->getPermission("report_foreign_edit")) && !$report->isClosed()) {
                include_once \Engine\Engine::ConstructTemplatePath("panelbtn", "report", "html");
                $reportBtnBlock = getBrick();
                $reportBtnBlock = str_replace("{REPORT_PAGE:REPORT_ID}", $report->getId(), $reportBtnBlock);

                $reportSeeBlock = str_replace_once("{REPORT_PAGE:BTNS_PANEL}", $reportBtnBlock, $reportSeeBlock);
            } else
                $reportSeeBlock = str_replace_once("{REPORT_PAGE:AUTHOR_GROUP_NAME}", "", $reportSeeBlock);

            if (!$report->isClosed()){
                include_once \Engine\Engine::ConstructTemplatePath("answeraddform", "report", "html");
                $reportAddAnswerForm = getBrick();

                $reportSeeBlock = str_replace_once("{REPORT_PAGE:ANSWER_ADD_FORM}", $reportAddAnswerForm, $reportSeeBlock);
                $reportSeeBlock = str_replace_once("{REPORT_PAGE:SOLVE_ANSWER}", "", $reportSeeBlock);
            } else {
                include_once \Engine\Engine::ConstructTemplatePath("solveanswer", "report", "html");
                $reportSolveAnswer = getBrick();

                $answer = new \Guards\ReportAnswer($report->getAnswerId());
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_AVATAR}", $answer->getAuthor()->getAvatar(), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_NICKNAME}", $answer->getAuthor()->getNickname(), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_GROUP_COLOR}", $answer->getAuthor()->UserGroup()->getColor(), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_GROUP_NAME}", $answer->getAuthor()->UserGroup()->getName(), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_REALNAME}", ($answer->getAuthor()->getRealName() != '') ? "Имя: " . htmlentities($answer->getAuthor()->getRealName()) . "<br>" : "", $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUHTOR_FROM}", ($answer->getAuthor()->getFrom() != '') ? "Откуда: " . htmlentities($answer->getAuthor()->getFrom()) . "<br>" : "", $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_VK}", ($answer->getAuthor()->getVK() != '' && $answer->getAuthor()->IsVKPublic()) ? "VK: <a href=\"http://vk.com/" . htmlentities($answer->getAuthor()->getVK()) . "\">перейти</a><br>" : "", $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_CREATE_DATE}", \Engine\Engine::DateFormatToRead($answer->getCreateDate()), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_TEXT}", nl2br(trim(\Engine\Engine::CompileBBCode($answer->getMessage()))), $reportSolveAnswer);
                $reportSolveAnswer = str_replace_once("{REPORT_PAGE:SA_AUTHOR_SIGNATURE}", \Engine\Engine::CompileBBCode($answer->getAuthor()->getSignature()), $reportSolveAnswer);
                $lastEditInfoBlock = "";
                if ($answer->getEditDate() != '') {
                    $lastEditInfoBlock = "<br><em>Последний раз редактировалось " . $answer->getLastEditor()->getNickname() . ", " . \Engine\Engine::DatetimeFormatToRead($answer->getEditDate());
                    if ($answer->getEditReason() != '')
                        $lastEditInfoBlock .= ", по причине: " . htmlentities($answer->getEditReason());
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
                for ($i = 0; $i <= $ansCount-1; $i++) {
                    $answer = new \Guards\ReportAnswer($answerList[$i]);

                    include_once \Engine\Engine::ConstructTemplatePath("answer", "report", "html");
                    $reportAnswer = getBrick();

                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_AVATAR}", $answer->getAuthor()->getAvatar() ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_NICKNAME}", $answer->getAuthor()->getNickname() ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_GROUP_ID}", $answer->getAuthor()->UserGroup()->getId(), $reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_GROUP_COLOR}", $answer->getAuthor()->UserGroup()->getColor() ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_GROUP_NAME}", $answer->getAuthor()->UserGroup()->getName() ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_REALNAME}", ($answer->getAuthor()->getRealName() != '') ? "Имя: " . htmlentities($answer->getAuthor()->getRealName()) . "<br>" : "" ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_FROM}", ($answer->getAuthor()->getFrom() != '') ? "Откуда: " . htmlentities($answer->getAuthor()->getFrom()) . "<br>" : "" ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_VK}", ($answer->getAuthor()->getVK() != '' && $answer->getAuthor()->IsVKPublic()) ? "VK: <a href=\"http://vk.com/" . htmlentities($answer->getAuthor()->getVK()) . "\">перейти</a><br>" : "" ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:ANSWER_CREATE_DATETIME}", \Engine\Engine::DateFormatToRead($answer->getCreateDate()) ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:ANSWER_TEXT}", nl2br(trim(\Engine\Engine::CompileBBCode($answer->getMessage()))) ,$reportAnswer);
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_SIGNATURE}", nl2br(\Engine\Engine::CompileBBCode($answer->getAuthor()->getSignature())) ,$reportAnswer);
                    $lastEditInfoAnswerBlock = "";
                    if ($answer->getEditDate() != ''){
                        $lastEditInfoAnswerBlock = "<br><em>Последний раз редактировалось " . $answer->getLastEditor()->getNickname() . ", " . \Engine\Engine::DatetimeFormatToRead($answer->getEditDate());
                        if ($answer->getEditReason() != '')
                            $lastEditInfoAnswerBlock .= ", по причине: " . htmlentities($answer->getEditReason());
                        else
                            $lastEditInfoAnswerBlock .= ".";
                        $lastEditInfoAnswerBlock .= "</em>";
                    }
                    $reportAnswer = str_replace_once("{REPORT_PAGE:AO_LAST_EDIT_DATETIME}", $lastEditInfoAnswerBlock ,$reportAnswer);

                    $reportAnswerBtn = "";
                    if ($answer->getAuthor() == $user && !$report->isClosed()) {
                        include_once \Engine\Engine::ConstructTemplatePath("panelanswerbtn", "report", "html");
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
                if (($user->UserGroup()->getPermission("report_edit") && $user->getId() == \Guards\ReportAgent::GetReportParam($_GET["rid"], "author"))
                    || $user->UserGroup()->getPermission("report_foreign_edit")
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

            $reportEditFormLabel = (!empty($_GET["rid"])) ? "жалобы" : "ответа";
            if (!empty($_GET["rid"])) {
                $reportEditBasicInfo = "<div class=\"input-group\">
                                            <div class=\"input-group-addon\">Название:</div>
                                            <div class=\"form-control\">" . htmlentities($report->getShortMessage()) . "</div>
                                        </div>";
            } else {
                $reportEditBasicInfo = "<div class=\"input-group\">
                                            <div class=\"input-group-addon\">Причина редактирования:</div>
                                            <input type=\"text\" name=\"report-edit-reason\" class=\"form-control\">
                                        </div>";
            }

            include_once \Engine\Engine::ConstructTemplatePath("edit", "report", "html");
            $reportEditBlock = getBrick();

            $reportEditBlock = str_replace_once("{REPORT_PAGE:EDIT_OF_LABEL}", $reportEditFormLabel, $reportEditBlock);
            $reportEditBlock = str_replace_once("{REPORT_PAGE:EDIT_BASIC_INFO}", $reportEditBasicInfo, $reportEditBlock);
            $reportEditBlock = str_replace_once("{REPORT_PAGE:EDIT_SUFFIX_FORMACTION}", $suffixFormaction, $reportEditBlock);
            $reportEditBlock = str_replace_once("{REPORT_PAGE:EDIT_BTN_NAME}", $nameBtnEdit, $reportEditBlock);

            echo $reportEditBlock;
            break;
        }
        default: {
            header("Location: ../../index.php?page=errors/notperm");
            exit;
        }
    }
}