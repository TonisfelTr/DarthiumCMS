<?php

namespace Guards\Models;

use Engine\DataKeeper;
use Engine\LanguageManager;
use Guards\ReportAgent;
use Users\Models\User;

class Report extends ReportAgent{
    private $reportId;
    private $reportStatus;
    private $reportTheme;
    private $reportShortMessage;
    private $reportMessage;
    private $reportAuthorId;
    private $reportCreateDate;
    private $reportCloseDate;
    private $reportAnswerId;
    private $reportIsViewed;
    private $reportAddedInDiscuse = [];

    private $reportAuthor;
    private $reportAnswerAuthor;

    private function getReportReasonsLines($lineNum){
        if (is_numeric($lineNum)) {
            if (!$reasons = file_get_contents("engine/config/represes.sfc", FILE_USE_INCLUDE_PATH)) return false;
            $reasons = explode("\n", $reasons);
            return $reasons[$lineNum];
        } else return $lineNum;

    }

    public function __construct($reportId){
        $queryResponse = DataKeeper::Get("tt_reports", ["*"], ["id" => $reportId])[0];

        $this->reportId = $queryResponse["id"];
        $this->reportStatus = $queryResponse["status"];
        $this->reportTheme = $queryResponse["theme"];
        $this->reportAuthorId = $queryResponse["author"];
        $this->reportShortMessage = $queryResponse["short_message"];
        $this->reportMessage = $queryResponse["message"];
        $this->reportAnswerId = $queryResponse["answerId"];
        $this->reportCreateDate = $queryResponse["create_date"];
        $this->reportCloseDate = $queryResponse["close_date"];
        $this->reportIsViewed = $queryResponse["viewed"];

        $this->reportAuthor = new User($this->reportAuthorId);
        $this->reportAnswerAuthor = new User(self::GetAnswerParam($this->reportAnswerId, "authorId"));

        $queryResponse = DataKeeper::Get("tt_reportda", ["addedUID"], ["reportId" => $this->reportId]);

        foreach ($queryResponse as $reportDA){
            $this->reportAddedInDiscuse[] = $reportDA["addedUID"];
        }
    }

    public function ReportAuthor(){
        return $this->reportAuthor;
    }
    public function ReportAnswerAuthor(){
        return $this->reportAnswerAuthor;
    }

    public function getId(){
        return $this->reportId;
    }
    public function getStatus(){
        /* Возможные расшифровки:
         * 0 - Жалоба ждёт проверки
         * 1 - Жалоба открыта.
         * 2 - Жалоба закрыта
         */
        switch($this->reportStatus){
            case 0:
                return LanguageManager::GetTranslation("reports_panel.discussion_page.status_wait_for_checking");
            case 1:
                return LanguageManager::GetTranslation("reports_panel.discussion_page.status_report_is_open");
            case 2:
                return LanguageManager::GetTranslation("reports_panel.discussion_page.status_report_is_closed");
        }
    }
    public function isClosed(){
        if ($this->reportStatus == 2) return true;
        else return false;
    }
    public function getTheme(){
        return $this->getReportReasonsLines($this->reportTheme);
    }
    public function getShortMessage(){
        return $this->reportShortMessage;
    }
    public function getMessage(){
        return $this->reportMessage;
    }
    public function getAuthorID(){
        return $this->reportAuthorId;
    }
    public function getCreateDate(){
        return $this->reportCreateDate;
    }
    public function getCloseDate(){
        return $this->reportCloseDate;
    }
    public function getAnswerId(){
        return $this->reportAnswerId;
    }
    public function getAddedToDiscuse(){
        return $this->reportAddedInDiscuse;
    }
    public function getViewed(){
        return $this->reportIsViewed;
    }
    public function getAnswersList($page = 1){
        $lowBorder = ($page - 1) * 12;
        $highBorder = $page * 12;

        return DataKeeper::MakeQuery("SELECT `id` FROM `tt_reportanswers` WHERE `reportId`=? AND `id` != (SELECT `answerId` FROM `tt_reports` WHERE `id`=?) LIMIT $lowBorder,$highBorder",
            [$this->reportId, $this->reportId], true);
    }
    public function setViewed(){
        return DataKeeper::Update("tt_reports", ["viewed" => 1, "status" => 1], ["id" => $this->reportId]);
    }
    public function isAdded($userId){
        if (in_array($userId, $this->getAddedToDiscuse())) return true;
        else return false;
    }
}