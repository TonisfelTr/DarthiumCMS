<?php

namespace Users\Services;

use Engine\DataKeeper;

class Session
{
    private const SESSION_TABLE = "tt_users_sessions";

    private $sessionId;
    private $content;
    private $createdAt;

    public function __construct(string $sessionId) {
        $doesSessionExist = DataKeeper::exists(self::SESSION_TABLE, "sessionId", $sessionId);

        if ($doesSessionExist) {
            $info = DataKeeper::Get(self::SESSION_TABLE, ["id", "sessionId", "content", "createdAt"], ["sessionId" => $sessionId]);
            $this->sessionId = $sessionId;
            $this->content = $info[0]["content"];
            $this->createdAt = $info[0]["createdAt"];
        } else {
            DataKeeper::InsertTo(self::SESSION_TABLE, ["sessionId" => $sessionId]);
            $this->sessionId = $sessionId;
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

    public function getContent(string $key = null) {
        if (is_null($key)) {
            return json_decode($this->content, true);
        }

        return json_decode($this->content, true)[$key];
    }

    public function remember() {
        return DataKeeper::Update(self::SESSION_TABLE, ["sessionId" => $this->sessionId, "content" => $this->content], ["sessionId" => $this->sessionId]);
    }

    /**
     * Remove session from database.
     *
     * @return bool Session has been removed successfully.
     */
    public function end() {
        return (bool)DataKeeper::Delete(self::SESSION_TABLE, ["sessionId" => $this->sessionId]);
    }

    public function isEmpty() : bool {
        return empty(json_decode($this->content, true));
    }

    public function getCreatedTime() {
        return $this->createdAt;
    }
}