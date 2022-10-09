<?php

namespace Guards;

use Engine\DataKeeper;
use Engine\Engine;
use Engine\ErrorManager;
use Users\UserAgent;

class Logger{

    private const ACCESSES_LOGS = 1;
    private const ERRORS_LOGS = 2;
    private const VISITORS_LOGS = 4;

    private const ACCESSES_PATH = "engine/logs/accesses/";
    private const ERRORS_PATH = "engine/logs/errors/";
    private const VISITORS_PATH = "engine/logs/visitors/";

    /**
     * Get count of files in log folder.
     *
     * @param int $logType
     * @return int Count files in log folder.
     */
    private static function getLogCount(int $logType) : int {
        if (!in_array($logType, [self::ACCESSES_LOGS, self::ERRORS_LOGS, self::VISITORS_LOGS]))
            throw new \InvalidArgumentException("Invalid argument received");

        switch($logType) {
            case self::ACCESSES_LOGS:
                $iterator = new \FilesystemIterator(self::ACCESSES_PATH, \FilesystemIterator::SKIP_DOTS);
                break;
            case self::ERRORS_LOGS:
                $iterator = new \FilesystemIterator(self::ERRORS_PATH, \FilesystemIterator::SKIP_DOTS);
                break;
            case self::VISITORS_LOGS:
                $iterator = new \FilesystemIterator(self::VISITORS_PATH, \FilesystemIterator::SKIP_DOTS);
                break;
        }

        $filesCount = iterator_count($iterator);
        return $filesCount;
    }

    /**
     * Get size of last log file.
     *
     * @param int $logType
     * @return int Size of last log file.
     */
    private static function getLogSize(int $logType) : int {
        if (!in_array($logType, [self::ACCESSES_LOGS, self::ERRORS_LOGS, self::VISITORS_LOGS]))
            throw new \InvalidArgumentException("Invalid argument received");

        switch ($logType) {
            case self::ACCESSES_LOGS:
                $filesize = filesize(self::ACCESSES_PATH . "access.log");
                break;
            case self::ERRORS_LOGS:
                $filesize = filesize(self::ACCESSES_PATH . "errors.log");
                break;
            case self::VISITORS_LOGS:
                $filesize = filesize(self::ACCESSES_PATH . "visitors.log");
                break;
        }

        return $filesize;
    }

    /**
     * Add log string into database.
     *
     * @param $authorId
     * @param $log_text
     * @return bool Success or fail of adding record to table.
     */
    public static function LogAction($authorId, $log_text) : bool {
        return (bool)DataKeeper::InsertTo("tt_logs", ["authorId" => $authorId, "log_text" => $log_text, "datetime" => Engine::GetSiteTime()]);
    }

    /**
     * Returns all log string from database.
     *
     * @return array|false|int
     */
    public static function GetLogged(){
        $queryResponse = DataKeeper::MakeQuery("SELECT * FROM `tt_logs` ORDER BY `datetime` DESC", null, true);
        $result = [];
        foreach ($queryResponse as $log){
            $result[] = [
                "id" => $log["id"],
                "authorId" => $log["authorId"],
                "log_text" => $log["log_text"],
                "datetime" => $log["datetime"]
            ];
        }

        $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
        if (mysqli_connect_errno()) {
            ErrorManager::GenerateError(2);
            return ErrorManager::GetError();
        }

        if ($stmt = $mysqli->prepare("SELECT * FROM `tt_logs` ORDER BY `datetime` DESC")){
            $stmt->execute();
            $stmt->bind_result($id, $authorId, $log_text, $datetime);
            $result = [];
            while($stmt->fetch()){
                array_push($result, [
                    "id" => $id,
                    "authorId" => $authorId,
                    "datetime" => $datetime,
                    "log_text" => $log_text
                ]);
            }
            return $result;
        }

        return false;
    }

    /**
     * Write into error log a record.
     *
     * @param string $logText
     * @return bool Success or fail create record in log file.
     */
    public static function addErrorLog(string $logText) : bool {
        $nowTime = date('Y-m-d H:i:s');
        $selfIdentificator = UserAgent::IsSessionContinued()
            ? (new \Users\Models\User($_SESSION["uid"]))->getNickname()
            : $_SERVER["REMOTE_ADDR"];
        $lastNumber = self::getLogCount(self::ERRORS_LOGS) == 1
            ? ""
            : self::getLogCount(self::ERRORS_LOGS);

        if (file_exists(self::ERRORS_PATH . "errors.log")) {
            if (@self::getLogSize(self::ERRORS_LOGS) >= 15728640) {
                rename(self::ERRORS_PATH . "errors.log", "errors_$lastNumber.log");
            }
        }

        return file_put_contents(self::ERRORS_PATH . "errors.log", "[$nowTime] $selfIdentificator says: $logText" . PHP_EOL, FILE_APPEND);
    }

    /**
     * Write into access log a record.
     *
     * @param string $logText
     * @return bool Success or fail create record in log file.
     */
    public static function addAccessLog(string $logText) : bool {
        $nowTime = date('Y-m-d H:i:s');
        $selfIdentificator = UserAgent::IsSessionContinued()
            ? (new \Users\Models\User($_SESSION["uid"]))->getNickname()
            : $_SERVER["REMOTE_ADDR"];
        $lastNumber = self::getLogCount(self::ACCESSES_LOGS) == 1
            ? ""
            : self::getLogCount(self::ACCESSES_LOGS);

        if (file_exists(self::ACCESSES_PATH . "access.log")) {
            if (@self::getLogSize(self::ACCESSES_LOGS) >= 15728640) {
                rename(self::ACCESSES_PATH . "access.log", "access_$lastNumber.log");
            }
        }

        return file_put_contents(self::ACCESSES_PATH . "access.log", "[$nowTime] $selfIdentificator says: $logText" . PHP_EOL, FILE_APPEND);
    }

    /**
     * Write into visitors log a record.
     *
     * @param string $logText
     * @return bool Success or fail create record in log file.
     */
    public static function addVisitLog(string $logText) : bool {
        $nowTime = date('Y-m-d H:i:s');
        $selfIdentificator = UserAgent::IsSessionContinued()
            ? (new \Users\Models\User($_SESSION["uid"]))->getNickname()
            : $_SERVER["REMOTE_ADDR"];
        $lastNumber = self::getLogCount(self::VISITORS_LOGS) == 1
            ? ""
            : self::getLogCount(self::VISITORS_LOGS);

        if (file_exists(self::VISITORS_PATH . "visitor.log")) {
            if (@self::getLogSize(self::VISITORS_LOGS) >= 15728640) {
                rename(self::VISITORS_PATH . "visitor.log", "visitor_$lastNumber.log");
            }
        }

        return file_put_contents(self::VISITORS_PATH . "visitor.log", "[$nowTime] $selfIdentificator says: $logText" . PHP_EOL, FILE_APPEND);
    }
}