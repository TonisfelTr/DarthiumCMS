<?php

namespace Users;

use Engine\DataKeeper;
use Engine\Engine;
use Users\UserAgent;

class Friendlist {
    private $userId;
    private $friendCount = 0;
    private $friendList = [];

    public function __construct($userId) {
        $this->userId = $userId;

        $friends = DataKeeper::Get("tt_friends", ["friendId", "regdate"], ["fhost" => $userId]);
        foreach ($friends as $friend) {
            $this->friendList[] = ["fhost"    => $this->userId,
                "friendId" => $friend["friendId"],
                "regdate"  => $friend["regdate"]];
            $this->friendCount++;
        }
    }

    public function isFriend($userId) {
        if (DataKeeper::MakeQuery("SELECT count(*) FROM tt_friends WHERE `fhost`=? AND `friendId`=?", [$this->userId,
                $userId])["count(*)"] >= 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getFriendsCount() {
        return $this->friendCount;
    }

    public function getFriendsList() {
        return $this->friendList;
    }

    public function getFriendFromDB($friendId) {
        if (!$this->isFriend($friendId)) {
            return false;
        }

        $friend = DataKeeper::Get("tt_friends", ["regdate"], ["friendId" => $friendId, "fhost" => $this->userId]);
        return ["fhost"    => $this->userId,
            "friendId" => $friendId,
            "regdate"  => $friend[0]["regdate"]];
    }

    public function getOnlineFriendCount() {
        return UserAgent::GetOnlineFriendsCount($this->userId);
    }

    public function getOnlineFriends() {
        return UserAgent::GetOnlineFriends($this->userId);
    }

    public function addFriend($friendId) {
        if ($this->isFriend($friendId)) {
            return false;
        }

        return DataKeeper::InsertTo("tt_friends", ["fhost"    => $this->userId,
            "friendId" => $friendId,
            "regdate"  => Engine::GetSiteTime()]);
    }

    public function deleteFriend($friendId) {
        if (!$this->isFriend($friendId)) {
            return false;
        }

        return DataKeeper::Delete("tt_friends", ["fhost" => $this->userId, "friendId" => $friendId]);
    }
}