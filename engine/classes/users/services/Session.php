<?php

namespace Users\Services;

use Engine\DataKeeper;

class Session
{
    private const SESSION_TBALE = "tt_users_sessions";

    private $sessionId;
    private $content;
    private $createdAt;

    public function __construct(string $sessionId) {
        $doesSessionExist = DataKeeper::exists(self::SESSION_TBALE, "sessionId", $sessionId);

        if ($doesSessionExist) {
            $info = DataKeeper::Get(self::SESSION_TBALE, ["id", "sessionId", "content", "createdAt"], ["sessionId" => $sessionId]);
            $this->sessionId = $sessionId;
            $this->content = $info["content"];
            $this->createdAt = $info["createdAt"];
        } else {
            DataKeeper::InsertTo(self::SESSION_TBALE, ["sessionId" => $sessionId]);
        }
    }

    public function setContentParam(string $key, string $value) : Session {
        $jsonContent = json_decode($this->content, true);
        $jsonContent[$key] = $value;

        $this->content = json_encode($jsonContent);

        return $this;
    }

    public function setContent(array $jsonStructure) : Session {
        $this->content = json_encode($jsonStructure);

        return $this;
    }

    public function getContent() : array {
        return json_decode($this->content, true);
    }

    public function remember() {
        return DataKeeper::Update(self::SESSION_TBALE, ["sessionId" => $this->sessionId, "content" => $this->content], ["sessionId" => $this->sessionId]);
    }

    public function end() {
        return DataKeeper::Delete(self::SESSION_TBALE, ["sessionId" => $this->sessionId]);
    }

    public function isEmpty() : bool {
        return empty(json_decode($this->content, true));
    }

    public function getCreatedTime() {
        return $this->createdAt;
    }
}