<?php

namespace Guards {

    use Engine\DataKeeper;
    use Engine\Engine;
    use Engine\ErrorManager;
    use Engine\LanguageManager;
    use Users\User;
    use Users\UserAgent;

    class SocietyGuard
    {
        public static function IsBanned($var, $isIP = false)
        {
            $type  = $isIP ? 2 : 1;
            $query = $isIP ? DataKeeper::MakeQuery("SELECT count(*) FROM `tt_banned` WHERE ? REGEXP `banned` AND `type` = ?", [$var, $type]) :
                             DataKeeper::MakeQuery("SELECT count(*) FROM `tt_banned` WHERE `banned` = ? AND `type` = ?", [$var, $type]);
            if ($query["count(*)"] >= 1)
                return true;
            else
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

            $result = DataKeeper::InsertTo("tt_banned", ["banned" => $id,
                                                            "type" => 1,
                                                            "banned_time" => Engine::GetSiteTime(),
                                                            "unban_time" => $time == 0 ? 0 : Engine::GetSiteTime() + $time,
                                                            "reason" => $reason,
                                                            "author" => $author]);
            if ($result >= 0){
                return true;
            } else
                return false;
        }
        public static function BanWithSearch($needle, $reason, $time = 1, $author) {
            //Поиск пользователей по шаблону.
            $needle = str_replace("*", "%", $needle);

            $haystack = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?", [$needle]);
            $banID    = $haystack["id"];
            $result   = self::Ban($banID, $reason, $time, $author);

            if ($result == 0)
                return false;

            return true;
        }
        public static function BanIP($ip, $reason, $time = 1, $author)
        {
            if (self::IsBanned($ip, true)){
                ErrorManager::GenerateError(5);
                return ErrorManager::GetError();
            }

            $result = DataKeeper::InsertTo("tt_banned", ["banned" => $ip,
                                                            "type" => 2,
                                                            "banned_time" => Engine::GetSiteTime(),
                                                            "unban_time" => $time != 0 ? Engine::GetSiteTime() + $time : 0,
                                                            "reason" => $reason,
                                                            "author" => $author]);
            if ($result >= 0)
                return true;
            else
                return false;
        }
        public static function Unban($id)
        {
            if (!self::IsBanned($id)) {
                ErrorManager::GenerateError(6);
                return ErrorManager::GetError();
            }

            $result = DataKeeper::Delete("tt_banned", ["banned"=> $id, "type" => 1]);

            return $result == 0 ? false : true;
        }
        public static function UnbanIP($ip)
        {
            if (!self::IsBanned($ip, true)) {
                ErrorManager::GenerateError(6);
                return ErrorManager::GetError();
            }

            $result = DataKeeper::Delete("tt_banned", ["banned" => $ip, "type" => 2]);

            return $result == 0 ? false : true;
        }
        public static function GetBanUserList($page = 1)
        {
            $lowBorder = ($page - 1) * 50;
            $highBorder = $page * 50;

            return DataKeeper::MakeQuery("SELECT `banned` FROM `tt_banned` WHERE `type`=? LIMIT $lowBorder, $highBorder", ["1"], true);
        }
        public static function GetBanUserParam($idUser, $param)
        {
            if (!self::IsBanned($idUser)) {
                ErrorManager::GenerateError(6);
                return ErrorManager::GetError();
            }

            return DataKeeper::Get("tt_banned", [$param], ["banned" => $idUser, "type" => 1])[0][$param];
        }
        public static function GetBanListByParams($params, $page = 1)
        {
            $lowBorder = ($page - 1) * 50;
            $highBorder = $page * 50;

            if ($params["nickname"] == "") $params["nickname"] = "%";
            elseif (strstr($params["nickname"], "*") === FALSE) $params["nickname"] = UserAgent::GetUserId($params["nickname"]);
            else $usersId = UserAgent::FindUsersBySNickname($params["nickname"]);

            if ($params["reason"] == "") $params["reason"] = "%";
            else $params["reason"] = str_replace("*", "%", $params["reason"]);

            $queryResponse = DataKeeper::MakeQuery("SELECT `banned` FROM `tt_banned` WHERE `reason` LIKE ? AND `type` = ? LIMIT $lowBorder, $highBorder", [$params["reason"], 1], true);
            $result = [];
            foreach ($queryResponse as $response){
                if (isset($usersId)){
                    if (in_array($response, $usersId))
                        $result[] = $response;
                } else
                    $result[] = $response;
            }
            return $result;
        }
        public static function GetIPBanList($page = 1){
            $lowBorder = ($page - 1) * 50;
            $highBorder = $page * 50;

            return DataKeeper::MakeQuery("SELECT `banned` FROM `tt_banned` WHERE `type` = ? LIMIT $lowBorder, $highBorder", [2], true);
        }
        public static function GetIPBanParam($ip, $param)
        {
            if (!self::IsBanned($ip, true)) {
                ErrorManager::GenerateError(6);
                return ErrorManager::GetError();
            }

            $queryResponse = DataKeeper::MakeQuery("SELECT $param FROM `tt_banned` WHERE ? REGEXP `banned` AND `type` = ?", [$ip, 2])[$param];

            return $queryResponse;
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

    class CaptchaMen{
        private static $captchaHash;
        private static $captchaIDHash;
        private static $captchaFetched = False;
        private static $captchaType;

        private static function GetCaptcha($id, $type){
            if (empty($id)) exit;

            $queryResponse = DataKeeper::Get("tt_captcha", ["captcha"], ["id_hash" => $id, "type" => $type])[0]["captcha"];
            if ($queryResponse != '') return $queryResponse;
            else return false;
        }
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
            $imageName = Engine::RandomGen(8);

            DataKeeper::InsertTo("tt_captcha", ["id_hash" => self::$captchaIDHash,
                                                             "captcha" => strtoupper(self::$captchaHash),
                                                             "type" => $type,
                                                             "createTime" => Engine::GetSiteTime(),
                                                             "picName" => $imageName]);
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

            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_captcha` WHERE `type` = ? AND `captcha` LIKE ? AND `id_hash` = ?", [$type, strtoupper($typedCaptcha), $captchaID])["count(*)"] == 0 ? false : true;
        }
        public static function RemoveCaptcha(){
            $time = Engine::GetSiteTime() - 600;
            $queryResponse = DataKeeper::MakeQuery("SELECT `picName` FROM `tt_captcha` WHERE `createTime` < ?", [$time], true);

            foreach ($queryResponse as $captcha) {
                if (!unlink("../engine/captchas/" . $captcha["picName"] . ".png"))
                    continue;
            }

            return DataKeeper::Delete("tt_captcha", ["createTime" => $time]);
        }
    }

    class Logger{
        public static function LogAction($authorId, $log_text){
            return DataKeeper::InsertTo("tt_logs", ["authorId" => $authorId, "log_text" => $log_text, "datetime" => Engine::GetSiteTime()]);
        }

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
    }
}