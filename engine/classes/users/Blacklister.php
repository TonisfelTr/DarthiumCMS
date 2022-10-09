<?php

namespace Users;

use Engine\DataKeeper;
use Engine\Engine;

class Blacklister {
    private $uId;
    private $uArray = [];

    public function __construct($userId) {
        $this->uId = $userId;

        $blacked = DataKeeper::Get("tt_blacklisted", ["blockId",
            "comment",
            "addedtime"], ["authorId" => $this->uId]);

        foreach ($blacked as $person) {
            $this->uArray[] = ["bid"       => $person["blockId"],
                "comment"   => $person["comment"],
                "addedtime" => $person["addedtime"]];
        }
    }

    public function getList() {
        return $this->uArray;
    }

    public function add(int $userId, string $comment = "") {
        return DataKeeper::InsertTo("tt_blacklisted", ["authorId" => $this->uId,
            "blockId"  => $userId,
            "comment"  => $comment,
            "date"     => date("Y-m-d H:i:s", Engine::GetSiteTime())]);
    }

    public function isBlocked(int $userId) {
        foreach ($this->uArray as $person) {
            if (in_array($userId, $person)) {
                return true;
            }
        }
        return false;
    }

    public function remove(int $userId): bool {
        if (!$this->isBlocked($userId)) {
            return false;
        }

        return DataKeeper::Delete("tt_blacklisted", ["authorId" => $this->uId, "blockId" => $userId]);
    }

    public function getBlockedInfo($userId) {
        if (!$this->isBlocked($userId)) {
            return false;
        }

        foreach ($this->uArray as $info) {
            if (in_array($userId, $info)) {
                return $info;
            }
        }

        return false;
    }
}