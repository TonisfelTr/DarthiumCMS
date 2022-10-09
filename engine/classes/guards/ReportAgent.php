<?php

namespace Guards;

use Engine\DataKeeper;
use Engine\Engine;
use Engine\ErrorManager;

class ReportAgent
{
    private static function isAnswerExists($answerId){
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_reportanswers` WHERE `id` = ?", ["$answerId"])["count(*)"];

    }
    private static function isAnswerSolve($answerId){
        if (!self::isAnswerExists($answerId)){
            ErrorManager::GenerateError(30);
            return ErrorManager::GetError();
        }

        $queryResponse = DataKeeper::Get("tt_reports", ["id"], [$answerId])[0]["id"];
        if ($queryResponse > 0)
            return true;
        else
            return false;
    }

    public static function isAddedToDiscusse($reportId, $id){
        $queryResponse = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_reportda` WHERE `addedUID` = ? AND `reportId` = ?", [$id, $reportId])["count(*)"];
        if ($queryResponse)
            return true;
        else
            return false;
    }
    public static function isReportExists($reportId){
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_reports` WHERE `id` = ?", [$reportId])["count(*)"];
    }

    public static function CreateAnswer($authorId, $text, $reportId){
        if (!self::isReportExists($reportId)){
            ErrorManager::GenerateError(29);
            return ErrorManager::GetError();
        }
        DataKeeper::InsertTo("tt_reportanswers", ["reportId" => $reportId,
            "authorId" => $authorId,
            "create_date" => date("Y-m-d", Engine::GetSiteTime()),
            "message" => $text]);

        return self::ChangeReportParam($reportId, "viewed", 0) == true ? true : false;
    }
    public static function DeleteAnswer($answerId){
        if (!self::isAnswerExists($answerId)){
            ErrorManager::GenerateError(30);
            return ErrorManager::GetError();
        }

        if (self::isAnswerSolve($answerId)){
            ErrorManager::GenerateError(31);
            return ErrorManager::GetError();
        }

        return DataKeeper::Delete("tt_reportanswers", ["id" => $answerId]);
    }
    public static function ChangeAnswerText($answerId, $newText, $reasonEdit, $editorId){
        if (!self::isAnswerExists($answerId)){
            ErrorManager::GenerateError(30);
            return ErrorManager::GetError();
        }

        if (self::isAnswerSolve($answerId)){
            ErrorManager::GenerateError(31);
            return ErrorManager::GetError();
        }

        return DataKeeper::Update("tt_reportanswers", ["message" => $newText, "edit_date" => date("Y-m-d H:m:s", Engine::GetSiteTime()), "reason_edit" => $reasonEdit, "last_editorId" => $editorId], ["id" => $answerId]);
    }
    public static function SetAsSolveOfReportTheAnswer($idReport, $answerId){
        if (!self::isAnswerExists($answerId)){
            ErrorManager::GenerateError(30);
            return ErrorManager::GetError();
        }
        if (!self::isReportExists($idReport)){
            ErrorManager::GenerateError(29);
            return ErrorManager::GetError();
        }

        return DataKeeper::Update("tt_reports", ["answerId" => $answerId, "status" => 2, "close_data" => date("Y-m-d", Engine::GetSiteTime())], ["id" => $idReport]);
    }
    public static function GetAnswerParam($answerId, $param){
        if (!self::isAnswerExists($answerId)){
            ErrorManager::GenerateError(30);
            return ErrorManager::GetError();
        }

        return DataKeeper::Get("tt_reportanswers", [$param], ["id" => $answerId])[0][$param];
    }

    public static function CreateReport($author, $theme, $shortMessage, $message){
        return DataKeeper::InsertTo("tt_reports", ["theme" => $theme,
            "author" => $author,
            "short_message" => $shortMessage,
            "message" => $message,
            "create_date" => date("Y-m-d", Engine::GetSiteTime())]);

    }
    public static function DeleteReport($reportId){
        if (!self::isReportExists($reportId)){
            ErrorManager::GenerateError(29);
            return ErrorManager::GetError();
        }

        $firstQuery = DataKeeper::Delete("tt_reports", ["id" => $reportId]);
        if ($firstQuery)
            return DataKeeper::Delete("tt_reportanswers", ["reportId" => $reportId]);
        return false;
    }

    public static function ChangeReportParam($idReport, $param, $newValue){
        if (in_array($param, ["create_date", "id", "close_date"])) return false;

        if (!self::isReportExists($idReport)){
            ErrorManager::GenerateError(29);
            return ErrorManager::GetError();
        }

        return DataKeeper::Update("tt_reports", [$param => $newValue], ["id" => $idReport]);
    }
    public static function GetReportsCount(){
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_reports`")["count(*)"];
    }
    public static function GetReportsCountWithUser($authorId){
        return DataKeeper::MakeQuery("SELECT (SELECT count(*) FROM `tt_reports` WHERE `author`=$authorId) + (SELECT count(*) FROM `tt_reportda` WHERE `addedUID`=$authorId) AS `result`")["result"];
    }
    public static function GetReportsList($page = 1){
        if ($page < 1)
            return false;

        $lowBorder = ($page - 1) * 50;

        return DataKeeper::MakeQuery("SELECT `id` FROM `tt_reports` ORDER BY `id` DESC LIMIT $lowBorder,50", null, true);
    }
    public static function GetReportsListByAuthor($authorId, $page = 1){
        {
            $lowBorder = ($page - 1) * 20;

            return DataKeeper::MakeQuery("(SELECT `id` FROM `tt_reports` WHERE `author` = ?) UNION (SELECT `reportId` FROM `tt_reportda` WHERE `addedUID` = ?) ORDER BY `id` DESC LIMIT $lowBorder, 20",
                [$authorId, $authorId], true);
        }
    }
    public static function GetReportParam($reportId, $param){
        return DataKeeper::Get("tt_reports", [$param], ["id" => $reportId])[0][$param];
    }
    public static function GetUnreadedReportsCount(){
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_reports` WHERE `viewed` = ?", [0])["count(*)"];
    }
    public static function GetReport($reportId){
        if (!ReportAgent::isReportExists($reportId)) return false;
        else return new Report($reportId);
    }

    public static function AddToDiscusse($reportId, $id, $addedBy){
        if (ReportAgent::isAddedToDiscusse($reportId, $id)) return false;

        return DataKeeper::InsertTo("tt_reportda", ["reportId" => $reportId, "addedUID" => $id, "addedByUID" => $addedBy]);
    }
    public static function RemoveFromDiscusse($reportId, $id){
        if (!ReportAgent::isAddedToDiscusse($reportId, $id)) return false;

        return DataKeeper::Delete("tt_reportda", ["reportId" => $reportId, "addedUID" => $id]);
    }

}