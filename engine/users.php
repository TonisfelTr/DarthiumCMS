<?php
namespace Users {

    use Engine\DataKeeper;
    use Engine\Engine;
    use Engine\ErrorManager;
    use Engine\LanguageManager;
    use Engine\Mailer;
    use Engine\PluginManager;
    use Engine\Uploader;
    use Forum\ForumAgent;
    use Guards\SocietyGuard;

    class Group{
        private $gId;
        private $gName;
        private $gColor;
        private $gDescript;
        private $gPerms = array();

        public function __construct($groupId){
            $result          = DataKeeper::Get("tt_groups", ["*"], ["id" => $groupId])[0];
            $this->gId       = $groupId;
            $this->gName     = $result["name"];
            $this->gColor    = $result["color"];
            $this->gDescript = $result["descript"];
            $this->gPerms    = array(
                'enterpanel' => $result["enterpanel"],
                'change_engine_settings' => $result["change_engine_settings"],
                'offline_visiter' => $result["offline_visiter"],
                'rules_edit' => $result["rules_edit"],
                'change_template_design' => $result["change_template_design"],

                /***********************************************************
                 * Group permissions.                                      *
                 ***********************************************************/

                'change_perms' => $result["change_perms"],
                'group_create' => $result["group_create"],
                'group_delete' => $result["group_delete"],
                'group_change' => $result["group_change"],

                /************************************************************
                 * User permissions.                                        *
                 ************************************************************/

                'change_another_profiles' => $result["change_another_profiles"],
                'change_user_group' => $result["change_user_group"],
                'user_add' => $result["user_add"],
                'user_remove' => $result["user_remove"],
                'user_see_foreign' => $result["user_see_foreign"],
                'user_signs' => $result["user_signs"],
                'change_profile' => $result["change_profile"],
                'user_ban' => $result["user_ban"],
                'user_unban' => $result["user_unban"],
                'user_banip' => $result["user_banip"],
                'user_unbanip' => $result["user_unbanip"],

                /*************************************************************
                 * Reports permissions                                       *
                 *************************************************************/

                'report_create' => $result["report_create"],
                'report_foreign_remove' => $result["report_foreign_remove"],
                'report_talking' => $result["report_talking"],
                'report_remove' => $result["report_remove"],
                'report_edit' => $result["report_edit"],
                'report_foreign_edit' => $result["report_foreign_remove"],
                'report_answer_edit' => $result["report_answer_edit"],
                'report_foreign_answer_edit' => $result["report_anser_foreign_edit"],
                'report_close' => $result["report_close"],

                /*************************************************************
                 * Uploading permissions                                     *
                 *************************************************************/

                'upload_add' => $result["upload_add"],
                'upload_delete' => $result["upload_delete"],
                'upload_delete_foreign' => $result["upload_delete_foreign"],
                'upload_see_all' => $result["upload_see_all"],

                /*************************************************************
                 * Categories permissions                                    *
                 *************************************************************/

                'category_create' => $result["category_create"],
                'category_delete' => $result["category_delete"],
                'category_edit' => $result["category_edit"],
                'category_see_unpublic' => $result["category_see_unpublic"],
                'category_params_ignore' => $result["category_params_ignore"],

                /*************************************************************
                 * Topics permissions                                        *
                 *************************************************************/

                'topic_create' => $result["topic_create"],
                'topic_edit' => $result["topic_edit"],
                'topic_foreign_edit' => $result["topic_foreign_edit"],
                'topic_delete' => $result["topic_delete"],
                'topic_foreign_delete' => $result["topic_foreign_delete"],
                'topic_manage' => $result["topic_manage"],

                /*************************************************************
                 * Comments permissions                                      *
                 *************************************************************/

                'comment_create' => $result["comment_create"],
                'comment_edit' => $result["comment_edit"],
                'comment_foreign_edit' => $result["comment_foreign_edit"],
                'comment_delete' => $result["comment_delete"],
                'comment_foreign_delete' => $result["comment_foreing_delete"],

                /**************************************************************
                 * Permissions manage with static content              *
                 **************************************************************/

                'sc_create_pages' => $result["sc_create_pages"],
                'sc_edit_pages' => $result["sc_edit_pages"],
                'sc_remove_pages' => $result["sc_remove_pages"],
                'sc_design_edit' => $result["sc_design_edit"],

                /**************************************************************
                 * Other                                                      *
                 **************************************************************/

                'bmail_sende' => $result["bmail_sende"],
                'bmail_sends' => $result["bmail_sends"],
                'logs_see' => $result["logs_see"],
                'plugins_control' => $result["plugins_control"]
            );
            return $this;
        }
        public function getPermission($permValue){
            return $this->gPerms[$permValue];
        }
        public function getName(){
            return $this->gName;
        }
        public function getColor(){
            return $this->gColor;
        }
        public function getDescript(){
            return $this->gDescript;
        }
        public function getId(){
            return $this->gId;
        }
    }

    class User{

        private $uId;
        private $uNickname;
        private $uPassHash;
        private $uEmail;
        private $uGroupId;
        private $uActive;

        private $uSex;
        private $uHobbies;
        private $uName;
        private $uFrom;
        private $uLastDate;
        private $uLastIp;
        private $uRegDate;
        private $uRegIp;
        private $uBirth;
        private $uAvatar;
        private $uSignature;
        private $uAbout;
        private $uReferer = null;
        private $uVK;
        private $uSkype;
        private $uLastTime;

        private $uIsVKPublic;
        private $uIsSkypePublic;
        private $uIsEmailPublic;
        private $uIsBirthdayPublic;
        private $uIsAccountPublic;

        private $uReputation;
        private $uGroupOwner;
        private $uBlacklist;
        private $uPrivateMessages;
        private $uNotifications;
        private $uFriendList;
        private $uAdditionFields;

        public function __construct($userId)
        {
            $result = DataKeeper::Get("tt_users", ["*"], ["id" => $userId])[0];
            $this->uId = $result["id"];
            $this->uNickname = $result["nickname"];
            $this->uPassHash = $result["password"];
            $this->uEmail = $result["email"];
            $this->uGroupId = $result["group"];
            $this->uActive = $result["active"];
            $this->uRegIp = $result["regip"];
            $this->uRegDate = $result["regdate"];
            $this->uSex = $result["sex"];
            $this->uHobbies = $result["hobbies"];
            $this->uName = $result["realname"];
            $this->uFrom = $result["city"];
            $this->uLastDate = $result["lastdate"];
            $this->uLastTime = $result["lasttime"];
            $this->uLastIp = $result["lastip"];
            $this->uBirth = $result["birth"];
            $this->uAvatar = $result["avatar"];
            $this->uSignature = $result["signature"];
            $this->uAbout = $result["about"];
            $this->uReferer = ($result["referer"] <= 0) ? null : $result["referer"];
            $this->uVK = $result["vk"];
            $this->uSkype = $result["skype"];
            $this->uIsVKPublic = $result["public_vk"];
            $this->uIsBirthdayPublic = $result["public_birthday"];
            $this->uIsEmailPublic = $result["public_email"];
            $this->uIsSkypePublic = $result["public_skype"];
            $this->uIsAccountPublic = $result["public_account"];

            $this->uReputation = new UserReputationer($this);
            $this->uGroupOwner = new Group($this->uGroupId);
            $this->uBlacklist = new UserBlacklister($this->uId);
            $this->uPrivateMessages = new PrivateMessager($this->uId);
            $this->uNotifications = new UserNotificator($this->uId);
            $this->uFriendList = new UserFriendlist($this->uId);
            $this->uAdditionFields = UserAgent::GetAdditionalFieldsListOfUser($userId);

            return $this;
        }
        public function getId(){

            return $this->uId;
        }
        public function getNickname(){
            return $this->uNickname;
        }
        public function getEmail(){
            return $this->uEmail;
        }
        public function getActiveStatus(){
            return ($this->uActive === "TRUE") ? true : false;
        }
        public function getActivationCode(){
            return $this->uActive;
        }
        public function getGroupId(){
            return $this->uGroupId;
        }
        public function getRegIp(){
            return $this->uRegIp;
        }
        public function getRegDate(){
            return $this->uRegDate;
        }
        public function getLastDate(){
            return $this->uLastDate;
        }
        public function getLastIp(){
            return $this->uLastIp;
        }
        public function getSignature(){
            return $this->uSignature;
        }
        public function getSex(){
            return $this->uSex;
        }
        public function getHobbies(){
            return $this->uHobbies;
        }
        public function getFrom(){
            return $this->uFrom;
        }
        public function getAbout(){
            return $this->uAbout;
        }
        public function getReputation(){
            return $this->uReputation;
        }
        public function getBirth(){
            return $this->uBirth;
        }
        public function getRealName(){
            return $this->uName;
        }
        public function getReferer(){
           if (UserAgent::IsUserExist($this->uReferer)){
                 return new User($this->uReferer);
           } else return null;
        }
        public function getAvatar(){
            $avatar = $this->uAvatar;
            if ($avatar == 'no') $result = "../uploads/avatars/no.jpg";
            else $result = "../uploads/avatars/".$avatar;

            return $result;
        }
        public function getVK(){
            return $this->uVK;
        }
        public function getSkype(){
            return $this->uSkype;
        }
        public function getLastTime(){
            return $this->uLastTime;
        }
        public function getReportsCreatedCount(){
            $queryResponse = DataKeeper::Get("tt_reports", ["count(*)"], ["author" => $this->getId()]);
            if (empty($queryResponse))
                return 0;
            else
                return $queryResponse[0]["count(*)"];
        }
        public function getAdditionalFields(){
            return $this->uAdditionFields;
        }

        public function IsVKPublic(){
            return ($this->uIsVKPublic) ? true : false;
        }
        public function IsEmailPublic(){
            return ($this->uIsEmailPublic) ? true : false;
        }
        public function IsSkypePublic(){
            return ($this->uIsSkypePublic) ? true : false;
        }
        public function IsBirthdayPublic(){
            return ($this->uIsBirthdayPublic) ? true : false;
        }
        public function IsAccountPublic(){
            return $this->uIsAccountPublic;
        }
        public function isBanned(){
            if (SocietyGuard::IsBanned($this->getId()))
                return true;
            else return false;
        }

        public function UserGroup(){
            return $this->uGroupOwner;
        }
        public function Blacklister(){
            return $this->uBlacklist;
        }
        public function MessageManager(){
            return $this->uPrivateMessages;
        }
        public function Notifications(){
            return $this->uNotifications;
        }
        public function FriendList(){
            return $this->uFriendList;
        }

        public function Activate(){
            if ($this->getActiveStatus() != true)
                return UserAgent::ActivateAccount($this->getId(), $this->getActivationCode());
            else return false;
        }
        public function passChange($new, bool $relogin){
            return UserAgent::ChangeUserPassword($this->uId, $new, $relogin);
        }
        public function groupChange($groupId){
            if (UserAgent::ChangeUserParams($this->getId(), "group", $groupId)){
                $this->uGroupOwner = null;
                $this->uGroupOwner = new Group($groupId);
            } else return false;
            return false;
        }

    }
    class PrivateMessager{
        private $notReadCount = 0;
        private $incomeSize = 0;
        private $incomes = [];
        private $outcomeSize = 0;
        private $outcomes = [];
        private $binSize = 0;
        private $bin = [];
        private $sendedSize = 0;
        private $sended = [];
        private $userId;

        private function getStatusForMessage($id){
            $queryResponse = DataKeeper::Get("tt_pmessages", ["senderUID", "receiverUID"], ["id" => $id, "isVisible" => 1]);
            $sender        = $queryResponse[0]["senderUID"];
            $receiver      = $queryResponse[0]["receiverUID"];

            if ($sender == $this->userId) return "sender";
            elseif ($receiver == $this->userId) return "receiver";
            else return "nobody";

        }
        private function hasAccess($id){
            if (!in_array($this->getStatusForMessage($id), ["sender", "receiver"])) return false;
            else return true;
        }
        private function setRead($id){
            return DataKeeper::Update("tt_pmessages", ["isRead" => true], ["id" => $id]);
        }

        public function __construct($userId)
        {
            $this->userId = $userId;

            $queryResponse = DataKeeper::MakeQuery("SELECT * 
                                                           FROM `tt_pmessages`
                                                           WHERE `senderUID`=? OR `receiverUID`=? AND `isVisible`=?
                                                           ORDER BY `id` DESC", [$this->userId, $this->userId, 1], true);
            //var_dump($queryResponse);
            //exit;
            foreach ($queryResponse as $array) {
                $id = $array["id"];
                $senderUID = $array["senderUID"];
                $receiverUID = $array["receiverUID"];
                $subject = $array["subject"];
                $text = $array["text"];
                $isRead = $array["isRead"];
                $receiveTime = date("Y-m-d H:i:s", $array["receiveTime"]);
                $isRemovedForSender = $array["isRemovedForSender"];
                $isRemovedForReceiver = $array["isRemovedForReceiver"];
                $isVisible = $array["isVisible"];
                $isSaved = $array["isSaved"];

                if (($senderUID == $this->userId && $isRemovedForSender == true) || ($receiverUID == $this->userId && $isRemovedForReceiver == true)) {
                    array_push($this->bin, ["id" => $id,
                        "senderUID" => $senderUID,
                        "receiverUID" => $receiverUID,
                        "subject" => $subject,
                        "text" => $text,
                        "isRead" => $isRead,
                        "receiveTime" => $receiveTime,
                        "isRemoved" => true,
                        "isVisible" => 1,
                        "isSaved" => $isSaved]);
                    $this->binSize++;
                } elseif ($senderUID == $this->userId && !$isSaved && !$isRemovedForSender) {
                    array_push($this->sended, ["id" => $id,
                        "senderUID" => $senderUID,
                        "receiverUID" => $receiverUID,
                        "subject" => $subject,
                        "text" => $text,
                        "isRead" => $isRead,
                        "receiveTime" => $receiveTime,
                        "isRemove" => false,
                        "isVisible" => 1,
                        "isSaved" => $isSaved]);
                    $this->sendedSize++;
                } elseif ($senderUID == $this->userId && $isSaved && !$isRemovedForSender) {
                    array_push($this->outcomes, ["id" => $id,
                        "senderUID" => $senderUID,
                        "receiverUID" => $receiverUID,
                        "subject" => $subject,
                        "text" => $text,
                        "isRead" => $isRead,
                        "receiveTime" => $receiveTime,
                        "isRemove" => false,
                        "isVisible" => 1,
                        "isSaved" => $isSaved]);
                    $this->outcomeSize++;
                } elseif ($receiverUID == $this->userId && !$isSaved && !$isRemovedForReceiver) {
                    array_push($this->incomes, ["id" => $id,
                        "senderUID" => $senderUID,
                        "receiverUID" => $receiverUID,
                        "subject" => $subject,
                        "text" => $text,
                        "isRead" => $isRead,
                        "receiveTime" => $receiveTime,
                        "isRemove" => false,
                        "isVisible" => 1,
                        "isSaved" => false]);
                    $this->incomeSize++;
                    if ($isRead == false) $this->notReadCount++;
                }
            }
        }
        public function getNotReadCount(){
            return $this->notReadCount;
        }
        public function getIncomeSize(){
            return $this->incomeSize;
        }
        public function getOutcomeSize(){
            return $this->outcomeSize;
        }
        public function getBinSize(){
            return $this->binSize;
        }
        public function getSendedSize(){
            return $this->sendedSize;
        }
        public function incomes($index = -1){
            if ($index == -1) return $this->incomes;
            else return $this->incomes[$index];
        }
        public function outcomes($index = -1){
            if ($index == -1) return $this->outcomes;
            else return $this->outcomes[$index];
        }
        public function bin($index = -1){
            if ($index == -1) return $this->bin;
            else return $this->bin[$index];
        }
        public function sended($index = -1){
            if ($index == -1) return $this->sended;
            else return $this->sended[$index];
        }
        public function send($receiverUID, $subject, $text){
            return DataKeeper::InsertTo("tt_pmessages", ["senderUID" => $this->userId,
                                                                "receiverUID" => $receiverUID,
                                                                "subject" => $subject,
                                                                "text" => $text,
                                                                "receiveTime" => Engine::GetSiteTime()]);
        }
        public function remove($id){
            if ($this->getStatusForMessage($id) == "nobody") return false;

            if ($this->getStatusForMessage($id) == "receiver"){
                return DataKeeper::Update("tt_pmessages", ["isRemovedForReceiver" => 1], ["id" => $id]);
            }
            if ($this->getStatusForMessage($id) == "sender"){
                return DataKeeper::Update("tt_pmessages", ["isRemovedForSender" => 1], ["id" => $id]);
            }
        }
        public function save($id){
            if ($this->getStatusForMessage($id) == "nobody") return false;

            return DataKeeper::Update("tt_pmessages", ["isSaved" => 1], ["id" => $id]);
        }
        public function read($id){
            if (!$this->hasAccess($id)){
                return false;
            }

            for ($i = 0; $i < $this->getIncomeSize(); $i++){
                if ($this->incomes[$i]["id"] == $id) {
                    $this->setRead($id);
                    return $this->incomes[$i];
                }
            }

            for ($i = 0; $i < $this->getOutcomeSize(); $i++){
                if ($this->outcomes[$i]["id"] == $id) return $this->outcomes[$i];
            }

            for ($i = 0; $i < $this->getSendedSize(); $i++){
                if ($this->sended[$i]["id"] == $id) return $this->sended[$i];
            }

            for ($i = 0; $i < $this->getBinSize(); $i++){
                if ($this->bin[$i]["id"] == $id) return $this->bin[$i];
            }

            return false;
        }
        public function restore($id)
        {
            if ($this->getStatusForMessage($id) == "nobody") return false;

            if ($this->getStatusForMessage($id) == "receiver") {
                return DataKeeper::Update("tt_pmessages", ["isRemovedForReceiver" => 0], ["id" => $id]);
            }
            if ($this->getStatusForMessage($id) == "sender") {
                return DataKeeper::Update("tt_pmessages", ["isRemovedForSender" => 0], ["id" => $id]);
            }
        }
    }
    class UserReputationer{
        private $userId;
        private $userReputationPoint;
        private $userReputationChanges;

        public function __construct(User $user)
        {
            $this->userId = $user->getId();
            $this->userReputationPoint = 0;
            $this->userReputationChanges = array();

            $reputations = DataKeeper::Get("tt_reputation", ["*"], ["uid" => $this->userId]);

            foreach($reputations as $reputation){
                $this->userReputationChanges[] = [
                    "authorId" => $reputation["authorId"],
                    "type" => $reputation["type"],
                    "comment" => $reputation["comment"],
                    "createDate" => $reputation["createDate"]
                ];
                $this->userReputationPoint += ($reputation["type"] == 0) ? -1 : 1;
            }
        }
        public function getReputationChangeByIndex(int $index){
            return $this->userReputationChanges[$index];
        }
        public function getReputationArray(){
            return $this->userReputationChanges;
        }
        public function getReputationPoints(){
            return $this->userReputationPoint;
        }
        public function addReputationPoint($authorId, $comment, $type){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_reputation` (`id`, `uid`, `authorId`, `type`, `comment`, `createDate`) VALUE (NULL, ?,?,?,?,?)")){
                $time = Engine::GetSiteTime();
                $stmt->bind_param("iiisi", $this->userId, $authorId, $type, $comment, $time);
                $stmt->execute();
                return true;
            }
            $stmt->close();
            $mysqli->close();
            return false;
        }
        public function getPointsFromUserCount(int $fromUserId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_reputation` WHERE `authorId` = ? AND `uid` = ?")){
                $stmt->bind_param("ii", $fromUserId, $this->userId);
                $stmt->execute();
                $stmt->bind_result($result);
                $stmt->fetch();
                return $result;
            }
            return false;
        }
        public function removeReputationPoint($commentId){
            return DataKeeper::Delete("tt_reputation", ["id" => $commentId]);
        }
        public function changeReputationComment(int $commentId, string $newComment){
            return DataKeeper::Update("tt_reputation", ["comment" => $newComment], ["id" => $commentId]);
        }
    }
    class UserBlacklister{
        private $uId;
        private $uArray = [];

        public function __construct($userId)
        {
            $this->uId = $userId;

            $blacked = DataKeeper::Get("tt_blacklisted", ["blockId", "comment", "addedtime"], ["authorId" => $this->uId]);

            foreach ($blacked as $person){
                $this->uArray[] = ["bid" => $person["blockId"], "comment" => $person["comment"], "addedtime" => $person["addedtime"]];
            }
        }
        public function getList(){
            return $this->uArray;
        }
        public function add(int $userId, string $comment = ""){
            return DataKeeper::InsertTo("tt_blacklisted", ["authorId" => $this->uId, "blockId" => $userId, "comment" => $comment, "date" => date("Y-m-d H:i:s", Engine::GetSiteTime())]);
        }
        public function isBlocked(int $userId){
            foreach ($this->uArray as $person){
                if (in_array($userId, $person))
                    return true;
            }
            return false;
        }
        public function remove(int $userId) : bool{
            if (!$this->isBlocked($userId)) return false;

            return DataKeeper::Delete("tt_blacklisted", ["authorId" => $this->uId, "blockId" => $userId]);
        }
        public function getBlockedInfo($userId){
            if (!$this->isBlocked($userId)) return false;

            foreach ($this->uArray as $info){
                if (in_array($userId, $info))
                    return $info;
            }

            return false;
        }
    }
    class UserNotificator{
        private $userId;
        private $notificationsList = [];
        private $notificationsCount = 0;
        private $notificationsUnreadCount = 0;

        public function __construct($userId)
        {
            $this->userId = $userId;
            $notifications = DataKeeper::MakeQuery("SELECT * FROM `tt_notifications` WHERE `toUid` = ? ORDER BY `createTime` DESC", [$this->userId], true);

            foreach ($notifications as $notification) {
                $this->notificationsList[] = [
                    "id" => $notification["id"],
                    "createTime" => $notification["createTime"],
                    "fromUid" => $notification["fromUid"],
                    "type" => $notification["type"],
                    "isRead" => $notification["isRead"],
                    "subject" => $notification["subject"]
                ];
                $this->notificationsCount++;
                if (!$notification["isRead"]) $this->notificationsUnreadCount++;
            }
        }
        public function getNotifies(){
            return $this->notificationsList;
        }
        public function getNotifiesCount(){
            return $this->notificationsCount;
        }
        public function getNotificationsUnreadCount(){
            return $this->notificationsUnreadCount;
        }
        /** Create a notification.
         * It has the notification code - encrypting notification message.
         * Code list:
         * 1. Added to report discussing. +
         * 2. Have offered to be friends. +
         * 3. Profile change by administrator. +
         * 4. Deleted from report discussing. +
         * 5. New answer in report discussing. +
         * 6. New answer in own topic. +
         * 7. Have liked a topic. +
         * 8. Have moved a topic. +
         * 9. Have deleted a topic. +
         * 10. Have changed text of report. +
         * 11. Have deleted report. +
         * 12. Have edited a topic. +
         * 13. Have changed status of topic. +
         * 14. Somebody has been signed up indicated referrer. +
         * 15. Have closed report. +
         * 16. Your answer in report discussion has been deleted. +
         * 17. Answer in report discussion has been changed. +
         * 18. Foreign added to discusse. +
         * 19. Foreign removed from discusse. +
         * 20. Closed foreign report. +
         * 21. Mentioned in topic. +
         * 22. Mentioned in comment. +
         *
         * @param $notificationCode integer Notification code.
         * @param $fromUid integer User ID by creating the notification.
         * @param $subject integer ID of subject of action.
         * @return bool|int|string
         */
        public function createNotify($notificationCode, $fromUid, int $subject  = 0){
            UserAgent::ClearNotifications();

            return DataKeeper::InsertTo("tt_notifications", ["toUid" => $this->userId,
                                                                   "type" => $notificationCode,
                                                                   "fromUid" => $fromUid,
                                                                   "createTime" => Engine::GetSiteTime(),
                                                                   "isRead" => 0,
                                                                   "subject" => $subject]);
        }
        public function setRead($id){
            return DataKeeper::Update("tt_notifications", ["isRead" => 1], ["id" => $id]);
        }
        public function getUserId(){
            return $this->userId;
        }
    }
    class UserFriendlist{
        private $userId;
        private $friendCount = 0;
        private $friendList = [];

        public function __construct($userId)
        {
            $this->userId = $userId;

            $friends = DataKeeper::Get("tt_friends", ["friendId", "regdate"], ["fhost" => $userId]);
            foreach ($friends as $friend) {
                $this->friendList[] = ["fhost" => $this->userId, "friendId" => $friend["friendId"], "regdate" => $friend["regdate"]];
                $this->friendCount++;
            }
        }
        public function isFriend($userId){
            if (DataKeeper::MakeQuery("SELECT count(*) FROM tt_friends WHERE `fhost`=? AND `friendId`=?", [$this->userId, $userId])["count(*)"] >= 1)
                return true;
            else
                return false;
        }
        public function getFriendsCount(){
            return $this->friendCount;
        }
        public function getFriendsList(){
            return $this->friendList;
        }
        public function getFriendFromDB($friendId){
            if (!$this->isFriend($friendId)) return false;

            $friend = DataKeeper::Get("tt_friends", ["regdate"], ["friendId" => $friendId, "fhost" => $this->userId]);
            return ["fhost" => $this->userId,
                "friendId" => $friendId,
                "regdate" => $friend[0]["regdate"]];
        }
        public function getOnlineFriendCount(){
            return UserAgent::GetOnlineFriendsCount($this->userId);
        }
        public function getOnlineFriends(){
            return UserAgent::GetOnlineFriends($this->userId);
        }
        public function addFriend($friendId){
            if ($this->isFriend($friendId)) return false;

            return DataKeeper::InsertTo("tt_friends", ["fhost" => $this->userId, "friendId" => $friendId, "regdate" => Engine::GetSiteTime()]);
        }
        public function deleteFriend($friendId){
            if (!$this->isFriend($friendId)) return false;

            return DataKeeper::Delete("tt_friends", ["fhost" => $this->userId, "friendId" => $friendId]);
        }
    }

    class UserAgent
    {
        private static $IsAuthorized = False;
        private static $authID = 0;

        private static function IsValidNick($str){
            if (strlen($str) > 16) return false;
            if (strlen($str) < 4) return false;
            if (preg_match("/^[a-zA-Z0-9_]+$/", $str) === 1) return true;
            else return false;
        }
        private static function IsEmailValid($str){
            if (strlen($str) < 2) return False;
            if (preg_match("/[a-z0-9A-Z.@\-_]+/", $str) == 1) return True;
            else return False;
        }
        private static function UpdateLastData($id){
            $date = date("Y-m-d", Engine::GetSiteTime());
            $time = Engine::GetSiteTime();

            return DataKeeper::Update("tt_users", ["lastip" => $_SERVER["REMOTE_ADDR"], "lastdate" => $date, "lasttime" => $time], ["id" => $id]);
        }
        private static function GetTopicsOfUser(int $userId){
            $result = [];
            $topicsId = DataKeeper::Get("tt_topics", ["id"], ["authorId" => $userId]);
            foreach ($topicsId as $topicId)
                $result[] = $topicId;

            return $result;
        }
        private static function IsWithQuize(int $topicId){
            return DataKeeper::Get("tt_quizes", ["id"], ["topicId" => $topicId])[0];
        }
        /**
         * Try to authorize with given param and password.
         * @param $param string Email or nickname. If activation is need it's have to be a email.
         * @param $pass string Password for this account.
         * @param bool $passIsHash
         * @return bool|int
         */
        private static function Authorization($param, $pass, $passIsHash = False){
            if (Engine::GetEngineInfo("na")){
                $paramsToEnter = [$param,
                                  ($passIsHash) ? $pass : hash("sha256", $pass)
                ];

                if (self::IsValidNick($param))
                    $autorizationResult = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname`=? AND `password`=?", $paramsToEnter);
                if (self::IsEmailExists($param)) {
                    $autorizationResult = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `email`=? AND `password`=?", $paramsToEnter);
                }

                if (isset($autorizationResult["id"]) && !empty($autorizationResult["id"])) {
                    if (self::IsActivate($autorizationResult["id"]) == false) {
                        ErrorManager::GenerateError(26);
                        return ErrorManager::GetError();
                    }
                    self::UpdateLastData($autorizationResult["id"]);
                    return true;
                } else {
                    return false;
                }
            } else {
                $autorizationResult = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname`=? AND `password`=?",
                    array($param, (($passIsHash) ? $pass : hash("sha256", $pass))));
                if (isset($autorizationResult["id"])) {
                    if (self::IsActivate($autorizationResult["id"]) == false) {
                        ErrorManager::GenerateError(26);
                        return ErrorManager::GetError();
                    }
                    self::UpdateLastData($autorizationResult["id"]);
                    return true;
                }
                else
                    return false;
            }
        }
        private static function AfterAuth(){
            self::$authID = $_SESSION["uid"];
            self::$IsAuthorized = True;
            session_register_shutdown();
            return True;
        }
        private static function NotValidPWD(){
            ErrorManager::GenerateError(25);
            return ErrorManager::GetError();
        }
        private static function IsActivate($id){
            $result = DataKeeper::MakeQuery("SELECT `active` FROM `tt_users` WHERE `id`=?", array($id));
            if ($result["active"] == "TRUE") return true;
            else return false;
        }
        private static function IsIPRegistred($ipaddress){
            $queryString = DataKeeper::MakeQuery("SELECT count(*) FROM tt_users WHERE `regip` = ?", [$ipaddress]);

            return $queryString["count(*)"] == 0 ? false : $queryString["count(*)"];
        }
        private static function str_replace_once($search, $replace, $text){
            $pos = strpos($text, $search);
            return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
        }

        public static function Get10OnlineUsers(){
            $queryResponse = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE NOT `lasttime` < ? LIMIT 0,10", [Engine::GetSiteTime() - 60*5], true);
            $res = [];
            foreach ($queryResponse as $user){
                $res[] = $user["id"];
            }
            return $res;
        }
        public static function IsEmailExists($email){
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `email` = ?", [$email])["count(*)"] > 0 ? true : false;
        }
        public static function IsNicknameExists($nickname){
            $sqlResult = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE nickname=?", [$nickname]);
            if ($sqlResult["count(*)"] > 0){
                return true;
            }
            return false;
        }
        public static function ActivateAccount($id = null, $code)
        {
            if ($code == "true") return false;

            if ($id != null) {
                $queryResponse = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `id`=? AND `active` = ?", [$id, $code]);
            } else {
                $queryResponse = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `active` = ?", [$code]);
            }
            if ($queryResponse["count(*)"] == 0)
                return false;

            if ($id != null) {
                DataKeeper::Update("tt_users", ["active" => "TRUE"], ["id" => $id, "active" => $code]);
            } else {
                DataKeeper::Update("tt_users", ["active" => "TRUE"], ["active" => $code]);
            }
            return true;
        }
        public static function SessionCreate($param, $pass){
            $authIs = self::Authorization($param, $pass);
            if ($authIs === True){
                ini_set("session.gc_maxlifetime", 31536000);
                ini_set("session.cookie_lifetime", 31536000);
                ini_set("session.save_path", $_SERVER["DOCUMENT_ROOT"] . "/engine/sessions/");
                session_start();
                $authIs = self::GetUserId($param);
                setcookie("reloadSession", true, time()+31536000, "/", $_SERVER["SERVER_NAME"]);
                $_SESSION["uid"] = $authIs;
                $_SESSION["nickname"] = self::GetUserNick($authIs);
                $_SESSION["email"] = self::GetUserParam($authIs, "email");
                $_SESSION["passhash"] = hash("sha256", $pass);
                $_SESSION["hostip"] = $_SERVER["REMOTE_ADDR"];
                return True;
            } elseif ($authIs === False) {
                return self::NotValidPWD();
            } elseif ($authIs == 26){
                ini_set("session.gc_maxlifetime", 3600);
                ini_set("session.cookie_lifetime", 3600);
                ini_set("session.save_path", $_SERVER["DOCUMENT_ROOT"] . "/engine/sessions/");
                session_start();
                $authIs = self::GetUserId($param);
                setcookie("reloadSession", true, time()+3600, '/', $_SERVER["SERVER_NAME"]);
                $_SESSION["uid"] = $authIs;
                $_SESSION["nickname"] = self::GetUserNick($authIs);
                $_SESSION["email"] = self::GetUserParam($authIs, "email");
                $_SESSION["passhash"] = hash("sha256", $pass);
                $_SESSION["hostip"] = $_SERVER["REMOTE_ADDR"];
                return 26;
            } else return $authIs;
        }
        public static function SessionContinue()
        {
            if (isset($_COOKIE["PHPSESSID"])) {
                setcookie("reloadSession", true, time() + 31536000, "/", $_SERVER["SERVER_NAME"]);
                session_id($_COOKIE["PHPSESSID"]);
                ini_set("session.gc_maxlifetime", 31536000);
                ini_set("session.cookie_lifetime", 31536000);
                ini_set("session.save_path", $_SERVER["DOCUMENT_ROOT"] . "/engine/sessions/");
                session_start();
                $authResult = self::Authorization($_SESSION["email"] == null ? $_SESSION["nickname"] : $_SESSION["email"], $_SESSION["passhash"], true);
                if ($authResult === True) return self::AfterAuth();
                elseif ($authResult === False){ return self::NotValidPWD();}
                else return $authResult;
            }
            return false;
        }
        public static function SessionDestroy(){
            session_id($_COOKIE["PHPSESSID"]);
            ini_set("session.gc_maxlifetime", 0);
            ini_set("session.cookie_lifetime", 0);
            ini_set("session.save_path", $_SERVER["DOCUMENT_ROOT"] . "/engine/sessions/");
            session_start();
            setcookie(session_name(), "", 0, "/", $_SERVER["SERVER_NAME"]);
            setcookie("sid", "", 0, "/", $_SERVER["SERVER_NAME"]);
            setcookie("uid", "", 0, "/", $_SERVER["SERVER_NAME"]);
            setcookie("reloadSession", "", 0, "/", $_SERVER["SERVER_NAME"]);
            setcookie("PHPSESSID", "", 0, "/", $_SERVER["SERVER_NAME"]);
            $_SESSION = array();
            session_unset();
            session_destroy();
            return true;

        }
        public static function IsUserExist($id){
            if ($id <= 0) return false;
            $res = DataKeeper::isExistsIn("tt_users", "id", $id);
            if ($res)
                return true;
            else
                return false;
        }
        public static function AddUser($nick, $password, $email, $referer, $unforce = False, $name = '', $city = '', $sex = 1)
        {
            if (!self::IsValidNick($nick)){
                ErrorManager::GenerateError(21);
                return ErrorManager::GetError();
            }

            if (!self::IsEmailValid($email)){
                ErrorManager::GenerateError(22);
                return ErrorManager::GetError();
            }

            if ($referer != ''){
                $referer = self::GetUserId($referer);

                if ($referer === False){
                    ErrorManager::GenerateError(23);
                    return ErrorManager::GetError();
                }} else $referer = 0;

            if (Engine::GetEngineInfo("na")) {
                $query = "SELECT count(*) FROM `tt_users` WHERE nickname=? OR email=?";
                $sqlResult = DataKeeper::MakeQuery($query, array($nick, $email));
                if ($sqlResult["count(*)"] > 0){
                    ErrorManager::GenerateError(3);
                    return ErrorManager::GetError();
                }
            } else {
                if (DataKeeper::isExistsIn("tt_users", "nickname", $nick)){
                    ErrorManager::GenerateError(4);
                    return ErrorManager::GetError();
                }
            }

            if (Engine::GetEngineInfo("map") == "y"){
                if (self::IsIPRegistred($_SERVER["REMOTE_ADDR"])){
                    ErrorManager::GenerateError(36);
                    return ErrorManager::GetError();
                }

            }

            $randomWord = Engine::RandomGen(10);

            $queryReqRequest = DataKeeper::InsertTo("tt_users", array(
               "nickname" => $nick,
               "password" => hash("sha256", $password),
               "email" => $email,
               "group" => Engine::GetEngineInfo("sg"),
               "active" => (Engine::GetEngineInfo("na") && $unforce != False) ? $randomWord : "TRUE",
               "regdate" => date("Y-m-d", Engine::GetSiteTime()),
               "regip" => $_SERVER["REMOTE_ADDR"],
               "avatar" => "no",
               "referer" => $referer,
               "city" => $city,
               "realname" => $name,
               "sex" => $sex,
               "lastip" => "null"
            ));
            if ($queryReqRequest){
                ob_start();
                include_once "../../site/templates/" . Engine::GetEngineInfo("stp") . "/mailbody.html";
                $body = ob_get_contents();
                ob_end_clean();

                $link = ((!empty(Engine::GetEngineInfo("dm"))) ? Engine::GetEngineInfo("dm") : $_SERVER['HTTP_HOST']) .
                        "/profile.php?activate=$randomWord&uid=" . UserAgent::GetUserId($nick);
                if (Engine::GetEngineInfo("na") &&  $unforce != false) {
                    $bodyMain = LanguageManager::GetTranslation("mail_need_activation");
                    $bodyMain = str_replace("{EMAIL:ACTIVATION_LINK}", $link, $bodyMain);
                    $bodyMain = self::str_replace_once("{EMAIL:NICKNAME}", $nick, $bodyMain);
                    $bodyMain = self::str_replace_once("{EMAIL:ACTIVATION_CODE}", $randomWord, $bodyMain);
                    $body = str_replace("{MAIL_TITLE}", LanguageManager::GetTranslation("mail_activation_topic") . " \"" . Engine::GetEngineInfo("sn") . "\"", $body);
                    $body = str_replace("{MAIL_SITENAME}", Engine::GetEngineInfo("sn") , $body);
                    $body = str_replace("{MAIL_NICKNAME_TO}", LanguageManager::GetTranslation("mail_hello") . " " . $nick . "!" , $body);
                    $body = str_replace("{MAIL_BODY_MAIN}", $bodyMain, $body);
                    $body = str_replace("{MAIL_FOOTER_INFORMATION}", LanguageManager::GetTranslation("mail_administrators_signature") ." \"" . Engine::GetEngineInfo("sn") . "\"<br>"
                                                                                 . LanguageManager::GetTranslation("copyright"), $body);
                    if (!Mailer::SendMail($body, $email, LanguageManager::GetTranslation("mail_activation_topic") . " \"" . Engine::GetEngineInfo("sn") . "\"")){
                        DataKeeper::Delete("tt_users", ["nickname" => $nick]);
                        return false;
                    } else {
                        if ($referer !== false){
                            $notificator = new UserNotificator($referer);
                            $notificator->createNotify(14, $queryReqRequest);
                        }
                    }
                } else {
                    $bodyMain = LanguageManager::GetTranslation("mail_just_info");
                    $bodyMain = str_replace("{EMAIL:NICKNAME}", $nick, $bodyMain);
                    $bodyMain = str_replace("{EMAIL:PASSWORD}", $password, $bodyMain);
                    $body = str_replace("{MAIL_TITLE}", LanguageManager::GetTranslation("mail_registration_topic") . " \"" . Engine::GetEngineInfo("sn") . "\"", $body);
                    $body = str_replace("{MAIL_SITENAME}", Engine::GetEngineInfo("sn") , $body);
                    $body = str_replace("{MAIL_NICKNAME_TO}", $nick , $body);
                    $body = str_replace("{MAIL_BODY_MAIN}", $bodyMain, $body);
                    $body = str_replace("{MAIL_FOOTER_INFORMATION}", LanguageManager::GetTranslation("mail_administrators_signature") ." \"" . Engine::GetEngineInfo("sn") . "\"<br>"
                        . LanguageManager::GetTranslation("copyright"), $body);
                    if (!Mailer::SendMail($body, $email, LanguageManager::GetTranslation("mail_registration_topic") . " \"" . Engine::GetEngineInfo("sn") . "\"")) {
                        DataKeeper::Delete("tt_users", ["nickname" => $nick]);
                        return false;
                    } else {
                        if ($referer !== false){
                            $notificator = new UserNotificator($referer);
                            $notificator->createNotify(14, $queryReqRequest);
                        }
                    }
                }
                return true;
            }
            return false;

        }
        public static function DeleteUser($id){
            /* These things must be deleted:
             * 1. Notifications
            */
            DataKeeper::Delete("tt_notifications", ["type" => 20, "subject" => $id]);
            DataKeeper::Delete("tt_notifications", ["fromUid" => $id]);
            DataKeeper::Delete("tt_notifications", ["toUid" => $id]);
             /* 2. Topics */
            $topics = self::GetTopicsOfUser($id);
            for ($i = 0; $i < count($topics); $i++){
               if (self::IsWithQuize($topics[$i]) > 0)
                   ForumAgent::DeleteQuize(self::IsWithQuize($topics[$i]));
            }
            DataKeeper::Delete("tt_topics", ["authorId" => $id]);
             /* 3. Comments */
            DataKeeper::Delete("tt_topiccomments", ["authorId" => $id]);
             /* 4. Reports */
            DataKeeper::Delete("tt_reports", ["author" => $id]);
             /* 5. Answers in reports. */
            DataKeeper::Delete("tt_reportda", ["addedUID" => $id]);
            DataKeeper::Delete("tt_reportanswers", ["authorId" => $id]);
             /* 6. Friend */
            DataKeeper::Delete("tt_friends", ["fhost" => $id]);
            DataKeeper::Delete("tt_friends", ["friendId" => $id]);
             /* 7. PMs */
            DataKeeper::Delete("tt_pmessages", ["senderUID" => $id]);
            DataKeeper::Delete("tt_pmessages", ["receiverUID" => $id]);
            /* 8. Blacklisted. */
            DataKeeper::Delete("tt_blacklisted", ["authorId" => $id]);
            DataKeeper::Delete("tt_blacklisted", ["blockId" => $id]);
            DataKeeper::Delete("tt_topicsmarks", ["userId" => $id]);
            /* 9. Uploaded files */
            Uploader::DeleteFilesOfUser($id);
            if (UserAgent::GetUserParam($id, "avatar") != "no") {
                unlink("../uploads/avatars/" . UserAgent::GetUserParam($id, "avatar"));
            }
            /* 10.Additional fields */
            DataKeeper::Delete("tt_adfieldscontent", ["userId" => $id]);
            ///////////////////////////////////////////////////////////////////////////////////
            DataKeeper::Delete("tt_users", ["id" => $id]);
        }
        public static function GetAllUsers(){
            return DataKeeper::Get("tt_users", ["id", "nickname"]);
        }
        public static function GetUsersList(array $paramsArray, $page = 1){
            $lowBorder = $page * 50 - 50;
            $highBorder = $page * 50;

            if (count($paramsArray) == 0){
                $queryResponse = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` LIMIT $lowBorder, $highBorder", [], true);
            } else {
                if (isset($paramsArray["nickname"])) $paramsArray["nickname"] = str_replace("*", "%", $paramsArray["nickname"]);
                if (isset($paramsArray["email"])) $paramsArray["email"] = str_replace("*", "%", $paramsArray["email"]);
                if (isset($paramsArray["lastip"])) $paramsArray["lastip"] = str_replace("*", "%", $paramsArray["lastip"]);
            }
            if (count($paramsArray) != 0){
                if (!isset($paramsArray["nickname"])) $paramsArray["nickname"] = "%";
                if (!isset($paramsArray["email"])) $paramsArray["email"]       = "%";
                if (!isset($paramsArray["lastip"])) $paramsArray["lastip"]     = "%";
                if (!isset($paramsArray["referer"])) $paramsArray["referer"]   = "%";
                if (!isset($paramsArray["group"])) $paramsArray["group"]       = "%";

                if (isset($paramsArray["referer"])) {
                    if (UserAgent::GetUserId($paramsArray["referer"]))
                        $paramsArray["referer"] = UserAgent::GetUserId($paramsArray["referer"]);

                }
                $queryResponse = DataKeeper::MakeQuery("SELECT
                                                                    `id`
                                                                FROM
                                                                    `tt_users`
                                                                WHERE
                                                                    `nickname` LIKE ?
                                                                AND `email` LIKE ?
                                                                AND `lastip` LIKE ?
                                                                AND `referer` LIKE ?
                                                                AND `group` LIKE ?
                                                                LIMIT $lowBorder, $highBorder",
                    [$paramsArray["nickname"], $paramsArray["email"],
                        $paramsArray["lastip"], $paramsArray["referer"], $paramsArray["group"]],
                    true);
            }

            return $queryResponse;
        }
        public static function GetUsersCount(){
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users`")["count(*)"];
        }
        public static function ChangeUserParams($id, $param, $newparam){
            if ($param == 'password' || $param == 'id'){
                return false;
            }

            if (!self::IsUserExist($id)){
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            if ($param == "nickname"){
                if (!self::IsValidNick($newparam)){
                    ErrorManager::GenerateError(21);
                    return ErrorManager::GetError();
                }

                if (self::IsNicknameExists($newparam)){
                    ErrorManager::GenerateError(4);
                    return ErrorManager::GetError();
                }
            }

            if ($param == "email"){
                if (!self::IsEmailValid($newparam)){
                    ErrorManager::GenerateError(22);
                    return ErrorManager::GetError();
                }

                if (Engine::GetEngineInfo("na") && self::IsEmailExists($newparam)){
                    ErrorManager::GenerateError(34);
                    ErrorManager::PretendToBeDied(ErrorManager::GetErrorCode(34), new \Exception("You cannot create user with duplicated email."));
                }
            }

            return DataKeeper::Update("tt_users", array($param => $newparam), array("id" => $id));
        }
        public static function ChangeUserPassword($id, $newPass, bool $relogin){
            if (!self::IsUserExist($id)) {
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            $resp = DataKeeper::Update("tt_users", array("password" => hash("sha256", $newPass)), array("id" => $id));
            if ($relogin) {
                self::SessionDestroy();
                self::SessionCreate(UserAgent::GetUser($id)->getEmail(), $newPass);
            }
            return $resp;
        }
        public static function GetUserId($param){
            return DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname`=? OR `email` = ?", [$param, $param])["id"];
        }
        public static function GetUserNick($id){
            if (self::IsUserExist($id) === false) {
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            return DataKeeper::Get("tt_users", ["nickname"], ["id" => $id])[0]["nickname"];
        }
        public static function GetUserGroupId($idUser){
            if (!self::IsUserExist($idUser)){
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            return DataKeeper::Get("tt_users", ["group"], ["id" => $idUser])["group"];
        }
        public static function GetUser($idUser){
            if (!UserAgent::IsUserExist($idUser)) return false;
            return new User($idUser);
        }
        public static function GetUserRefererForCount($idUser){
            if (!self::IsUserExist($idUser)){
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `referer` = ?", [$idUser])["count(*)"];
        }
        /**
         * Return content of user property.
         * @param $idUser int User ID
         * @param $param string Property that should be returned.
         * @return bool|int|array
         */
        public static function GetUserParam($idUser, $param)
        {
            if (in_array($param, array(0 => 'nickname',
                1 => 'id',
                2 => 'password',
                3 => 'group'))) {
                return false;
            }

            if (!self::IsUserExist($idUser)) {
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }
            return DataKeeper::Get("tt_users", [$param], [$idUser])[$param];
        }
        /**
         * Return a array with ids users have a nickname like a shedule.
         * @param $Snickname Shedule of nickname.
         * @return array|int
         * In shedule you can use * symbol for unknown substring.
         */
        public static function FindUsersBySNickname($Snickname){
            if (strstr($Snickname, "*") > -1) $Snickname = str_replace("*", "%", $Snickname);
            $result = [];
            $queryResponse = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?", [$Snickname]);
            foreach ($queryResponse as $Sid){
                $result[] = $Sid;
            }
            return $result;
        }
        public static function UploadAvatar($idUser, $fileFormName){
            if (!UserAgent::IsUserExist($idUser)){
                ErrorManager::GenerateError(11);
                return ErrorManager::GetError();
            }
            $imgtypes = array(
                0 => "jpg",
                1 => "png",
                2 => "tif",
                3 => "jpeg",
                4 => "gif"
            );
            echo Uploader::ExtractType($_FILES[$fileFormName]['name']) . "<br>";
            echo $_FILES[$fileFormName]['name'];
            if (in_array(Uploader::ExtractType($_FILES[$fileFormName]['name']), $imgtypes)) {
                $uploaddir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/avatars/";
            }
            else{ ErrorManager::GenerateError(18); return ErrorManager::GetError(); }

            if (getimagesize($_FILES[$fileFormName]['tmp_name'])[0] != Engine::GetEngineInfo("aw") ||
                getimagesize($_FILES[$fileFormName]['tmp_name'])[1] != Engine::GetEngineInfo("ah")){
                ErrorManager::GenerateError(19);
                return ErrorManager::GetError();
            }

            if ($_FILES[$fileFormName]['size'] > 6*1024*1024){
                ErrorManager::GenerateError(20);
                return ErrorManager::GetError();
            }

            if (file_exists("../uploads/avatars/" . UserAgent::GetUserParam($idUser, "avatar"))){
                unlink("../uploads/avatars/" . UserAgent::GetUserParam($idUser, "avatar"));
            }

            $newName = $idUser . "." . Uploader::ExtractType(basename($_FILES[$fileFormName]['name']));
            $uploadfile = $uploaddir . $newName;

            $glob = glob($uploaddir . $idUser . ".*");
            if (count($glob) != 0) {
                $reset = reset($glob);
                unlink($reset);
            }

            if (!is_uploaded_file($_FILES[$fileFormName]['tmp_name'])) echo "Something wrong, file was not uploaded!";

            if (!move_uploaded_file($_FILES[$fileFormName]['tmp_name'], $uploadfile)) {
                return False;
            }

            return DataKeeper::Update("tt_users", ["avatar" => $newName], ["id" => $idUser]);
        }
        public static function ClearNotifications(){
            return DataKeeper::MakeQuery("DELETE FROM `tt_notifications` WHERE `createTime` < ?", [Engine::GetSiteTime() - 30*24*60*60]);
        }
        public static function GetOnlineFriendsCount($userId){
            if (!self::IsUserExist($userId)) return false;

            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `id` IN (SELECT `friendId` FROM `tt_friends` WHERE `fhost`=?) AND `lasttime` > ?", [$userId, Engine::GetSiteTime()-60*15])["count(*)"];
        }
        public static function GetOnlineFriends($ofUserId){
            if (!self::IsUserExist($ofUserId)) return false;
            return DataKeeper::MakeQuery("SELECT `users`.`id` AS `friendId`,
                                                        `friends`.`fhost` AS `fhost`,
                                                        `friends`.`regdate` AS `regdate`            
                                                 FROM `tt_users` AS `users`
                                                 LEFT JOIN `tt_friends` AS `friends` ON `users`.`id` = `friends`.`friendId`   
                                                 WHERE `users`.`id` IN (SELECT `friendId` FROM `tt_friends` WHERE `fhost`=?) AND `users`.`lasttime` > ?",
                [$ofUserId, Engine::GetSiteTime()-60*15], true);
        }
        public static function GetAdditionalFieldsList(){
            return DataKeeper::Get("tt_adfields", ["*"]);
        }
        public static function GetAdditionalFieldsListOfUser($userId){
            $result = [];
            $data = DataKeeper::Get("tt_adfieldscontent", array("fieldId", "content", "isPrivate"), array("userId" => $userId));
            foreach ($data as $d){
                $result[$d["fieldId"]] = $d;
            }
            return $result;
        }
        public static function GetAdditionalFieldContentOfUser($userId, $fieldId){
            return DataKeeper::Get("tt_adfieldscontent", ["content"], ["userId" => $userId, "fieldId" => $fieldId])[0];
        }
        public static function SetAdditionalFieldContent($userId, $fieldId, $content){
            if (DataKeeper::_isExistsIn("tt_adfieldscontent", array("fieldId" => $fieldId, "userId" => $userId)))
                $request = DataKeeper::Update("tt_adfieldscontent", array("content" => $content), array("fieldId" => $fieldId, "userId" => $userId));
            else
                $request = DataKeeper::InsertTo("tt_adfieldscontent", array("userId" => $userId, "fieldId" => $fieldId, "content" => $content));
            return $request;
        }
        public static function SetPrivacyToAdditionalField($userId, $fieldId, $privacy){
            $request = DataKeeper::Update("tt_adfieldscontent", array("isPrivate" => $privacy), array("fieldId" => $fieldId, "userId" => $userId));
            return $request;
        }
    }
    class GroupAgent{

        private static function CheckNameValid($name){
            if (strlen($name) <= 4){
                ErrorManager::GenerateError(15);
                return ErrorManager::GetError();
            }

            if (strlen($name) >= 16){
                ErrorManager::GenerateError(16);
                return ErrorManager::GetError();
            }

            preg_match("/[a-zA-Zа-яА-Я]+/", $name, $arrPreg);
            if (count($arrPreg) > 1 || strlen($arrPreg[0]) != strlen($name)) echo 2; else echo 1;

            return (bool) DataKeeper::MakeQuery("SELECT count(*) FROM `tt_groups` WHERE `name` = ?", [$name])["count(*)"];
        }

        public static function IsGroupExists($id){
            return (bool) DataKeeper::MakeQuery("SELECT count(*) FROM `tt_groups` WHERE `id` = ?", [$id])["count(*)"];
        }
        public static function AddGroup($name, $color, $descript){

            if (!self::CheckNameValid($name) == True) return ErrorManager::GetError();

            return DataKeeper::InsertTo("tt_groups", ["id" => null, "name" => $name, "color" => $color, "descript" => $descript]);
        }
        public static function RemoveGroup($id){
            if (!self::IsGroupExists($id)) return ErrorManager::GetError();

            return DataKeeper::Delete("tt_groups", ["id" => $id]);
        }
        public static function ChangeGroupPerms($id, $type, $typeNew){
            $nonPerms = array(0=>'id', 1=>'name', 2=>'color', 3=>'descript');
            if (in_array($type, $nonPerms)) exit;
            if (!self::IsGroupExists($id)){ ErrorManager::GenerateError(10); return ErrorManager::GetError(); }

            return DataKeeper::Update("tt_groups", ["$type" => $typeNew], ["id" => $id]);
        }
        public static function ChangeGroupData($id, $type, $typeNew){
            $nonPerms = array(0 => "id", 1=>'name', 2=>'color', 3=>'descript');
            if (!in_array($type, $nonPerms)) exit;

            if ($type == 'name')
                if (!self::CheckNameValid($typeNew)){
                    return ErrorManager::GetError();
                }

            return DataKeeper::Update("tt_groups", ["$type" => $typeNew], ["id" => $id]);
        }
        public static function MoveGroupMembers($id, $toId){
            if (!GroupAgent::IsGroupExists($toId)) return False;

            return DataKeeper::Update("tt_users", ["group" => $toId], ["group" => $id]);
        }
        public static function GetGroupList(){
            return DataKeeper::Get("tt_groups", ["id"]);
        }
        public static function GetGroupNameById($id){
            return DataKeeper::Get("tt_groups", ["name"], ["id" => $id])[0]["name"];
        }
        public static function GetGroupColor($id){
            return DataKeeper::Get("tt_groups", ["color"], ["id" => $id])[0]["color"];
        }
        public static function GetGroupDescribe($id){
            return DataKeeper::Get("tt_groups", ["descript"], ["id" => $id])[0]["descript"];
        }
        public static function GetUsersCountInGroup(int $groupId){
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `group` = ?", [$groupId])["count(*)"];
        }
        public static function GetGroupUsers($id, int $page = 1){
            $lowBorder = $page * 15 - 15;
            $highBorder = 15;

            $queryResponse = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `group` = ? ORDER BY `id` LIMIT $lowBorder, $highBorder", [$id], true);
            return $queryResponse;
        }
        public static function IsHavePerm($id, $perm) : bool{
            $nonPerms = array(0=>'id', 1=>'name', 2=>'color', 3=>'descript');
            if (in_array($perm, $nonPerms))
                return false;

            $result = DataKeeper::Get("tt_groups", [$perm], ["id" => $id])[0][$perm];
            if ($result)
                return true;
            else
                return false;
        }
    }
}