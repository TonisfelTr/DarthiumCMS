<?php
namespace Users {

    use Engine\DataKeeper;
    use Engine\Engine;
    use Engine\ErrorManager;
    use Engine\Mailer;
    use Engine\Uploader;
    use Guards\SocietyGuard;

    class Group{
        private $gId;
        private $gName;
        private $gColor;
        private $gDescript;
        private $gPerms = array();

        public function __construct($groupId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                ErrorManager::PretendToBeDied(ErrorManager::GetErrorCode(2), "Could not connect to the db in Group constructor.");
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM `tt_groups` WHERE `id`=?")){
                $stmt->bind_param("i", $groupId);
                $stmt->execute();
                $stmt->bind_result($id, $name, $descript, $color, $enterpanel, $offline_visiter, $rules_edit,
                    $change_perms, $group_create, $group_delete, $group_change,
                    $change_another_profiles,
                    $change_user_group, $user_add, $user_remove, $user_see_foreign, $user_signs,
                    $user_ban, $user_unban, $user_banip, $user_unbanip,
                    $report_create, $report_foreign_remove, $report_talking, $report_remove, $report_edit, $report_foreign_edit, $report_answer_edit, $report_anser_foreign_edit, $report_close,
                    $change_profile, $change_engine_settings,
                    $bmail_sende, $bmail_sends,
                    $upload_add, $upload_see_all, $upload_delete, $upload_delete_foreign,
                    $category_create, $category_edit, $category_delete, $category_see_unpublic, $category_params_ignore,
                    $topic_create, $topic_edit, $topic_foreign_edit, $topic_delete, $topic_foreign_delete, $topic_manage,
                    $comment_create, $comment_edit, $comment_foreign_edit, $comment_delete, $comment_foreing_delete,
                    $sc_create_pages, $sc_edit_pages, $sc_remove_pages, $sc_design_edit,
                    $logs_see);
                while ($stmt->fetch()){
                    $this->gId = $id;
                    $this->gName = $name;
                    $this->gColor = $color;
                    $this->gDescript = $descript;

                    $this->gPerms = array(
                        'enterpanel' => $enterpanel,
                        'change_engine_settings' => $change_engine_settings,
                        'offline_visiter' => $offline_visiter,
                        'rules_edit' => $rules_edit,

                        /***********************************************************
                         * Group permissions.                                      *
                         ***********************************************************/

                        'change_perms' => $change_perms,
                        'group_create' => $group_create,
                        'group_delete' => $group_delete,
                        'group_change' => $group_change,

                        /************************************************************
                         * User permissions.                                        *
                         ************************************************************/

                        'change_another_profiles' => $change_another_profiles,
                        'change_user_group' => $change_user_group,
                        'user_add' => $user_add,
                        'user_remove' => $user_remove,
                        'user_see_foreign' => $user_see_foreign,
                        'user_signs' => $user_signs,
                        'change_profile' => $change_profile,
                        'user_ban' => $user_ban,
                        'user_unban' => $user_unban,
                        'user_banip' => $user_banip,
                        'user_unbanip' => $user_unbanip,

                        /*************************************************************
                         * Reports permissions                                       *
                         *************************************************************/

                        'report_create' => $report_create,
                        'report_foreign_remove' => $report_foreign_remove,
                        'report_talking' => $report_talking,
                        'report_remove' => $report_remove,
                        'report_edit' => $report_edit,
                        'report_foreign_edit' => $report_foreign_remove,
                        'report_answer_edit' => $report_answer_edit,
                        'report_foreign_answer_edit' => $report_anser_foreign_edit,
                        'report_close' => $report_close,

                        /*************************************************************
                         * Uploading permissions                                     *
                         *************************************************************/

                        'upload_add' => $upload_add,
                        'upload_delete' => $upload_delete,
                        'upload_delete_foreign' => $upload_delete_foreign,
                        'upload_see_all' => $upload_see_all,

                        /*************************************************************
                         * Categories permissions                                    *
                         *************************************************************/

                        'category_create' => $category_create,
                        'category_delete' => $category_delete,
                        'category_edit' => $category_edit,
                        'category_see_unpublic' => $category_see_unpublic,
                        'category_params_ignore' => $category_params_ignore,

                        /*************************************************************
                         * Topics permissions                                        *
                         *************************************************************/

                        'topic_create' => $topic_create,
                        'topic_edit' => $topic_edit,
                        'topic_foreign_edit' => $topic_foreign_edit,
                        'topic_delete' => $topic_delete,
                        'topic_foreign_delete' => $topic_foreign_delete,
                        'topic_manage' => $topic_manage,

                        /*************************************************************
                         * Comments permissions                                      *
                         *************************************************************/

                        'comment_create' => $comment_create,
                        'comment_edit' => $comment_edit,
                        'comment_foreign_edit' => $comment_foreign_edit,
                        'comment_delete' => $comment_delete,
                        'comment_foreign_delete' => $comment_foreing_delete,

                        /**************************************************************
                         * Permissions manage with static content              *
                         **************************************************************/

                        'sc_create_pages' => $sc_create_pages,
                        'sc_edit_pages' => $sc_edit_pages,
                        'sc_remove_pages' => $sc_remove_pages,
                        'sc_design_edit' => $sc_design_edit,

                        /**************************************************************
                         * Other                                                      *
                         **************************************************************/

                        'bmail_sende' => $bmail_sende,
                        'bmail_sends' => $bmail_sends,
                        'logs_see' => $logs_see
                    );
                }
            } else {
                ErrorManager::GenerateError(9);
                return ErrorManager::GetError();
            }

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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_reports` WHERE `author` = ?")){
                $id = $this->getId();
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($result);
                $stmt->fetch();
                return $result;
            }

            $mysqli->close();
            return 0;
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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `senderUID`, `receiverUID` FROM `tt_pmessages` WHERE `id`=? AND `isVisible`=?")){
                $t = 1;
                $stmt->bind_param("ii", $id, $t);
                $stmt->execute();
                $stmt->bind_result($sender, $receiver);
                $stmt->fetch();
                if ($sender == $this->userId) return "sender";
                elseif ($receiver == $this->userId) return "receiver";
                else return "nobody";
            }
        }
        private function hasAccess($id){
            if (!in_array($this->getStatusForMessage($id), ["sender", "receiver"])) return false;
            else return true;
        }
        private function setRead($id){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_pmessages` SET `isRead`=? WHERE `id`=?")){
                $t = true;
                $stmt->bind_param("ii", $t, $id);
                $stmt->execute();
                if ($stmt->errno) return false;
                else return true;
            }
            $mysqli->close();
            return false;
        }

        public function __construct($userId)
        {
            $this->userId = $userId;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM `tt_pmessages` WHERE `senderUID`=? OR `receiverUID`=? AND `isVisible`=? ORDER BY `id` DESC")){
                $t = 1;
                $stmt->bind_param("iii", $this->userId, $this->userId, $t);
                $stmt->execute();
                $stmt->bind_result($id, $senderUID, $receiverUID, $subject, $text, $isRead, $receiveTime, $isRemovedForSender, $isRemovedForReceiver, $isVisible, $isSaved);
                while($stmt->fetch()){
                    $receiveTime = date("Y-m-d H:i:s", $receiveTime);
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
                    } elseif ($senderUID == $this->userId && !$isSaved && !$isRemovedForSender){
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
                    } elseif ($senderUID == $this->userId && $isSaved && !$isRemovedForSender){
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
                    } elseif ($receiverUID == $this->userId && !$isSaved && !$isRemovedForReceiver){
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
                $stmt->close();
            }

            $mysqli->close();
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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_pmessages` (`id`, `senderUID`, `receiverUID`, `subject`, `text`, `receiveTime`) VALUE (NULL, ?, ?, ?, ?, ?)")){
                $time = Engine::GetSiteTime();
                $stmt->bind_param("iisss", $this->userId, $receiverUID, $subject, $text, $time);
                $stmt->execute();
                if (!$stmt->errno) return true;
                else return false;
            }

            return false;
        }
        public function remove($id){
            if ($this->getStatusForMessage($id) == "nobody") return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($this->getStatusForMessage($id) == "receiver"){
                $stmt = $mysqli->prepare("UPDATE `tt_pmessages` SET `isRemovedForReceiver`=? WHERE `id`=?");
            }
            if ($this->getStatusForMessage($id) == "sender"){
                $stmt = $mysqli->prepare("UPDATE `tt_pmessages` SET `isRemovedForSender`=? WHERE `id`=?");
            }

            if (isset($stmt)) {
                $t = 1;
                $stmt->bind_param("ii", $t, $id);
                $stmt->execute();
                if (!$stmt->errno) return true;
            }

            return false;
        }
        public function save($id){
            if ($this->getStatusForMessage($id) == "nobody") return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_pmessages` SET `isSaved`=? WHERE `id`=?")){
                $t = 1;
                $stmt->bind_param("ii", $t, $id);
                $stmt->execute();
                if(!$stmt->errno) return true;
            }
            return false;
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
        public function restore($id){
            if ($this->getStatusForMessage($id) == "nobody") return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($this->getStatusForMessage($id) == "receiver"){
                $stmt = $mysqli->prepare("UPDATE `tt_pmessages` SET `isRemovedForReceiver`=? WHERE `id`=?");
            }
            if ($this->getStatusForMessage($id) == "sender"){
                $stmt = $mysqli->prepare("UPDATE `tt_pmessages` SET `isRemovedForSender`=? WHERE `id`=?");
            }

            if (isset($stmt)){
                $t = 0;
                $stmt->bind_param("ii", $t, $id);
                $stmt->execute();
                if (!$stmt->errno) return true;
            }
            return false;
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM `tt_reputation` WHERE `uid`=?")){
                $id = $user->getId();
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($id, $userId, $authorId, $type, $comment, $createDate);
                $i = 0;
                while($stmt->fetch()){
                    $i++;
                    $this->userReputationChanges[$i] = ["authorId" => $authorId,
                                                         "type" => $type,
                                                         "comment" => $comment,
                                                         "createDate" => $createDate];
                    $this->userReputationPoint += ($type == 0) ? -1 : 1;
                }
            }

            $stmt->close();
            $mysqli->close();
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
        //TODO Remove reputation function and avaibility.
        public function removeReputationPoint($commentId){

        }
        //TODO Change reputation comment function and avaibility.
        public function changeReputationComment($commentId){

        }
    }
    class UserBlacklister{
        private $uId;
        private $uArray = [];

        public function __construct($userId)
        {
            $this->uId = $userId;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `blockId`, `comment`, `addedtime` FROM `tt_blacklisted` WHERE `authorId`=?")){
                $stmt->bind_param("i", $this->uId);
                $stmt->execute();
                $stmt->bind_result($blockId, $comment, $addedtime);
                while($stmt->fetch()){
                    array_push($this->uArray, ["bid" => $blockId, "comment" => $comment, "addedtime" => $addedtime]);
                }
            }
        }
        public function getList(){
            return $this->uArray;
        }
        public function add($userId, $comment = ""){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_blacklisted` (`id`, `authorId`, `blockId`, `comment`, `addedtime`) VALUE (NULL, ?, ?, ?, ?)")){
                $date = date("Y-m-d H:i:s", Engine::GetSiteTime());
                $stmt->bind_param("iiss", $this->uId, $userId, $comment, $date);
                $stmt->execute();
                if (!$stmt->errno) return true;
                else {
                    echo $stmt->error;
                    return false;
                }
            }
            return false;
        }
        public function isBlocked($userId){
            for ($i = 0; $i <= count($this->uArray)-1; $i++){
                if (in_array($userId, $this->uArray[$i]))
                    return true;
            }
            return false;
        }
        public function remove($userId){
            if (!$this->isBlocked($userId)) return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_blacklisted` WHERE `authorId`=? AND `blockId`=?")){
                $stmt->bind_param("ii", $this->uId, $userId);
                $stmt->execute();
                return true;
            }
            return false;
        }
        public function getBlockedInfo($userId){
            if (!$this->isBlocked($userId)) return false;

            for ($i = 0; $i <= count($this->uArray)-1; $i++){
                if (in_array($userId, $this->uArray[$i])){
                    return $this->uArray[$i];
                }
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `id`, `createTime`, `fromUid`, `type`, `isRead`, `subject` FROM `tt_notifications` WHERE `toUid` = ? ORDER BY `createTime` DESC")){
                $stmt->bind_param("i", $this->userId);
                $stmt->execute();
                $stmt->bind_result($id, $createTime, $fromUid, $type, $isRead, $subject);
                while ($stmt->fetch()){
                    array_push($this->notificationsList, ["id" => $id,
                        "createTime" => $createTime,
                        "fromUid" => $fromUid,
                        "type" => $type,
                        "isRead" => $isRead,
                        "subject" => $subject]);
                    $this->notificationsCount++;
                    if (!$isRead) $this->notificationsUnreadCount++;
                }
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
        public function createNotify($notificationCode, $fromUid, $subject  = 0){
            UserAgent::ClearNotifications();

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_notifications` (`id`, `toUid`, `type`, `fromUid`, `createTime`, `isRead`, subject) VALUE (NULL,?,?,?,?,?,?)")){
                $time = Engine::GetSiteTime();
                $y = 0;
                $stmt->bind_param("iiiiis", $this->userId, $notificationCode, $fromUid, $time, $y, $subject);
                $stmt->execute();
                if ($stmt->errno)
                    return $stmt->error;
                return true;
            }
            return false;
        }
        public function setRead($id){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_notifications` SET `isRead`=? WHERE `id`=?")){
                $y = 1;
                $stmt->bind_param("ii", $y, $id);
                $stmt->execute();
                if ($stmt->errno)
                    return $stmt->error;
                return true;
            }
            return false;
        }
        public function getUserId(){
            return $this->userId;
        }
    }
    class UserFriendlist{
        private $userId;
        private $friendCount = 0;
        private $friendList = [];

        public function isFriend($userId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_friends` WHERE `fhost` = ? AND `friendId` = ?")){
                $stmt->bind_param("ii", $this->userId, $userId);
                $stmt->execute();
                $stmt->bind_result($result);
                $stmt->fetch();
                if ($result >= 1) return true;
            }
            return false;
        }

        public function __construct($userId)
        {
            $this->userId = $userId;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if ($stmt = $mysqli->prepare("SELECT `friendId`, `regdate` FROM `tt_friends` WHERE `fhost` = ?")){
                $stmt->bind_param("i", $this->userId);
                $stmt->execute();
                if ($stmt->errno) echo $stmt->error;
                $stmt->bind_result($friendId, $regdate);
                while($stmt->fetch()){
                    array_push($this->friendList, [ "friendId" => $friendId, "regdate" => $regdate]);
                    $this->friendCount++;
                }
            }
        }
        public function getFriendsCount(){
            return $this->friendCount;
        }
        public function getFriendsList(){
            return $this->friendList;
        }
        public function getFriendFromList($index){
            return new User($this->friendList[$index]["friendId"]);
        }
        public function getFriendFromDB($friendId){
            if (!$this->isFriend($friendId)) return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if ($stmt = $mysqli->prepare("SELECT `friendId`, `regdate` FROM `tt_friends` WHERE `friendId`=? AND `fhost`=?")){
                $stmt->bind_param("ii", $friendId, $this->userId);
                $stmt->execute();
                $stmt->bind_result($resFriendId, $resRegDate);
                $stmt->fetch();
                return [ "fhost" => $this->userId,
                    "friendId" => $resFriendId,
                    "regdate" => $resRegDate];
            }
            return false;
        }
        public function getOnlineFriendCount(){
            return UserAgent::GetOnlineFriendsCount($this->userId);
        }
        public function getOnlineFriends(){
            return UserAgent::GetOnlineFriends($this->userId);
        }
        public function addFriend($friendId){
            if ($this->isFriend($friendId)) return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_friends` (`fhost`, `friendId`, `regdate`) VALUE (?,?,?)")){
                $time = Engine::GetSiteTime();
                $stmt->bind_param("iis", $this->userId, $friendId, $time);
                $stmt->execute();
                if ($stmt->errno) return $stmt->error;
                return true;
            }
            return false;
        }
        public function deleteFriend($friendId){
            if (!$this->isFriend($friendId)) return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_friends` WHERE `fhost` = ? AND `friendId` = ?")){
                $stmt->bind_param("ii", $this->userId, $friendId);
                $stmt->execute();
                if (!$stmt->errno) return true;
            }
            return false;
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
            if (preg_match("/[a-z0-9A-Z.@-_]+/", $str) == 1) return True;
            else return False;
        }
        private static function UpdateLastData($id){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if($stmt = $mysqli->prepare("UPDATE `tt_users` SET `lastip`=?, `lastdate`=?, `lasttime`=? WHERE `id`=?")){
                $date = date("Y-m-d", Engine::GetSiteTime());
                $time = Engine::GetSiteTime();
                $stmt->bind_param("sssi", $_SERVER["REMOTE_ADDR"], $date, $time, $id);
                $stmt->execute();
                if($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->close();
                $mysqli->close();
                return True;
            } else echo $mysqli->error;

            $mysqli->close();
            return False;

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
                $paramsToEnter = array($param, (($passIsHash) ? $pass : hash("sha256", $pass)));
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

        public static function Get10OnlineUsers(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            };

            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_users` WHERE NOT lasttime < ? LIMIT 0,10")){
                $timeBorder = Engine::GetSiteTime() - 60*5;
                $stmt->bind_param("i", $timeBorder);
                $stmt->execute();
                $res = array();
                $stmt->bind_result($val);
                while($stmt->fetch()){
                    array_push($res, $val);
                }
            }
            return $res;
        }
        public static function IsEmailExists($email){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_users` WHERE `email` = ? ")){
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($res);
                $stmt->fetch();
                $res1 = $res;
                if ($res1 >= 1) return true;
                else return false;

            }
            return false;
        }
        public static function IsNicknameExists($nickname){
            $query = "SELECT count(*) FROM `tt_users` WHERE nickname=?";
            $sqlResult = DataKeeper::MakeQuery($query, array($nickname));
            if ($sqlResult["count(*)"] > 0){
                return true;
            }
            return false;
        }
        public static function ActivateAccount($id = null, $code)
        {
            if ($code == "true") return false;
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if ($id != null) {
                if (!$stmt = $mysqli->prepare("SELECT count(*) FROM `tt_users` WHERE `id`=? AND active=?")) return false;

                $stmt->bind_param("is", $id, $code);
            } else {
                if (!$stmt = $mysqli->prepare("SELECT count(*) FROM `tt_users` WHERE active=?")) return false;
                $stmt->bind_param("s", $code);
            }
            $stmt->execute();
            if ($stmt->errno){
                ErrorManager::GenerateError(9);
                return ErrorManager::GetError();
            }
            $stmt->bind_result($act);
            $stmt->fetch();
            if ($act == 0) return false;
            $stmt->close();

            $valueAct = 'TRUE';

            if ($id != null) {
                if(!$stmt = $mysqli->prepare("UPDATE `tt_users` SET active=? WHERE `id`=? AND active=?")) return false;
                $stmt->bind_param("sis", $valueAct, $id, $code);
            } else {
                if(!$stmt = $mysqli->prepare("UPDATE `tt_users` SET active=? WHERE active=?")) return false;
                $stmt->bind_param("ss", $valueAct, $code);
            }
            $stmt->execute();
            if ($stmt->errno){
                ErrorManager::GenerateError(9);
                return ErrorManager::GetError();
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
                setcookie("sid", session_id(), time()+31536000, '/', $_SERVER["SERVER_NAME"]);
                setcookie("reloadSession", true, time()+31536000, '/', $_SERVER["SERVER_NAME"]);
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
                setcookie("sid", session_id(), time()+3600, '/', $_SERVER["SERVER_NAME"]);
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
            if (isset($_COOKIE["reloadSession"]) && isset ($_COOKIE["sid"])) {
                session_id($_COOKIE["sid"]);
                ini_set("session.gc_maxlifetime", 31536000);
                ini_set("session.cookie_lifetime", 31536000);
                ini_set("session.save_path", $_SERVER["DOCUMENT_ROOT"] . "/engine/sessions/");
                session_start();
                if ($_SESSION["hostip"] == $_SERVER["REMOTE_ADDR"]) {
                    $authResult = self::Authorization($_SESSION["email"], $_SESSION["passhash"], true);
                    setcookie("reloadSession", true, time()+31536000, "/", $_SERVER["SERVER_NAME"]);
                    if ($authResult === True) return self::AfterAuth();
                    elseif ($authResult === False) return self::NotValidPWD();
                    else return $authResult;
                } else {
                    session_register_shutdown();
                    setcookie("reloadSession", false, 0, "/", $_SERVER["SERVER_NAME"]);
                    setcookie("sid", true, 1, "/", $_SERVER["SERVER_NAME"]);
                    ErrorManager::GenerateError(24);
                    return ErrorManager::GetError();
                }
            }
            return false;
        }
        public static function SessionDestroy(){
            session_id($_COOKIE["sid"]);
            ini_set("session.gc_maxlifetime", 0);
            ini_set("session.cookie_lifetime", 0);
            ini_set("session.save_path", $_SERVER["DOCUMENT_ROOT"] . "/engine/sessions/");
            session_start();
            setcookie(session_name(), "", 0, "/", $_SERVER["SERVER_NAME"]);
            setcookie("sid", "", 0, "/", $_SERVER["SERVER_NAME"]);
            setcookie("uid", "", 0, "/", $_SERVER["SERVER_NAME"]);
            setcookie("reloadSession", "", 0, "/", $_SERVER["SERVER_NAME"]);
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
        public static function AddUser($nick, $password, $email, $referer, $unforce = False, $name = '', $city = '', $sex = 0)
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
                    $bodyMain = "<p> Вы получили данное сообщение, поскольку на нашем сайте при регистрации кто-то указал этот Email. Если это были не Вы, тогда
                                      забудьте о существовании этого письма, предварительно кинув его в мусорку. А так же в небытье пустоты.</p>
                                 <p> Если же это были всё таки Вы, то напоминаем, что для того, чтобы начать пользоваться Вашим аккаунтом нужно его активировать, перейдя по
                                      ссылке ниже. Также, Вы можете активировать свой аккаунт при входе. После авторизации, сайт попросит у Вас код активации.</p>
                                  <span class=\"mail-span\">Никнейм: </span>$nick<br>
                                  <span class=\"mail-span\">Код активации: </span>$randomWord
                                  <p class=\"mail-link\">Вы также можете активировать свой аккаунт просто перейдя по ссылке: <a href=\"$link\">$link</a>";
                    $body = str_replace("{MAIL_TITLE}", "Активация аккаунта - Администрация \"" . Engine::GetEngineInfo("sn") . "\"", $body);
                    $body = str_replace("{MAIL_SITENAME}", Engine::GetEngineInfo("sn") , $body);
                    $body = str_replace("{MAIL_NICKNAME_TO}", "Приветствуем, " . $nick . "!" , $body);
                    $body = str_replace("{MAIL_BODY_MAIN}", $bodyMain, $body);
                    $body = str_replace("{MAIL_FOOTER_INFORMATION}", "С уважением, Администрация \"" . Engine::GetEngineInfo("sn") . "\"<br>
                                                                                 Все права защищены ©", $body);
                    if (!Mailer::SendMail($body, $email, "Активация аккаунта - Администрация \"" . Engine::GetEngineInfo("sn") . "\"")){
                        DataKeeper::Delete("tt_users", ["nickname" => $nick]);
                        return false;
                    } else {
                        if ($referer !== false){
                            $notificator = new UserNotificator($referer);
                            $notificator->createNotify(14, $queryReqRequest);
                        }
                    }
                } else {
                    $bodyMain = "<p> Вы получили данное сообщение, поскольку на нашем сайте при регистрации кто-то указал этот Email. Если это были не Вы, тогда
                                      забудьте о существовании этого письма, предварительно кинув его в мусорку. А так же в небытье пустоты.</p>
                                 <p> На нашем сайте не требуется активация аккаунтов, но мы не хотим, чтобы Вы забылы данные от Ващего аккаунта.</p>
                                  <span class=\"mail-span\">Никнейм: </span>$nick<br>
                                  <span class=\"mail-span\">Пароль: </span>$password";
                    $body = str_replace("{MAIL_TITLE}", "Регистрация аккаунта - Администрация \"" . Engine::GetEngineInfo("sn") . "\"", $body);
                    $body = str_replace("{MAIL_SITENAME}", Engine::GetEngineInfo("sn") , $body);
                    $body = str_replace("{MAIL_NICKNAME_TO}", $nick , $body);
                    $body = str_replace("{MAIL_BODY_MAIN}", $bodyMain, $body);
                    $body = str_replace("{MAIL_FOOTER_INFORMATION}", "С уважением, Администрация \"" . Engine::GetEngineInfo("sn") . "\"<br>
                                                                                 Все права защищены ©", $body);
                    if (!Mailer::SendMail($body, $email, "Регистрация аккаунта - Администрация \"" . Engine::GetEngineInfo("sn") . "\"")) {
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
            DataKeeper::Delete("tt_users", ["id" => $id]);
        }
        public static function GetAllUsers(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT id, nickname FROM tt_users")){
                $stmt->execute();
                $result = [];
                $stmt->bind_result($id, $nickname);
                while ($stmt->fetch()){
                    array_push($result, [$id, $nickname]);
                }
                return $result;
            }
        }
        public static function GetUsersList($paramsArray, $page = 1){

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $lowBorder = ($page-1)*50;
            $highBorder = $page*50;

            if ($paramsArray == 0)
                $query = "SELECT `id` FROM `tt_users` LIMIT $lowBorder, $highBorder";
            else {
                $query = "SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ? AND `email` LIKE ? AND `lastip` LIKE ? AND `referer` LIKE ? AND `group` LIKE ? LIMIT $lowBorder, $highBorder";
                if (isset($paramsArray["nickname"])) $paramsArray["nickname"] = str_replace("*", "%", $paramsArray["nickname"]);
                if (isset($paramsArray["email"])) $paramsArray["email"] = str_replace("*", "%", $paramsArray["email"]);
                if (isset($paramsArray["lastip"])) $paramsArray["lastip"] = str_replace("*", "%", $paramsArray["lastip"]);
            }

            if ($stmt = $mysqli->prepare($query)){
                if ($paramsArray != 0){
                    $prc = "%";
                    if (isset($paramsArray["referer"])) {
                        #Проверка на существование...
                        if (UserAgent::GetUserId($paramsArray["referer"]))
                            $paramsArray["referer"] = UserAgent::GetUserId($paramsArray["referer"]);

                    }
                    if (!isset($paramsArray["nickname"])) $paramsArray["nickname"] = $prc;
                    if (!isset($paramsArray["email"])) $paramsArray["email"] = $prc;
                    if (!isset($paramsArray["lastip"])) $paramsArray["lastip"] = $prc;
                    if (!isset($paramsArray["referer"])) $paramsArray["referer"] = $prc;
                    if (!isset($paramsArray["group"])) $paramsArray["group"] = $prc;
                    $stmt->bind_param("sssss", $paramsArray["nickname"], $paramsArray["email"],
                        $paramsArray["lastip"], $paramsArray["referer"], $paramsArray["group"]);
                }
                $stmt->execute();
                if (mysqli_stmt_errno($stmt)){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id);
                $result = array();
                while ($stmt->fetch()){
                    array_push($result, $id);
                }
                return $result;
            }

            return False;
        }
        public static function GetUsersCount(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_users`")) {
                $stmt->execute();
                $stmt->bind_result($paramProp);
                $stmt->fetch();
                return $paramProp;
            }

            return false;
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
                    return ErrorManager::GetError();
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if($stmt = $mysqli->prepare("SELECT `id` FROM `tt_users` WHERE nickname=? OR email=?")){
                $stmt->bind_param("ss", $param, $param);
                $stmt->execute();
                $stmt->bind_result($result);
                $stmt->fetch();
                $result1 = $result;
                if($result1 == ''){ $stmt->close(); return False;}
                else{ $stmt->close(); $mysqli->close(); return $result1; }
            }

            return False;
        }
        public static function GetUserNick($id){
            if (self::IsUserExist($id) === false) {
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf("Не удалось подключиться: %s\n", mysqli_connect_error());
                return False;
            }

            if($stmt = $mysqli->prepare("SELECT `nickname` FROM `tt_users` WHERE id=?")){
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($result);
                $stmt->fetch();
                $result1 = $result;
                if($result1 == ''){ $stmt->close(); return False;}
                else{ $stmt->close(); $mysqli->close(); return $result1; }
            }

            return False;
        }
        public static function GetUserGroupId($idUser){
            if (!self::IsUserExist($idUser)){
                ErrorManager::GenerateError(7);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `group` FROM `tt_users` WHERE `id`=?")){
                $stmt->bind_param("i", $idUser);
                $stmt->execute();
                if(mysqli_stmt_errno($stmt)){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                } else {
                    $stmt->bind_result($groupId);
                    $stmt->fetch();
                    return $groupId;
                }
            }
            return false;
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_users` WHERE `referer`=?")){
                $stmt->bind_param("i", $idUser);
                $stmt->execute();
                if(mysqli_stmt_errno($stmt)){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                } else {
                    $stmt->bind_result($refCounts);
                    $stmt->fetch();
                    return $refCounts;
                }
            }
            return false;
        }
        /**
         * Return content of user property.
         * @param $idUser int User ID
         * @param $param string Property that should be returned.
         * @return bool|int
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `$param` FROM `tt_users` WHERE `id`=?")) {
                $stmt->bind_param("i", $idUser);
                $stmt->execute();
                $stmt->bind_result($paramProp);
                $stmt->fetch();
                return $paramProp;
            }

            return false;
        }
        /**
         * Return a array with ids users have a nickname like a shedule.
         * @param $Snickname Shedule of nickname.
         * @return mixed
         * In shedule you can use * symbol for unknown substring.
         */
        public static function FindUsersBySNickname($Snickname){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            if (strstr($Snickname, "*") > -1) $Snickname = str_replace("*", "%", $Snickname);
            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?")){
                $stmt->bind_param("s", $Snickname);
                $stmt->execute();
                $result = array();
                $stmt->bind_result($Sid);
                while ($stmt->fetch()){
                    array_push($result, $Sid);
                }
                return $result;
            }
            return false;
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

            if (in_array(Uploader::ExtractType(basename($_FILES[$fileFormName]['name'])), $imgtypes)) $uploaddir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/avatars/";
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


            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_users` SET `avatar`=? WHERE `id`=?")){
                $stmt->bind_param("si", $newName, $idUser);
                $stmt->execute();
                if (mysqli_stmt_errno($stmt)){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return True;
            }

            return False;
        }
        public static function ClearNotifications(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_notifications` WHERE `createTime` < ?")){
                $needlyTime = Engine::GetSiteTime() - 30*24*60*60;
                $stmt->bind_param("i", $needlyTime);
                $stmt->execute();
                if ($stmt->errno){
                    return $stmt->error;
                }
                return true;
            }
        }
        public static function GetOnlineFriendsCount($userId){
            if (!self::IsUserExist($userId)) return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_users` WHERE `id` in (SELECT `friendId` FROM `tt_friends` WHERE `fhost`=?) AND `lasttime` > ?")){
                $time = Engine::GetSiteTime()-60*15;
                $stmt->bind_param("ii", $userId, $time);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                return $count;
            }
            return 0;
        }
        public static function GetOnlineFriends($ofUserId){
            if (!self::IsUserExist($ofUserId)) return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_users` WHERE `id` IN (SELECT `friendId` FROM `tt_friends` WHERE `fhost`=?) AND `lasttime` > ?")){
                $time = Engine::GetSiteTime()-60*15;
                $stmt->bind_param("ii", $ofUserId, $time);
                $stmt->execute();
                if (!$stmt->errno){
                    $res = [];
                    $stmt->bind_result($id);
                    while($stmt->fetch()){
                        array_push($res, $id);
                    }
                    return $res;
                }
            }
            $stmt->close();
            return false;
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

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if (!$stmt = $mysqli->prepare("SELECT count(*) FROM `tt_groups` WHERE `name`=?")) die ($mysqli->error);
            $stmt->bind_param("s", $name);
            $stmt->execute();
            if (mysqli_stmt_errno($stmt)){
                ErrorManager::GenerateError(9);
                return ErrorManager::GetError();
            }
            $stmt->bind_result($res);
            $stmt->fetch();
            if($res >= 1){
                ErrorManager::GenerateError(17);
                return ErrorManager::GetError();
            }
            else return True;

        }

        public static function IsGroupExists($id){

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmt = $mysqli->prepare("SELECT count(*) FROM `tt_groups` WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($result);
            $stmt->fetch();

            if ($result != 0) return True;
            elseif (mysqli_stmt_errno($stmt)) ErrorManager::GenerateError(9);
            else return False;

            return False;
        }
        public static function AddGroup($name, $color, $descript){

            if (!self::CheckNameValid($name) == True) return ErrorManager::GetError();

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmt = $mysqli->prepare("INSERT INTO `tt_groups` (`id`, `name`, `color`, `descript`) VALUE (NULL,?,?,?)");
            $stmt->bind_param("sss", $name, $color, $descript);
            $stmt->execute();

            if (mysqli_stmt_errno($stmt)){ echo mysqli_Stmt_error($stmt);ErrorManager::GenerateError(9); }
            else return True;

            return false;
        }
        public static function RemoveGroup($id){

            if (!self::IsGroupExists($id)) return ErrorManager::GetError();

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmt = $mysqli->prepare("DELETE FROM `tt_groups` WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if (mysqli_stmt_errno($stmt)){ ErrorManager::GenerateError(9); return ErrorManager::GetError(); }

            return True;

        }
        public static function ChangeGroupPerms($id, $type, $typeNew){
            $nonPerms = array(0=>'id', 1=>'name', 2=>'color', 3=>'descript');
            if (in_array($type, $nonPerms)) exit;
            if (!self::IsGroupExists($id)){ ErrorManager::GenerateError(10); return ErrorManager::GetError(); }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if (!$stmt = $mysqli->prepare("UPDATE `tt_groups` SET $type=? WHERE `id`=?")) die($mysqli->error);
            $stmt->bind_param("ii", $typeNew, $id);
            $stmt->execute();

            if (mysqli_stmt_errno($stmt)){ ErrorManager::GenerateError(9); return ErrorManager::GetError(); }

            $stmt->close();
            $mysqli->close();
            return True;

        }
        public static function ChangeGroupData($id, $type, $typeNew){
            $nonPerms = array(1=>'name', 2=>'color', 3=>'descript');
            if (!in_array($type, $nonPerms)) exit;

            if ($type == 'name')
                if (!self::CheckNameValid($typeNew)){
                    return ErrorManager::GetError();
                }


            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if (!$stmt = $mysqli->prepare("UPDATE `tt_groups` SET `$type`=? WHERE `id`=?")) die($mysqli->error);
            $stmt->bind_param("si", $typeNew, $id);
            $stmt->execute();

            if (mysqli_stmt_errno($stmt)){
                ErrorManager::GenerateError(9);
                return ErrorManager::GetError();
            }

            $stmt->close();
            $mysqli->close();

            return True;
        }
        public static function MoveGroupMembers($id, $toId){
            if (!GroupAgent::IsGroupExists($toId)) return False;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if (!$stmt = $mysqli->prepare("UPDATE `tt_users` SET `group`=? WHERE `group`=?")) die($mysqli->error);
            $stmt->bind_param("ii", $toId, $id);
            $stmt->execute();

            if (mysqli_stmt_errno($stmt)){
                ErrorManager::GenerateError(9);
                return ErrorManager::GetError();
            }

            $stmt->close();
            $mysqli->close();

            return True;
        }
        public static function GetGroupList(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmt = $mysqli->prepare("SELECT `id` FROM `tt_groups`");
            $stmt->execute();
            $stmt->bind_result($id);
            $r = array();
            while($stmt->fetch()){
                array_push($r, $id);
            }
            $stmt->close();
            $mysqli->close();
            return $r;
        }
        public static function GetGroupNameById($id){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmt = $mysqli->prepare("SELECT `name` FROM `tt_groups` WHERE `id`=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($result);
            $stmt->fetch();
            $result1 = $result;
            $stmt->close();
            $mysqli->close();
            return $result1;
        }
        public static function GetGroupColor($id){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmt = $mysqli->prepare("SELECT `color` FROM `tt_groups` WHERE `id`=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($result);
            $stmt->fetch();
            $result1 = $result;
            $stmt->close();
            $mysqli->close();
            return $result1;
        }
        public static function GetGroupDescribe($id){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmt = $mysqli->prepare("SELECT `descript` FROM `tt_groups` WHERE `id`=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($result);
            $stmt->fetch();
            $result1 = $result;
            $stmt->close();
            $mysqli->close();
            return $result1;
        }
        public static function GetUsersCountInGroup(int $groupId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM tt_users WHERE group = ?")){
                $stmt->bind_param("i", $groupId);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                return $count;
            }
            return false;
        }
        public static function GetGroupUsers($id, int $page = 1){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $lowBorder = $page * 15 - 15;
            $highBorder = 15;

            if ($stmt = $mysqli->prepare("SELECT id FROM tt_users WHERE `group` = ? ORDER BY id DESC LIMIT $lowBorder, $highBorder")){
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($id);
                $result = [];
                while ($stmt->fetch()){
                    array_push($result, $id);
                }
                return $result;
            }
            return false;
        }
        public static function IsHavePerm($id, $perm){
            $nonPerms = array(0=>'id', 1=>'name', 2=>'color', 3=>'descript');
            if (in_array($perm, $nonPerms)) exit;
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if (mysqli_connect_errno()) {
                printf(mysqli_connect_error() . "<br />");
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `$perm` FROM `tt_groups` WHERE `id`=?")) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($result);
                $stmt->fetch();
                $result1 = $result;
                $stmt->close();
                $mysqli->close();
                return $result1;
            }
        }
    }
}