<?php

namespace Forum\Models;

use Engine\DataKeeper;
use Forum\ForumAgent;

class Quize extends ForumAgent{
    private $QuizeId;
    private $QuizeTopicId;
    private $QuizeQuest;
    private $QuizeAnswers;
    private $QuizeVars;
    private $QuizeAnswersCount;

    public function __construct($quizeId)
    {
        $quizeQuery = DataKeeper::Get("tt_quizes", ["id", "topicId", "quest"], ["id" => $quizeId])[0];
        $this->QuizeTopicId = $quizeQuery["topicId"];
        $this->QuizeId = $quizeQuery["id"];
        $this->QuizeQuest = $quizeQuery["quest"];

        $quizeQuery = DataKeeper::Get("tt_quizesvars", ["id", "var"], ["quizId" => $quizeId]);
        foreach ($quizeQuery as $var){
            $this->QuizeVars[] = [$var["id"], $var["var"]];
        }

        $quizeQuery = DataKeeper::Get("tt_quizesanswers", ["*"], ["quizId" => $quizeId]);
        foreach ($quizeQuery as $answer) {
            $this->QuizeAnswers[] = [$answer["userId"], $answer["quizId"], $answer["varId"]];
        }

        $this->QuizeAnswersCount = count($this->QuizeAnswers);
    }
    public function getId(){
        return $this->QuizeId;
    }
    public function getQuestion(){
        return $this->QuizeQuest;
    }
    public function getAnswers(){
        return $this->QuizeAnswers;
    }
    public function getVars(){
        return $this->QuizeVars;
    }
    public function getProcentAnswer($answerId){
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_quizesanswers` WHERE `varId` = ?", [$answerId])["count(*)"];
    }
    public function getTotalAnswers(){
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_quizesanswers` WHERE `quizId` = ?", [$this->QuizeId])["count(*)"];
    }
}