<?php

namespace Guards {

    use Engine\Engine;
    use Engine\ErrorManager;
    use Engine\LanguageManager;
    use Users\User;
    use Users\UserAgent;

    class SocietyGuard
    {
        public static function IsBanned($var, $isIP = false)
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $query = ($isIP) ? "SELECT count(*) FROM `tt_banned` WHERE ? REGEXP `banned` AND `type` = ?" : "SELECT count(*) FROM `tt_banned` WHERE `banned` = ? AND `type`= ?";
            $type = ($isIP) ? 2 : 1;

            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("si", $var, $type);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                if ($count >= 1) return true;

            }

            $stmt->close();
            $mysqli->close();

            return false;
        }
        public static function Ban($id, $reason, $time = 1, $author)
        {
            if (self::IsBanned($id)) {
                ErrorManager::GenerateError(5);
                return ErrorManager::GetError();
            }
            if (!UserAgent::IsUserExist($id)) {
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_banned` (`banned`, `type`,`banned_time`, `unban_time`,`reason`, `author`) VALUE (?,?,?,?,?,?)")) {
                $bannedTime = time();
                if ($time == 0) $unbanTime = "0"; else $unbanTime = time() + $time;
                $type = 1;
                $stmt->bind_param("sisssi", $id, $type, $bannedTime, $unbanTime, $reason, $author);
                $stmt->execute();
                if ($stmt->errno) {
                    echo $stmt->error;
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();

                }

                return true;
            } else {
                return $mysqli->error;
            }

            return false;

        }
        public static function BanWithSearch($needle, $reason, $time = 1, $author) {
            //Поиск пользователей по шаблону.
            $needle = str_replace("*", "%", $needle);
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?")){
                $stmt->bind_param("s", $needle);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                } else {
                    $stmt->bind_result($banID);
                    while($stmt->fetch()){
                        self::Ban($banID, $reason, $time, $author);
                    }
                    return true;
                }
            }
            return False;
        }
        public static function BanIP($ip, $reason, $time = 1, $author)
        {
            if (self::IsBanned($ip, true)){
                ErrorManager::GenerateError(5);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_banned` (`banned`, `type`,`banned_time`, `unban_time`, `reason`, `author`) VALUE (?,?,?,?,?,?)")) {
                if ($time != 0) $unbanTime = time() + $time;
                else $unbanTime = 0;
                $time = time();
                $type = 2;
                $stmt->bind_param("sisssi", $ip, $type, $time, $unbanTime, $reason, $author);
                $stmt->execute();
                if ($stmt->errno) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }

                return true;
            }

            return false;

        }
        public static function Unban($id)
        {
            if (!self::IsBanned($id)) {
                ErrorManager::GenerateError(6);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                exit();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_banned` WHERE `banned` = ? AND `type`=?")) {
                $type = "1";
                $stmt->bind_param("ii", $id, $type);
                $stmt->execute();
                if (mysqli_stmt_errno($stmt)) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return True;
            }

            return False;
        }
        public static function UnbanIP($ip)
        {
            if (!self::IsBanned($ip, true)) {
                ErrorManager::GenerateError(6);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_banned` WHERE `banned` = ? AND `type`=?")) {
                $type = 2;
                $stmt->bind_param("si", $ip, $type);
                $stmt->execute();
                if ($stmt->errno) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();

                }
                return True;
            }

            return False;
        }
        public static function GetBanUserList($page = 1)
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $lowBorder = ($page - 1) * 50;
            $highBorder = $page * 50;
            $type = 1;
            $query = "SELECT `banned` FROM `tt_banned` WHERE `type`=? LIMIT $lowBorder, $highBorder";

            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("i", $type);
                $stmt->execute();
                if (mysqli_stmt_errno($stmt)) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id);
                $result = array();
                while ($stmt->fetch()) {
                    array_push($result, $id);
                }
                return $result;
            } else {
                ErrorManager::GenerateError(9);
                return $mysqli->error;
            }
        }
        public static function GetBanUserParam($idUser, $param)
        {
            if (!self::IsBanned($idUser)) {
                ErrorManager::GenerateError(6);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                exit();
            }

            if ($stmt = $mysqli->prepare("SELECT $param FROM `tt_banned` WHERE `banned`=? AND `type`=?")) {
                $type = 1;
                $stmt->bind_param("ii", $idUser, $type);
                $stmt->execute();
                if (mysqli_stmt_errno($stmt)) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($paramProp);
                $stmt->fetch();
                return $paramProp;
            }

            return False;
        }
        public static function GetBanListByParam($param, $page = 1)
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $lowBorder = ($page - 1) * 50;
            $highBorder = $page * 50;
            $query = $mysqli->query("SELECT * FROM `tt_banned` WHERE `banned` LIKE ? AND `type` = '1' LIMIT $lowBorder, $highBorder");
            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("s", $param);
                $stmt->execute();
                $stmt->bind_result($response);
                $result = array();
                while ($stmt->fetch()) {
                    array_push($result, $response);
                }
                return $result;
            }
            return false;
        }
        public static function GetBanListByParams($params, $page = 1)
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            $lowBorder = ($page - 1) * 50;
            $highBorder = $page * 50;

            if ($params["nickname"] == "") $params["nickname"] = "%";
            elseif (strstr($params["nickname"], "*") === FALSE) $params["nickname"] = UserAgent::GetUserId($params["nickname"]);
            else $usersId = UserAgent::FindUsersBySNickname($params["nickname"]);

            if ($params["reason"] == "") $params["reason"] = "%";
            else $params["reason"] = str_replace("*", "%", $params["reason"]);

            if ($stmt = $mysqli->prepare("SELECT `banned` FROM `tt_banned` WHERE `reason` LIKE ? AND `type` = 1 LIMIT $lowBorder, $highBorder")){
                $stmt->bind_param("s", $params["reason"]);
                $stmt->execute();
                $result = array();
                $stmt->bind_result($response);
                while ($stmt->fetch()) {
                    if (isset($usersId)) {
                        if (in_array($response, $usersId))
                            array_push($result, $response);
                    }
                    else array_push($result, $response);
                }
                return $result;
            }
            return false;
        }
        public static function GetIPBanList($page = 1){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            $lowBorder = ($page - 1) * 50;
            $highBorder = $page * 50;

            if ($stmt = $mysqli->prepare("SELECT `banned` FROM `tt_banned` WHERE type=? LIMIT $lowBorder, $highBorder")){
                $type = 2;
                $stmt->bind_param("i", $type);
                $stmt->execute();
                $stmt->bind_result($resIP);
                $result = array();
                while ($stmt->fetch()){
                    array_push($result, $resIP);
                }
                return $result;
            }
            $stmt->close();
            $mysqli->close();
            return false;
        }
        public static function GetIPBanParam($ip, $param)
        {
            if (!self::IsBanned($ip, true)) {
                ErrorManager::GenerateError(6);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                exit();
            }

            if ($stmt = $mysqli->prepare("SELECT $param FROM `tt_banned` WHERE ? REGEXP `banned` AND `type`=?")) {
                $type = 2;
                $stmt->bind_param("si", $ip, $type);
                $stmt->execute();
                if (mysqli_stmt_errno($stmt)) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($paramProp);
                $stmt->fetch();
                return $paramProp;
            }

            return False;
        }
    }

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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM `tt_reports` WHERE `id`=?")){
                $stmt->bind_param("i", $reportId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id, $status, $theme, $author, $shortMessage, $message,
                    $answer, $createDate, $closeDate, $viewed);
                $stmt->fetch();
                $this->reportId = $reportId;
                $this->reportStatus = $status;
                $this->reportTheme = $theme;
                $this->reportAuthorId = $author;
                $this->reportShortMessage = $shortMessage;
                $this->reportMessage = $message;
                $this->reportAnswerId = $answer;
                $this->reportCreateDate = $createDate;
                $this->reportCloseDate = $closeDate;
                $this->reportIsViewed = $viewed;

                $this->reportAuthor = new User($this->reportAuthorId);
                $this->reportAnswerAuthor = new User(self::GetAnswerParam($answer, "authorId"));

            }

            $stmt->close();

            if ($stmt = $mysqli->prepare("SELECT `addedUID` FROM `tt_reportda` WHERE `reportId`=?")){
                $stmt->bind_param("i", $reportId);
                $stmt->execute();
                if ($stmt->errno){
                    $this->reportAddedInDiscuse = $stmt->error;
                }
                $stmt->bind_result($var);
                while($stmt->fetch()){
                    array_push($this->reportAddedInDiscuse, $var);
                }
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
        public function getMark(){
            return $this->reportMark;
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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $lowBorder = ($page - 1) * 12;
            $highBorder = $page * 12;

            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_reportanswers` WHERE `reportId`=? AND `id` != (SELECT `answerId` FROM `tt_reports` WHERE `id`=?) LIMIT $lowBorder,$highBorder")){
                $stmt->bind_param("ii", $this->reportId, $this->reportId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $result = array();
                $stmt->bind_result($answerId);
                while ($stmt->fetch()){
                    array_push($result, $answerId);
                }
                return $result;
            }
            return false;
        }

        public function setMark($markInt){
            return self::ChangeReportParam($this->reportId, "mark", $markInt);
        }
        public function setViewed(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_reports` SET `viewed`=?, `status`=? WHERE `id`=?")){
                $v = 1;
                $stmt->bind_param("iii", $v, $v, $this->reportId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            return false;
        }

        public function isAdded($userId){
            if (in_array($userId, $this->getAddedToDiscuse())) return true;
            else return false;
        }
    }

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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM `tt_reportanswers` WHERE `id`=?")){
                $stmt->bind_param("i", $commentId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($ansId, $reportId, $authorId, $createDate, $message, $edit_date, $reason_edit, $lasteditor);
                $stmt->fetch();
                $this->answerId = $ansId;
                $this->answerAuthorId = $authorId;
                $this->parentReportId = $reportId;
                $this->answerCreateDate = $createDate;
                $this->answerMessage = $message;
                $this->answerEditDate = $edit_date;
                $this->answerEditReason = $reason_edit;
                $this->answerLastEditorId = $lasteditor;
                $this->parentReport = new Report($reportId);
                $this->authorUser = new User($authorId);
                if ($lasteditor != 0) $this->lastEditor = new User($lasteditor);
            }
            return false;
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_reportanswers` SET `message`=?, `edit_date`=?, `reason_edit`=?, `last_editorId`=? WHERE `id`=?")){
                $date = date("Y-m-d", time());
                $stmt->bind_param("si", $newText, $date, $reason, $editorId, $this->answerId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            return false;
        }
    }

    class ReportAgent
    {
        private static function isAnswerExists($answerId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_reportanswers` WHERE `id`=?")){
                $stmt->bind_param("i", $answerId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($result);
                $stmt->fetch();
                return $result;
            }
            return false;
        }
        private static function isAnswerSolve($answerId){
            if (!self::isAnswerExists($answerId)){
                ErrorManager::GenerateError(30);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_reports` WHERE `answerId`=?")){
                $stmt->bind_param("i", $answerId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($result);
                $stmt->fetch();
                if (!empty($result)) return true;
                else return false;
            }
            return false;
        }

        public static function isAddedToDiscusse($reportId, $id){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_reportda` WHERE `addedUID` = ? AND `reportId` = ?")){
                $stmt->bind_param("ii", $id, $reportId );
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($v);
                $stmt->fetch();
                if ($v) return true;
                else return false;
            }
            return false;
        }
        public static function isReportExists($reportId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_reports` WHERE `id`=?")){
                $stmt->bind_param("i", $reportId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($result);
                $stmt->fetch();
                return $result;
            }
            return false;
        }

        public static function CreateAnswer($authorId, $text, $reportId){
            if (!self::isReportExists($reportId)){
                ErrorManager::GenerateError(29);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_reportanswers` (`id`, `reportId`, `authorId`, `create_date`, `message`) VALUE (NULL,?,?,?,?)")){
                $date = date("Y-m-d", time());
                $stmt->bind_param("iiss", $reportId, $authorId, $date, $text);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                self::ChangeReportParam($reportId, "viewed", 0);
                return true;
            }
            return false;
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_reportanswers` WHERE `id`=?")){
                $stmt->bind_param("i", $answerId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            return false;
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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_reportanswers` SET `message`=?, `edit_date`=?, `reason_edit`=?, `last_editorId`=? WHERE `id`=?")){
                $date = date("Y-m-d H:m:s", Engine::GetSiteTime());
                $stmt->bind_param("sssii", $newText, $date, $reasonEdit, $editorId, $answerId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            return false;
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_reports` SET `answerId`=?, `status`=?, `close_date`=? WHERE `id`=?")){
                $stat = 2;
                $date = date("Y-m-d", time());
                $stmt->bind_param("iisi", $answerId, $stat, $date, $idReport);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            return false;
        }
        public static function GetAnswerParam($answerId, $param){
            if (!self::isAnswerExists($answerId)){
                ErrorManager::GenerateError(30);
                return ErrorManager::GetError();
            }
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `$param` FROM `tt_reportanswers` WHERE `id` = ? ")){
                $stmt->bind_param("i", $answerId);
                $stmt->execute();
                if($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($result);
                $stmt->fetch();
                return $result;
            }
            return false;

        }

        public static function CreateReport($author, $theme, $shortMessage, $message){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ( $stmt = $mysqli->prepare("INSERT INTO `tt_reports` (`theme`, `author`, `short_message`, `message`, `create_date`) VALUE (?,?,?,?,?)")){
                $date = date("Y-m-d", time());
                $message = nl2br($message);
                $stmt->bind_param("sisss", $theme, $author, $shortMessage, $message, $date);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return $stmt->insert_id;
            }
            return false;
        }
        public static function DeleteReport($reportId){
            if (!self::isReportExists($reportId)){
                ErrorManager::GenerateError(29);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_reports` WHERE `id`=?")){
                $stmt->bind_param("i", $reportId);
                $stmt->execute();
                $stmt->prepare("DELETE FROM `tt_reportanswers` WHERE `reportId`=?");
                $stmt->bind_param("i", $reportId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return True;
            }
            return false;
        }
        public static function ChangeReportParam($idReport, $param, $newValue){
            if (in_array($param, ["create_date", "id", "close_date"])) return false;

            if (!self::isReportExists($idReport)){
                ErrorManager::GenerateError(29);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_reports` SET $param=? WHERE `id`=?")){
                $stmt->bind_param("si", $newValue, $idReport);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            $stmt->close();
            $mysqli->close();

            return false;
        }
        public static function GetReportsCount(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_reports`")){
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($var);
                $stmt->fetch();
                return $var;
            }
            return 0;
        }
        public static function GetReportsCountWithUser($authorId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT (SELECT count(*) FROM `tt_reports` WHERE `author`=?) + (SELECT count(*) FROM `tt_reportda` WHERE `addedUID`=?)")){
                $stmt->bind_param("ii", $authorId, $authorId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($var);
                $stmt->fetch();
                return $var;
            }
            return 0;
        }
        public static function GetReportsList($page = 1){
            if ($page < 1)
                return false;

            $lowBorder = ($page - 1) * 50;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_reports` ORDER BY `id` DESC LIMIT $lowBorder,50")){
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $result = array();
                $stmt->bind_result($ids);
                while ($stmt->fetch()){
                    array_push($result, $ids);
                }
                return $result;
            }
            return false;
        }
        public static function GetReportsListByAuthor($authorId, $page = 1){
            {
                $lowBorder = ($page - 1) * 20;

                $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

                if ($mysqli->errno){
                    ErrorManager::GenerateError(2);
                    return ErrorManager::GetError();
                }

                if ($stmt = $mysqli->prepare("(SELECT `id` FROM `tt_reports` WHERE `author` = ?) UNION (SELECT `reportId` FROM `tt_reportda` WHERE `addedUID` = ?) ORDER BY `id` DESC LIMIT $lowBorder, 20")){
                    $stmt->bind_param("ii", $authorId,$authorId);
                    $stmt->execute();
                    if ($stmt->errno){
                        ErrorManager::GenerateError(9);
                        return ErrorManager::GetError();
                    }
                    $result = array();
                    $stmt->bind_result($ids);
                    while ($stmt->fetch()){
                        array_push($result, $ids);
                    }
                    return $result;
                }
                return false;
            }
        }
        public static function GetReportParam($reportId, $param){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `$param` FROM `tt_reports` WHERE `id` = ? ")){
                $stmt->bind_param("i", $reportId);
                $stmt->execute();
                if($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($result);
                $stmt->fetch();
                return $result;
            }
            return false;

        }
        public static function GetUnreadedReportsCount(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_reports` WHERE `viewed`=?")){
                $v = 0;
                $stmt->bind_param("i", $v);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($r);
                $stmt->fetch();
                return $r;
            }
            return false;
        }
        public static function GetReport($reportId){
            if (!ReportAgent::isReportExists($reportId)) return false;
            else return new Report($reportId);
        }

        public static function AddToDiscusse($reportId, $id, $addedBy){
            if (ReportAgent::isAddedToDiscusse($reportId, $id)) return false;
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_reportda` (`reportId`, `addedUID`, `addedByUID`) VALUE (?,?,?)")){
                $stmt->bind_param("iii", $reportId, $id, $addedBy);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            return false;
        }
        public static function RemoveFromDiscusse($reportId, $id){
            if (!ReportAgent::isAddedToDiscusse($reportId, $id)) return false;
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_reportda` WHERE `reportId`=? AND `addedUID`=?")){
                $stmt->bind_param("ii", $reportId, $id);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            return false;
        }

    }

    class CaptchaMen{

        /* Первым делом используем GenerateCaptcha, чтобы сгенерировать код.
         * Затем используем FetchCaptcha, чтобы внести капчу в бд.
         * Только затем (!) используем GenerateImage.
         */
        private static $captchaHash;
        private static $captchaIDHash;
        private static $captchaFetched = False;
        private static $captchaType;

        private static function GetCaptcha($id, $type){
            if (empty($id)) exit;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $result1 = '';
            $stmt = $mysqli->prepare("SELECT `captcha` FROM `tt_captcha` WHERE id_hash=? and `type`=?");
            $stmt->bind_param("ss", $id, $type);
            $stmt->execute();
            $stmt->bind_result($result);
            $stmt->fetch();
            if ($result != '') $result1 = $result;
            else{ ErrorManager::GenerateError(8); $result = False;}

            $stmt->close();
            $mysqli->close();

            if ($result == False) return ErrorManager::GetError();
            else return $result1;

        }
        //Function to generate captcha.
        public static function GenerateCaptcha(){

            self::$captchaFetched = False;
            $captcha = Engine::RandomGen();
            self::$captchaIDHash = hash("sha1", Engine::RandomGen());
            if(self::$captchaHash = $captcha)
                if (!empty(self::$captchaHash)) return self::$captchaIDHash;
                else return False;

        }
        public static function FetchCaptcha($type){

            /* Types:
             * 1. Registration
             * 2. Authorization
             * 3. Send message.
             * 4. Reputation change
             */
            if (empty(self::$captchaHash) || empty($type)){ ErrorManager::GenerateError(8); return ErrorManager::GetError(); }

            self::$captchaType = $type;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_captcha` (`id_hash`, `captcha`, `type`, `createTime`, `picName`) VALUE (?,?,?,?,?)")) {
                $time = time();
                $imageName = Engine::RandomGen(8);
                $stmt->bind_param("sssis", self::$captchaIDHash, self::$captchaHash, $type, $time, $imageName);
                $stmt->execute();

                if (mysqli_stmt_errno($stmt)) {
                    $stmt->close();
                    $mysqli->close();
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }

            } else {
                ErrorManager::GenerateError(9);
                return ErrorManager::GetError();
            }

            $stmt->close();
            $mysqli->close();
            self::$captchaFetched = True;
            return $imageName;

        }
        public static function GenerateImage($imageName){
            if (empty(self::$captchaIDHash) || self::$captchaFetched == False){
                ErrorManager::GenerateError(8);
                return ErrorManager::GetError();
            }

            if (!$image = imagecreatetruecolor(100,35)){
                ErrorManager::GenerateError(14);
                return ErrorManager::GetError();
            }

            imagefill($image, 0, 0, imagecolorallocate($image, 255,255,255));
            for($i = 0; $i <= 8; $i++)
                imageline($image, rand(0, 35), rand(0, 35), rand(0, 100), rand(0, 100), imagecolorallocate($image, rand(0,255),rand(0,255),rand(0,255)));
            imagettftext($image, 12, 0, 8, 23, imagecolorallocate($image, 0x00, 0x00, 0x00), $_SERVER["DOCUMENT_ROOT"]."/engine/captchas/font.ttf", CaptchaMen::$captchaHash);
            imagepng($image, $_SERVER["DOCUMENT_ROOT"]."/engine/captchas/".$imageName.".png");
            return "/engine/captchas/".$imageName.".png";
        }
        public static function CheckCaptcha($typedCaptcha, $captchaID, $type){
            if (empty($captchaID) || empty($type) || empty($typedCaptcha)) return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_captcha` WHERE `type`=? AND `captcha` LIKE ? AND `id_hash`=?")){
                $stmt->bind_param("iss", $type, $typedCaptcha, $captchaID);
                $stmt->execute();
                $stmt->bind_result($r);
                $stmt->fetch();
                if ($r == 0) return false;
                else return true;
            }
            return false;
        }
        public static function RemoveCaptcha($id){

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $time = time()-600;
            if ($stmt = $mysqli->prepare("SELECT picName FROM `tt_captcha` WHERE createTime < ?")) {
                $stmt->bind_param("s", $time);
                $stmt->execute();
                $stmt->bind_result($picName);
                while ($stmt->fetch()) {
                    unlink("./captchas/" . $picName . ".png");
                }
                $stmt->close();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_captcha` WHERE createTime < ?")) {
                $stmt->bind_param("s", $time);
                $stmt->execute();
                $stmt->close();
            }

            if ($stmt = $mysqli->prepare("SELECT picName FROM 'tt_captcha' WHERE `id_hash`=?")) {
                $stmt->bind_param("s", $id);
                $stmt->execute();
                $stmt->bind_result($r);
                $stmt->fetch();
                unlink("./captchas/" . $r . ".png");
                $stmt->close();
            }

            $stmt = $mysqli->prepare("DELETE FROM `tt_captcha` WHERE id_hash=?");
            $stmt->bind_param("s", $id);
            $stmt->execute();

            if (mysqli_stmt_errno($stmt)){
                $stmt->close();
                $mysqli->close();
                ErrorManager::GenerateError(9);
                return ErrorManager::GetError();
            }

            $stmt->close();
            $mysqli->close();
            return True;
        }

    }

    class Logger{
        public static function LogAction($authorId, $log_text){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            $dataTime = Engine::GetSiteTime();

            if ($stmt = $mysqli->prepare("INSERT INTO tt_logs (authorId, log_text, `datetime`) VALUE (?,?,?)")){
                $stmt->bind_param("isi", $authorId,$log_text, $dataTime);
                $stmt->execute();
                return true;
            }
            return false;
        }

        public static function GetLogged(){
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
    }
}