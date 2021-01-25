<?php
if (!defined("TT_AP")){ header("Location: ../adminpanel.php?p=forbidden"); exit; }
//Проверка на наличие прав.
if (!$user->UserGroup()->getPermission("report_talking") ||
    !$user->UserGroup()->getPermission("report_foreign_remove") ||
    !$user->UserGroup()->getPermission("report_foreign_edit") ||
    !$user->UserGroup()->getPermission("report_close")
    ){ header("Location: ../../adminpanel.php?res=1"); exit; }
else {
    if (!isset($_GET["reqtype"])){
        $reportList = \Guards\ReportAgent::GetReportsList((!empty($_REQUEST["rpage"])) ? $_REQUEST["rpage"] : 1);
        $reportCount = count($reportList);
        $allReportsCount = \Guards\ReportAgent::GetReportsCount();
    }
    if (isset($_GET["reqtype"]) && $_GET["reqtype"] == "discusion"){
        if (empty($_GET["rid"])){
            header("Location: ../../adminpanel.php?p=report&res=5nrid");
            exit;
        } else {
            if (!Guards\ReportAgent::isReportExists($_GET["rid"])){
                header("Location: ../../adminpanel.php?p=report&res=5ne");
                exit;
            }
        }
        $report = new \Guards\Report($_GET["rid"]);
        if (!$report->isClosed())
            $report->setViewed();
        $answerList = $report->getAnswersList((!empty($_GET["rapage"])) ? $_GET["rapage"] : 1);
        $answerCount = count($answerList);
    }
    if (isset($_GET["reqtype"]) && $_GET["reqtype"] == "edit"){
        if (empty($_GET["rid"]) || empty($_GET["ansid"])){
            if (empty($_GET["rid"]) && empty($_GET["ansid"])){
                header("Location: ../../adminpanel.php?p=report&res=5nroai");
                exit;
            } elseif (!empty($_GET["rid"])){
                $report = new \Guards\Report($_GET["rid"]);
                $message = $report->getMessage();
                $nameBtnEdit = "reports-edit-reports-edit";
                $suffixFormaction = "&rid=".$_GET["rid"];
            } elseif (!empty($_GET["ansid"])){
                $answer = new \Guards\ReportAnswer($_GET["ansid"]);
                $message = $answer->getMessage();
                $nameBtnEdit = "report-edit-answer-edit";
                $date = date("Y-m-d H:i:s", time());
                $suffixFormaction = "&ansid=" . $_GET["ansid"];
            }
        }
        if (\Guards\Report::GetReportParam($_REQUEST["rid"], "status") == 2) {
            header("Location: ../../adminpanel.php?p=report&res=5naacr");
            exit;
        }
    }?>
<script src="adminpanel/scripts/UserFinderParser.js"></script>
<div class="inner cover">
    <h1 class="cover-heading"><?= \Engine\LanguageManager::GetTranslation("reports_panel.page_name"); ?></h1>
    <p class="lead"><?= \Engine\LanguageManager::GetTranslation("reports_panel.page_description"); ?></p>
    <form method="post" action="adminpanel/scripts/reports.php">
        <div class="custom-group">
            <div class="div-border">
                <?php if (!isset($_GET["reqtype"])){ ?>
                <div class="report-table">
                    <h2><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.panel_name"); ?></h2>
                    <p class="helper"><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.panel_description"); ?></p>
                    <hr>
                    <p><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.panel_tip"); ?></p>
                    <div class="alert alert-info">
                        <span class="glyphicons glyphicons-info-sign"></span> <?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.panel_remove_tip"); ?>
                    </div>
                    <div class="alert alert-info" id="report-counter-div" hidden>
                        <strong><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.selected"); ?></strong>: <span id="report-counter-span">0</span>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-danger" type="submit" id="report-table-delete-selected-btn" name="reports-table-delete-btn" disabled><span class="glyphicons glyphicons-delete"></span> <?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.remove_selected"); ?></button>
                    </div>
                    <input type="hidden" id="report-ids-for-delete" name="reports-ids-for-delete">
                    <hr>
                    <div class="table-responsive">
                    <table class="table">
                        <thead style="background: radial-gradient(at center, #b40000, #351822); color: white;">
                            <tr>
                                <td><input type="checkbox" id="reports-table-select-all"></td>
                                <td><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.table_creation"); ?></td>
                                <td><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.table_author"); ?></td>
                                <td><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.table_subject"); ?></td>
                                <td><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.table_description"); ?></td>
                                <td><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.table_mark"); ?></td>
                                <td><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.table_answered"); ?></td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($reportCount == 0) { ?><tr><td class="alert-success" style="text-align: center;" colspan="8"><span class="glyphicon glyphicon-info-sign"></span> <?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.empty_table"); ?></td></tr> <?php }
                            else {
                                for ($i = 0; $i <= $reportCount-1; $i++){
                                    $report = new \Guards\Report($reportList[$i]); ?>
                                    <tr <?php if (!$report->getViewed()) echo "class=\"tr-bold\"";?>>
                                        <td><input type="checkbox" data-rid-selected="<?php echo $report->getId(); ?>"></td>
                                        <td><?php echo \Engine\Engine::DateFormatToRead($report->getCreateDate()); ?></td>
                                        <td><?php echo $report->ReportAuthor()->getNickname(); ?></td>
                                        <td><?php echo htmlentities($report->getTheme()); ?></td>
                                        <td><?php echo htmlentities($report->getShortMessage()); ?></td>
                                        <td><?php echo $report->getMark(); ?></td>
                                        <td><?php echo $report->ReportAnswerAuthor()->getNickname(); ?></td>
                                        <td><button class="btn btn-default" style="width:100%;" type="submit" name="reports-see-btn" formaction="adminpanel/scripts/reports.php?rid=<?php echo $report->getId(); ?>"><?= \Engine\LanguageManager::GetTranslation("reports_panel.table_page.see_btn"); ?></button></td>
                                    </tr>
                                <?php }} ?>
                        </tbody>
                    </table>
                    </div>
                    <div class="table-footer">
                        <div class="btn-group">
                            <?php for ($i = 0; $i <= $allReportsCount/50; $i++) {
                                $rp = $i + 1;
                                $class = $_GET["rpage"] == $rp ? "active" : "";
                                echo "<a class=\"btn btn-default $class\" href=\"adminpanel.php?p=report&rpage=$rp\">$rp</a>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php } elseif (isset($_GET["reqtype"]) && $_GET["reqtype"] == "discusion") { ?>
                <div class="report-discusion">
                    <h3><?php echo htmlentities($report->getShortMessage()); ?></h3>
                    <p class="helper"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.panel_description");?></p>
                    <hr>
                    <div class="report-header">
                        <div class="report-header-head">
                            <span class="report-author-info"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.author");?> <?php echo $report->ReportAuthor()->getNickname(); ?></span>
                            <img src="<?php echo $report->ReportAuthor()->getAvatar(); ?>" class="report-author-avatar">
                            <span class="report-report-info"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.create_date");?> <?php echo \Engine\Engine::DateFormatToRead($report->getCreateDate()); ?>
                               | <?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.category")?> <?php echo htmlentities($report->getTheme()); ?></span>
                        </div>
                        <div class="report-header-body">
                            <?php echo trim(\Engine\Engine::CompileBBCode(htmlentities($report->getMessage()))); ?>
                        </div>
                        <div class="report-header-footer">
                            Статус: <?php echo $report->getStatus(); ?>
                            <div class="btn-group" style="float: right;">
                                <button class="btn btn-default" type="submit" name="reports-report-edit" formaction="adminpanel/scripts/reports.php?rid=<?php echo $report->getId(); ?>" title="<?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.edit_report_btn");?>"><span class="glyphicons glyphicons-pen"></span></button>
                                <button class="btn btn-danger" type="submit" name="reports-report-delete" formaction="adminpanel/scripts/reports.php?rid=<?php echo $report->getId(); ?>" title="<?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.remove_report_btn");?>"><span class="glyphicons glyphicons-erase"></span></button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <?php
                    #Если тема закрыта - то нельзя добавлять ответы.
                    if (!$report->isClosed()){ ?>
                    <div class="report-answer-block">
                        <div class="btn-group">
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.bold")?>" type="button"
                                    onclick="insertBBCode('b', false, document.getElementById('report-answer-text'));">
                                <strong>B</strong></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.italic")?>" type="button"
                                    onclick="insertBBCode('i', false, document.getElementById('report-answer-text'));"><em>I</em>
                            </button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.underline")?>" type="button"
                                    onclick="insertBBCode('u', false, document.getElementById('report-answer-text'));">
                                <ins>U</ins>
                            </button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.strike")?>" type="button"
                                    onclick="insertBBCode('s', false, document.getElementById('report-answer-text'));"><s>S</s></button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_left")?>" type="button"
                                    onclick="insertBBCode('align=left', 'align', document.getElementById('report-answer-text'));"><span
                                    class="glyphicon glyphicon-align-left"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_center")?>" type="button"
                                    onclick="insertBBCode('align=center', 'align', document.getElementById('report-answer-text'));">
                                <span class="glyphicon glyphicon-align-center"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_right")?>" type="button"
                                    onclick="insertBBCode('align=right', 'align', document.getElementById('report-answer-text'));"><span
                                    class="glyphicon glyphicon-align-right"></span></button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.youtube")?>" type="button"
                                    onclick="insertBBCode('youtube=', true, document.getElementById('report-answer-text'));"><span
                                    class="glyphicon glyphicon-play btn-youtube"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.image")?>" type="button"
                                    onclick="insertBBCode('img=', true, document.getElementById('report-answer-text'));"><span
                                    class="glyphicon glyphicon-picture"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.upload_file")?>" type="button" onclick="$('#uploader-form').show();">
                                <span class="glyphicon glyphicon-upload"></span> Загрузить файл
                            </button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.insert_hr")?>" type="button"
                                    onclick="insertBBCode('hr', true, document.getElementById('report-answer-text'));"><span
                                    class="glyphicons glyphicons-vector-path-line"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.quote")?>" type="button"
                                    onclick="insertBBCode('quote', 'quote', document.getElementById('report-answer-text'));"><span
                                    class="glyphicons glyphicons-user-conversation"></span></button>
                        </div>
                         <textarea class="form-control" id="report-answer-text" name="reports-answer-text"
                          style="resize: none; height: 300px;"
                          placeholder="<?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.placeholder_answer");?>"></textarea>

                        <div class="btn-group">
                            <button class="btn btn-default" type="submit" name="reports-answer-send" formaction="adminpanel/scripts/reports.php?rid=<?php echo $report->getId(); ?>"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.public_answer");?></button>
                            <button class="btn btn-default" type="reset"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.clear_form");?></button>
                        </div>
                    </div>
                    <?php } else {
                        $repAnswer = new \Guards\ReportAnswer($report->getAnswerId()); ?>
                    <h3><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.answer");?></h3>
                    <div class="report-answer">
                        <div class="report-solve-head">
                            <span class="report-author-info"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.author");?> <?php echo $repAnswer->getAuthor()->getNickname();?></span>
                            <img class="report-author-avatar" src="<?php echo $repAnswer->getAuthor()->getAvatar(); ?>">
                            <span class="report-report-info"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.create_date");?> <?php echo \Engine\Engine::DateFormatToRead($repAnswer->getCreateDate()); ?></span>
                        </div>
                        <div class="report-solve-body">
                            <?php echo nl2br(trim(\Engine\Engine::CompileBBCode($repAnswer->getMessage()))); ?>
                        </div>
                        <div class="report-solve-footer">
                            <?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.date_closed");?> <?php echo \Engine\Engine::DateFormatToRead($report->getCloseDate()); ?>
                        </div>
                    </div>
                    <?php }
                    for ($i = 0; $i <= $answerCount-1; $i++){
                    $answer = new \Guards\ReportAnswer($answerList[$i]); ?>
                    <hr>
                    <div class="report-answer">
                        <div class="report-answer-head">
                            <span class="report-author-info"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.author")?> <?php echo $answer->getAuthor()->getNickname();?></span>
                            <img class="report-author-avatar" src="<?php echo $answer->getAuthor()->getAvatar(); ?>">
                            <span class="report-report-info"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.create_date")?> <?php echo \Engine\Engine::DateFormatToRead($answer->getCreateDate()); ?></span>
                        </div>
                        <div class="report-answer-body">
                            <?php echo \Engine\Engine::CompileBBCode(trim($answer->getMessage())); ?>
                            <?php if ($answer->getEditDate() != ''){
                                $editInfo =  "<span class=\"report-answer-edit-info\">" . \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.last_edit") . " " . $answer->getLastEditor()->getNickname() .

                                   " " . \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.in") . " " .  \Engine\Engine::DatetimeFormatToRead($answer->getEditDate());
                                if ($answer->getEditReason()) $editInfo .= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.reason") . " " . htmlentities($answer->getEditReason());
                                echo $editInfo .= "</span>";
                            }
                                ?>
                        </div>
                        <div class="report-answer-footer">
                            <?php if (!$report->isClosed()) { ?><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.actions")?>
                            <div class="btn-group" style="float: right;">
                                <button class="btn btn-default" type="submit" name="reports-answer-accept" title="<?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.check_for_solve")?>" formaction="adminpanel/scripts/reports.php?rid=<?php echo $answer->getParentReportID(); ?>&ansid=<?php echo $answer->getAnswerId(); ?>"><span class="glyphicon glyphicon-ok"></span></button>
                                <button class="btn btn-default" type="submit" name="reports-answer-edit" title="<?= \Engine\LanguageManager::GetTranslation("edit")?>" formaction="adminpanel/scripts/reports.php?rid=<?php echo $answer->getParentReportID(); ?>&ansid=<?php echo $answer->getAnswerId(); ?>"><span class="glyphicons glyphicons-pen"></span></button>
                                <button class="btn btn-danger" type="submit" name="reports-answer-delete" title="<?= \Engine\LanguageManager::GetTranslation("remove")?>" formaction="adminpanel/scripts/reports.php?rid=<?php echo $answer->getParentReportID(); ?>&ansid=<?php echo $answer->getAnswerId(); ?>"><span class="glyphicons glyphicons-delete"></span></button>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php }
                elseif (isset($_GET["reqtype"]) && $_GET["reqtype"] == "edit"){ ?>
                <div class="report-edit">
                    <h3><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.edit")?> <?php echo (!empty($_GET["rid"])) ? \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.of_report") : \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.of_answer"); ?></h3>
                    <hr>
                    <?php if (!empty($_GET["rid"])) { ?>
                        <div class="input-group">
                            <div class="input-group-addon"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.name")?>:</div>
                            <input name="reports-edit-shortmessage" class="form-control" type="text" value="<?php echo $report->getShortMessage(); ?>">
                        </div>
                    <?php } else { ?>
                        <div class="input-group">
                            <div class="input-group-addon"><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.edit_reason")?></div>
                            <input type="text" name="reports-edit-reason" class="form-control">
                        </div>
                    <?php } ?>
                    <hr>
                    <div class="report-edit-message-form">
                        <div class="btn-group">
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.bold")?>" type="button" onclick="insertBBCode('b', false, document.getElementById('report-edit-message-text'));"><strong>B</strong></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.italic")?>" type="button" onclick="insertBBCode('i', false, document.getElementById('report-edit-message-text'));"><em>I</em></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.underline")?>" type="button" onclick="insertBBCode('u', false, document.getElementById('report-edit-message-text'));"><ins>U</ins></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.strike")?>" type="button" onclick="insertBBCode('s', false, document.getElementById('report-edit-message-text'));"><s>S</s></button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_left")?>" type="button" onclick="insertBBCode('align=left', 'align', document.getElementById('report-edit-message-text'));"><span class="glyphicon glyphicon-align-left"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_center")?>" type="button" onclick="insertBBCode('align=center', 'align', document.getElementById('report-edit-message-text'));"><span class="glyphicon glyphicon-align-center"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.align_right")?>" type="button" onclick="insertBBCode('align=right', 'align', document.getElementById('report-edit-message-text'));"><span class="glyphicon glyphicon-align-right"></span></button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.youtube")?>" type="button" onclick="insertBBCode('youtube=', true, document.getElementById('report-edit-message-text'));"><span class="glyphicon glyphicon-play btn-youtube"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.image")?>" type="button" onclick="insertBBCode('img=', true, document.getElementById('report-edit-message-text'));"><span class="glyphicon glyphicon-picture"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.upload_file")?>" type="button" onclick="$('#uploader-form').show();"><span class="glyphicon glyphicon-upload"></span> Загрузить файл</button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.insert_hr")?>" type="button" onclick="insertBBCode('hr', true, document.getElementById('report-edit-message-text'));"><span class="glyphicons glyphicons-vector-path-line"></span></button>
                            <button class="btn btn-default" title="<?= \Engine\LanguageManager::GetTranslation("editor_functions.quote")?>" type="button" onclick="insertBBCode('quote', 'quote', document.getElementById('report-edit-message-text'));"><span class="glyphicons glyphicons-user-conversation"></span></button>
                        </div>
                        <textarea class="form-control" name="reports-edit-message-text" id="report-edit-message-text" style="resize: none; height: 350px;"><?php echo $message; ?></textarea>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-default" formaction="adminpanel/scripts/reports.php?<?php echo $suffixFormaction; ?>" name="<?php echo $nameBtnEdit; ?>"><span class="glyphicons glyphicons-pencil"></span> <?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.save_changes")?></button>
                            <button type="reset" class="btn btn-info" name="reports-edit-message-erase"><span class="glyphicons glyphicons-erase"></span> <?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.cancel_changes")?></button>
                            <button type="button" class="btn btn-default" onclick="window.history.back();" name="reports-edit-message-back"><span class="glyphicons glyphicons-arrow-left"></span> <?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.back_btn")?></button>
                        </div>
                </div>
                <?php if (!empty($_GET["rid"])){ ?>
                <hr>
                <div class="report-user-added-div">
                    <h4><?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.add_to_discussion")?></h4>
                    <div class="input-group">
                        <input class="form-control" type="text" maxlength="16" id="report-user-add-input" placeholder="<?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.placeholder_add_to_discussion")?>">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" onclick="addToReportDiscusse();">
                                <span class="glyphicons glyphicons-user-add"></span> <?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.add")?>
                            </button>
                        </span>
                    </div>
                    <?= \Engine\LanguageManager::GetTranslation("reports_panel.discussion_page.added")?>
                    <div class="report-user-added-list" id="reports-au-list">
                        <?php for ($i = 0; $i < count($report->getAddedToDiscuse()); $i++){ ?>
                            <div class="report-user-added-btn">
                                <?php echo "<a target=\"_blank\" href=\"/adminpanel.php?p=users&uid=" . $report->getAddedToDiscuse()[$i]. "\">" . Users\UserAgent::GetUserNick($report->getAddedToDiscuse()[$i]) . "</a>"; ?>
                                <span class="report-user-added-btn-cls" id="report-user-added-btn-span-<?php echo $report->getAddedToDiscuse()[$i]; ?>" onclick="deleteFromDiscuse('<?php echo $report->getAddedToDiscuse()[$i]; ?>')">X</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            </div>
                <?php } }
                else {
                    header("Location: ../../adminpanel.php?p=report&res=2");
                    exit;
                } ?>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    //report-table-delete-selected-btn - delete btn id
    //reports-table-select-all - checkbox for checking all id
    //report-ids-for-delete - hidden input for accumulate ids for deleting

    $("#report-table-select-all").change(function(){
        if ($("#report-table-select-all").is(":checked")){
            $("tbody input[type=checkbox]").prop("checked", true);
        } else {
            $("tbody input[type=checkbox]").prop("checked", false);
        }
    });

    $("input[type=checkbox]").change(function() {
        var inputStr = "";

        $("tbody input[type=checkbox]:checked").each(function() {
           if ($(this).data("rid-selected") == undefined)
               return;

           inputStr += $(this).data("rid-selected") + ",";
        });

        inputStr = inputStr.slice(0, -1);
        $("#report-ids-for-delete").val(inputStr);

        if ($("#report-counter-div").is(":hidden")){
            $("#report-counter-div").show();
            $("#report-table-delete-selected-btn").prop("disabled", false);
        } else {
            if ($("tbody input[type=checkbox]:checked").length == 0) {
                $("#report-counter-div").hide();
                $("#report-table-delete-selected-btn").prop("disabled", true);
            }
        }
        $("#report-counter-span").html($("tbody input[type=checkbox]:checked").length);
    });

    $("#report-user-add-input").on("keyup", function() {
        setInputElement($("#report-user-add-input"));
        if ($(inputElement).val().length >= 2)
            showPopMenu();
        else hidePopMenu();
    });

    var texterID = "report-edit-message-text";
    function insertBBCode(openTag, notNeedClose = false, texterElement = null){
        if (texterElement == null)
            var texter = document.getElementById("report-add-message");
        else
            var texter = texterElement;
        startText = texter.value.substring(0, texter.selectionStart);
        endText = texter.value.substring(texter.selectionEnd, texter.value.length);
        tagingText = texter.value.substring(texter.selectionStart, texter.selectionEnd);
        startPos = texter.selectionStart;
        endPos = texter.selectionEnd;
        startText += '[' + openTag + ']';
        if (notNeedClose === false) endText = '[\/' + openTag + ']' + endText;
        if (notNeedClose !== false && notNeedClose !== true) endText = '[\/' + notNeedClose + ']' + endText;
        texter.value = startText + tagingText + endText;
        texter.focus();
        texter.setSelectionRange(startPos + (2 + openTag.length), endPos + (2 + openTag.length));

        //texter.value.insert
    }
    function addToReportDiscusse(){
        $.ajax({
            type: "POST",
            url: "/adminpanel/scripts/ajax/reportdaajax.php",
            data: "atd&uid=" + $("#report-user-add-input").val() + "&rid=<?php echo $report->getId(); ?>",
            success: function (data) {
                if (data == "User is not exists.") {
                    return;
                }
                if (data == "Not need to add.") {
                    return;
                }
                if (data == "Not need to add yourself.") {
                    return;
                }
                if (data == "User is added.") {
                    return;
                }
                if (data == "User id not set."){
                    return;
                }
                alert(data);
                $("#reports-au-list").append("<div class=\"report-user-added-btn\"><a target=\"_blank\" href=\"/adminpanel.php?p=users&uid=" + data.substring(0, data.indexOf(" ")) + "\">"
                    + data.substring(data.indexOf(" ") + 1, data.length) + "</a><span class=\"report-user-added-btn-cls\" id=\"report-user-added-btn-span-" + data.substring(0, data.indexOf(" ")) +
                    "\" onclick=\"deleteFromDiscuse('" + data.substring(0, data.indexOf(" ")) + "');\">X</span>");
            }
        });
    }
    function deleteFromDiscuse(idUser){
        $.ajax({
            type: "POST",
            url: "/adminpanel/scripts/ajax/reportdaajax.php",
            data: "rfd&uid=" + idUser + "&rid=<?php echo $report->getId(); ?>",
            success: function (data) {
                if (data == "User is not exists.") {
                    return;
                }
                if (data == "User is not in discusse.") {
                    return;
                }
                if (data == "Report id not set.") {
                    return;
                }
                var parent = $("#report-user-added-btn-span-" + idUser).parent().remove();
            }
        });
    }

    <?php include_once "adminpanel/scripts/uploaderscript.js"; ?>
</script>
<?php } ?>