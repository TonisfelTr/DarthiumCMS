<?php

namespace Users;

use Engine\DataKeeper;
use Engine\Engine;

class PrivateMessager {
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

    private function getStatusForMessage($id) {
        $queryResponse = DataKeeper::Get("tt_pmessages", ["senderUID", "receiverUID"], ["id"        => $id,
            "isVisible" => 1]);
        $sender = $queryResponse[0]["senderUID"];
        $receiver = $queryResponse[0]["receiverUID"];

        if ($sender == $this->userId) {
            return "sender";
        } elseif ($receiver == $this->userId) {
            return "receiver";
        } else {
            return "nobody";
        }

    }

    private function hasAccess($id) {
        if (!in_array($this->getStatusForMessage($id), ["sender", "receiver"])) {
            return false;
        } else {
            return true;
        }
    }

    private function setRead($id) {
        return DataKeeper::Update("tt_pmessages", ["isRead" => true], ["id" => $id]);
    }

    public function __construct($userId) {
        $this->userId = $userId;

        $queryResponse = DataKeeper::MakeQuery("SELECT * 
                                                           FROM `tt_pmessages`
                                                           WHERE `senderUID`=? OR `receiverUID`=? AND `isVisible`=?
                                                           ORDER BY `id` DESC", [$this->userId,
            $this->userId,
            1], true);
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
                array_push($this->bin, ["id"          => $id,
                    "senderUID"   => $senderUID,
                    "receiverUID" => $receiverUID,
                    "subject"     => $subject,
                    "text"        => $text,
                    "isRead"      => $isRead,
                    "receiveTime" => $receiveTime,
                    "isRemoved"   => true,
                    "isVisible"   => 1,
                    "isSaved"     => $isSaved]);
                $this->binSize++;
            } elseif ($senderUID == $this->userId && !$isSaved && !$isRemovedForSender) {
                array_push($this->sended, ["id"          => $id,
                    "senderUID"   => $senderUID,
                    "receiverUID" => $receiverUID,
                    "subject"     => $subject,
                    "text"        => $text,
                    "isRead"      => $isRead,
                    "receiveTime" => $receiveTime,
                    "isRemove"    => false,
                    "isVisible"   => 1,
                    "isSaved"     => $isSaved]);
                $this->sendedSize++;
            } elseif ($senderUID == $this->userId && $isSaved && !$isRemovedForSender) {
                array_push($this->outcomes, ["id"          => $id,
                    "senderUID"   => $senderUID,
                    "receiverUID" => $receiverUID,
                    "subject"     => $subject,
                    "text"        => $text,
                    "isRead"      => $isRead,
                    "receiveTime" => $receiveTime,
                    "isRemove"    => false,
                    "isVisible"   => 1,
                    "isSaved"     => $isSaved]);
                $this->outcomeSize++;
            } elseif ($receiverUID == $this->userId && !$isSaved && !$isRemovedForReceiver) {
                array_push($this->incomes, ["id"          => $id,
                    "senderUID"   => $senderUID,
                    "receiverUID" => $receiverUID,
                    "subject"     => $subject,
                    "text"        => $text,
                    "isRead"      => $isRead,
                    "receiveTime" => $receiveTime,
                    "isRemove"    => false,
                    "isVisible"   => 1,
                    "isSaved"     => false]);
                $this->incomeSize++;
                if ($isRead == false) {
                    $this->notReadCount++;
                }
            }
        }
    }

    public function getNotReadCount() {
        return $this->notReadCount;
    }

    public function getIncomeSize() {
        return $this->incomeSize;
    }

    public function getOutcomeSize() {
        return $this->outcomeSize;
    }

    public function getBinSize() {
        return $this->binSize;
    }

    public function getSendedSize() {
        return $this->sendedSize;
    }

    public function incomes($index = -1) {
        if ($index == -1) {
            return $this->incomes;
        } else {
            return $this->incomes[$index];
        }
    }

    public function outcomes($index = -1) {
        if ($index == -1) {
            return $this->outcomes;
        } else {
            return $this->outcomes[$index];
        }
    }

    public function bin($index = -1) {
        if ($index == -1) {
            return $this->bin;
        } else {
            return $this->bin[$index];
        }
    }

    public function sended($index = -1) {
        if ($index == -1) {
            return $this->sended;
        } else {
            return $this->sended[$index];
        }
    }

    public function send($receiverUID, $subject, $text) {
        return DataKeeper::InsertTo("tt_pmessages", ["senderUID"   => $this->userId,
            "receiverUID" => $receiverUID,
            "subject"     => $subject,
            "text"        => $text,
            "receiveTime" => Engine::GetSiteTime()]);
    }

    public function remove($id) {
        if ($this->getStatusForMessage($id) == "nobody") {
            return false;
        }

        if ($this->getStatusForMessage($id) == "receiver") {
            return DataKeeper::Update("tt_pmessages", ["isRemovedForReceiver" => 1], ["id" => $id]);
        }
        if ($this->getStatusForMessage($id) == "sender") {
            return DataKeeper::Update("tt_pmessages", ["isRemovedForSender" => 1], ["id" => $id]);
        }
    }

    public function save($id) {
        if ($this->getStatusForMessage($id) == "nobody") {
            return false;
        }

        return DataKeeper::Update("tt_pmessages", ["isSaved" => 1], ["id" => $id]);
    }

    public function read($id) {
        if (!$this->hasAccess($id)) {
            return false;
        }

        for ($i = 0; $i < $this->getIncomeSize(); $i++) {
            if ($this->incomes[$i]["id"] == $id) {
                $this->setRead($id);
                return $this->incomes[$i];
            }
        }

        for ($i = 0; $i < $this->getOutcomeSize(); $i++) {
            if ($this->outcomes[$i]["id"] == $id) {
                return $this->outcomes[$i];
            }
        }

        for ($i = 0; $i < $this->getSendedSize(); $i++) {
            if ($this->sended[$i]["id"] == $id) {
                return $this->sended[$i];
            }
        }

        for ($i = 0; $i < $this->getBinSize(); $i++) {
            if ($this->bin[$i]["id"] == $id) {
                return $this->bin[$i];
            }
        }

        return false;
    }

    public function restore($id) {
        if ($this->getStatusForMessage($id) == "nobody") {
            return false;
        }

        if ($this->getStatusForMessage($id) == "receiver") {
            return DataKeeper::Update("tt_pmessages", ["isRemovedForReceiver" => 0], ["id" => $id]);
        }
        if ($this->getStatusForMessage($id) == "sender") {
            return DataKeeper::Update("tt_pmessages", ["isRemovedForSender" => 0], ["id" => $id]);
        }
    }
}