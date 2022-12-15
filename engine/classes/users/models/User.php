<?php

namespace Users\Models;

use Engine\DataKeeper;
use Engine\Engine;
use Guards\SocietyGuard;
use Users\PrivateMessager;
use Users\Services\Session;
use Users\UserAgent;
use Users\Blacklister;
use Users\Friendlist;
use Users\Notificator;
use Users\Reputationer;

class User {
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
    private $uSession;

    public function __construct($userId) {
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

        $this->uReputation = new Reputationer($this);
        $this->uGroupOwner = new Group($this->uGroupId);
        $this->uBlacklist = new Blacklister($this->uId);
        $this->uPrivateMessages = new PrivateMessager($this->uId);
        $this->uNotifications = new Notificator($this->uId);
        $this->uFriendList = new Friendlist($this->uId);
        $this->uAdditionFields = UserAgent::GetAdditionalFieldsListOfUser($userId);
        $this->uSession = new Session($_COOKIE["PHPSESSID"]);

        return $this;
    }

    public function getSession() : Session {
        return $this->uSession;
    }

    public function getId() {

        return $this->uId;
    }

    public function getNickname() {
        return $this->uNickname;
    }

    public function getEmail() {
        return $this->uEmail;
    }

    public function getActiveStatus() {
        return ($this->uActive === "TRUE") ? true : false;
    }

    public function getActivationCode() {
        return $this->uActive;
    }

    public function getGroupId() {
        return $this->uGroupId;
    }

    public function getRegIp() {
        return $this->uRegIp;
    }

    public function getRegDate() {
        return $this->uRegDate;
    }

    public function getLastDate() {
        return $this->uLastDate;
    }

    public function getLastIp() {
        return $this->uLastIp;
    }

    public function getSignature() {
        return $this->uSignature;
    }

    public function getSex() {
        return $this->uSex;
    }

    public function getHobbies() {
        return $this->uHobbies;
    }

    public function getFrom() {
        return $this->uFrom;
    }

    public function getAbout() {
        return $this->uAbout;
    }

    public function getReputation() {
        return $this->uReputation;
    }

    public function getBirth() {
        return $this->uBirth;
    }

    public function getRealName() {
        return $this->uName;
    }

    public function getReferer() {
        if (UserAgent::IsUserExist($this->uReferer)) {
            return new \Users\Models\User($this->uReferer);
        } else {
            return null;
        }
    }

    public function getAvatar() {
        $avatar = $this->uAvatar;
        if ($avatar == 'no') {
            $result = "../uploads/avatars/no.jpg";
        } else {
            $result = "../uploads/avatars/" . $avatar;
        }

        return $result;
    }

    public function getVK() {
        return $this->uVK;
    }

    public function getSkype() {
        return $this->uSkype;
    }

    public function getLastTime() {
        return $this->uLastTime;
    }

    public function getReportsCreatedCount() {
        $queryResponse = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_reports` WHERE `author`=?", [$this->getId()]);
        if (empty($queryResponse)) {
            return 0;
        } else {
            return $queryResponse["count(*)"];
        }
    }

    public function getAdditionalFields() {
        return $this->uAdditionFields;
    }

    public function IsVKPublic() {
        return ($this->uIsVKPublic) ? true : false;
    }

    public function IsEmailPublic() {
        return ($this->uIsEmailPublic) ? true : false;
    }

    public function IsSkypePublic() {
        return ($this->uIsSkypePublic) ? true : false;
    }

    public function IsBirthdayPublic() {
        return ($this->uIsBirthdayPublic) ? true : false;
    }

    public function IsAccountPublic() {
        return $this->uIsAccountPublic;
    }

    public function isBanned() {
        if (SocietyGuard::IsBanned($this->getId())) {
            return true;
        } else {
            return false;
        }
    }

    public function UserGroup() {
        return $this->uGroupOwner;
    }

    public function Blacklister() {
        return $this->uBlacklist;
    }

    public function MessageManager() {
        return $this->uPrivateMessages;
    }

    public function Notifications() {
        return $this->uNotifications;
    }

    public function FriendList() {
        return $this->uFriendList;
    }

    public function Activate() {
        if ($this->getActiveStatus() != true) {
            return UserAgent::ActivateAccount($this->getId(), $this->getActivationCode());
        } else {
            return false;
        }
    }

    public function passChange($new, bool $relogin) {
        return UserAgent::ChangeUserPassword($this->uId, $new, $relogin);
    }

    public function groupChange($groupId) {
        if (UserAgent::ChangeUserParams($this->getId(), "group", $groupId)) {
            $this->uGroupOwner = null;
            $this->uGroupOwner = new Group($groupId);
        } else {
            return false;
        }
        return false;
    }

}