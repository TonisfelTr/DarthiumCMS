<?php

namespace Users;

use Engine\DataKeeper;
use Engine\Engine;
use Engine\ErrorManager;
use Exceptions\Exemplars\NotConnectedToDatabaseError;
use Users\Models\User;

class Reputationer {
    private $userId;
    private $userReputationPoint;
    private $userReputationChanges;

    public function __construct(User $user) {
        $this->userId = $user->getId();
        $this->userReputationPoint = 0;
        $this->userReputationChanges = array();

        $reputations = DataKeeper::Get("tt_reputation", ["*"], ["uid" => $this->userId]);

        foreach ($reputations as $reputation) {
            $this->userReputationChanges[] = [
                "authorId"   => $reputation["authorId"],
                "type"       => $reputation["type"],
                "comment"    => $reputation["comment"],
                "createDate" => $reputation["createDate"]
            ];
            $this->userReputationPoint += ($reputation["type"] == 0) ? -1 : 1;
        }
    }

    public function getReputationChangeByIndex(int $index) {
        return $this->userReputationChanges[$index];
    }

    public function getReputationArray() {
        return $this->userReputationChanges;
    }

    public function getReputationPoints() {
        return $this->userReputationPoint;
    }

    public function addReputationPoint($authorId, $comment, $type) {
        $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

        if ($mysqli->errno) {
            throw new NotConnectedToDatabaseError("Cannot connect to database");
        }

        if ($stmt = $mysqli->prepare("INSERT INTO `tt_reputation` (`id`, `uid`, `authorId`, `type`, `comment`, `createDate`) VALUE (NULL, ?,?,?,?,?)")) {
            $time = Engine::GetSiteTime();
            $stmt->bind_param("iiisi", $this->userId, $authorId, $type, $comment, $time);
            $stmt->execute();
            return true;
        }
        $stmt->close();
        $mysqli->close();
        return false;
    }

    public function getPointsFromUserCount(int $fromUserId) {
        $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

        if ($mysqli->errno) {
            throw new NotConnectedToDatabaseError("Cannot connect to database");
        }

        if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_reputation` WHERE `authorId` = ? AND `uid` = ?")) {
            $stmt->bind_param("ii", $fromUserId, $this->userId);
            $stmt->execute();
            $stmt->bind_result($result);
            $stmt->fetch();
            return $result;
        }
        return false;
    }

    public function removeReputationPoint($commentId) {
        return DataKeeper::Delete("tt_reputation", ["id" => $commentId]);
    }

    public function changeReputationComment(int $commentId, string $newComment) {
        return DataKeeper::Update("tt_reputation", ["comment" => $newComment], ["id" => $commentId]);
    }
}