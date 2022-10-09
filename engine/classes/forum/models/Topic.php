<?php

namespace Forum\Models;

use Engine\DataKeeper;
use Forum\ForumAgent;
use Users\User;

class Topic extends ForumAgent{
    private $topicId;
    private $topicName;
    private $topicAuthorId;
    private $topicCategoryId;
    private $topicText;
    private $topicPreviewText;
    private $topicCreateDate;
    private $topicSummaMarks;
    private $topicLikes;
    private $topicDislikes;
    private $topicLastEditor;
    private $topicLastEditDatetime;
    private $topicStatus;

    public function __construct($topicId){
        $query = "SELECT `sub`.*, 
                      (SELECT count(`mark`) FROM `tt_topicsmarks` AS `marks` WHERE `marks`.`mark` = 0 AND `marks`.`topicId` = ?) AS `negatives`, 
                      (SELECT count(`mark`) FROM `tt_topicsmarks` AS `marks` WHERE `marks`.`mark` = 1 AND `marks`.`topicId` = ?) AS `positives`
                      FROM (
                            SELECT *
                            FROM `tt_topics` AS `topics`
                            WHERE `id` = ?
                    ) AS `sub`";
        $queryResponse = DataKeeper::MakeQuery($query, [$topicId, $topicId, $topicId], false);

        $this->topicId = $queryResponse["id"];
        $this->topicAuthorId = $queryResponse["authorId"];
        $this->topicCategoryId = $queryResponse["categoryId"];
        $this->topicName = $queryResponse["name"];
        $this->topicText = $queryResponse["text"];
        $this->topicLikes = $queryResponse["positives"];
        $this->topicDislikes = $queryResponse["negatives"];
        $this->topicSummaMarks = $this->topicLikes + $this->topicDislikes;
        $this->topicCreateDate = $queryResponse["createDate"];
        $this->topicPreviewText = $queryResponse["preview"];
        $this->topicLastEditor = $queryResponse["lastEditor"];
        $this->topicLastEditDatetime = $queryResponse["lastEditDateTime"];
        $this->topicStatus = $queryResponse["status"];
    }
    public function getId(){
        return $this->topicId;
    }
    public function getName(){
        return $this->topicName;
    }
    public function getAuthorId(){
        return $this->topicAuthorId;
    }
    public function getAuthor(){
        return new \Users\Models\User($this->topicAuthorId);
    }
    public function getPretext(){
        return $this->topicPreviewText;
    }
    public function getText(){
        return $this->topicText;
    }
    public function getCreateDate(){
        return $this->topicCreateDate;
    }
    public function getCategoryId(){
        return $this->topicCategoryId;
    }
    public function getCategory(){
        return new Category($this->topicCategoryId);
    }
    public function getMarksCount(){
        return $this->topicSummaMarks;
    }
    public function getLikes(){
        return $this->topicLikes;
    }
    public function getDislikes(){
        return $this->topicDislikes;
    }
    public function getLastEditor(){
        return $this->topicLastEditor;
    }
    public function getLastEditDateTime(){
        return $this->topicLastEditDatetime;
    }
    public function getStatus(){
        return $this->topicStatus;
    }
}