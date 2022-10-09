<?php

namespace Guards\Models;

use Engine\DataKeeper;
use Engine\Engine;
use Users\Models\User;

class ReportAnswer{
    private $answerId;
    private $answerAuthorId;
    private $parentReportId;
    private $answerCreateDate;
    private $answerMessage;
    private $answerEditDate;
    private $answerEditReason;
    private $answerLastEditorId;

    private $parentReport;
    private $authorUser;
    private $lastEditor;

    public function __construct($commentId){
        $params = DataKeeper::Get("tt_reportanswers", ["*"], ["id" => $commentId])[0];

        $this->answerId = $params["id"];
        $this->parentReportId = $params["reportId"];
        $this->answerAuthorId = $params["authorId"];
        $this->answerCreateDate = $params["create_date"];
        $this->answerMessage = $params["message"];
        $this->answerEditDate = $params["edit_date"];
        $this->answerEditReason = $params["edit_reason"];
        $this->answerLastEditorId = $params["last_editorId"];

        $this->parentReport = new Report($this->parentReportId);
        $this->authorUser = new User($this->answerAuthorId);
        if ($this->answerLastEditorId != 0) $this->lastEditor = new User($this->answerLastEditorId);
    }
    public function getAnswerId(){
        return $this->answerId;
    }
    public function getAuthorID(){
        return $this->answerAuthorId;
    }
    public function getParentReportID(){
        return $this->parentReportId;
    }
    public function getCreateDate(){
        return $this->answerCreateDate;
    }
    public function getEditDate(){
        return $this->answerEditDate;
    }
    public function getEditReason(){
        return $this->answerEditReason;
    }
    public function getMessage(){
        return $this->answerMessage;
    }
    public function ParentReport(){
        return $this->parentReport;
    }
    public function getAuthor(){
        return $this->authorUser;
    }
    public function getLastEditor(){
        return $this->lastEditor;
    }
    public function changeText($newText, $editorId, $reason = ''){
        return DataKeeper::Update("tt_reportanswers", ["message" => $newText, "edit_date" => date("Y-m-d", Engine::GetSiteTime()),
            "reason_edit" => $editorId], ["id" => $this->answerId]);
    }
}