<?php

namespace Forum;

use Engine\DataKeeper;
use Users\User;

class TopicComment extends ForumAgent{
    private $id;
    private $topicParentId;
    private $text;
    private $authorId;
    private $createDateTime;
    private $changeDateTime;
    private $changeReason;
    private $changerId;

    public function __construct(int $commentId)
    {
        $queryResponse = DataKeeper::Get("tt_topiccomments", ["*"], ["id" => $commentId])[0];

        $this->id = $commentId;
        $this->topicParentId = $queryResponse["topicId"];
        $this->text = $queryResponse["text"];
        $this->authorId = $queryResponse["authorId"];
        $this->createDateTime = $queryResponse["createDate"];
        $this->changeDateTime = $queryResponse["editDate"];
        $this->changeReason = $queryResponse["editReason"];
        $this->changerId = $queryResponse["editorId"];
    }
    public function getId(){
        return $this->id;
    }
    public function getTopicParentId(){
        return $this->topicParentId;
    }
    public function getText(){
        return $this->text;
    }
    public function getAuthorId(){
        return $this->authorId;
    }
    public function author(){
        return \Users\Models\Group($this->authorId);
    }
    public function getCreateDatetime(){
        return $this->createDateTime;
    }
    public function getChangeInfo(){
        return ["editDate" => $this->changeDateTime,
            "editReason" => $this->changeReason,
            "editorId" => $this->changerId];
    }
}