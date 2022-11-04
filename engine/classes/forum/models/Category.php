<?php

namespace Forum\Models;

use Engine\DataKeeper;
use Engine\ErrorManager;
use Exceptions\Exemplars\CategoryNotExistError;
use Forum\ForumAgent;

class Category extends ForumAgent{
    private $categoryId;
    private $categoryName;
    private $categoryDescription;
    private $categoryIsPublic;
    private $categoryNoComments;
    private $categoryNoTopics;
    private $categoryAddedGroups;
    private $categoryKeyWords;

    public function __construct($categoryId){
        if (!self::isCategoryExists($categoryId)){
            throw new CategoryNotExistError("This category doesn't exist");
        }

        $queryResponse = DataKeeper::Get("tt_categories", ["*"], ["id" => $categoryId])[0];

        $this->categoryId = $categoryId;
        $this->categoryName = $queryResponse["name"];
        $this->categoryDescription = $queryResponse["descript"];
        $this->categoryIsPublic = $queryResponse["public"] == 1 ? true : false;
        $this->categoryNoComments = $queryResponse["no_comment"] == 1 ? true : false;
        $this->categoryNoTopics = $queryResponse["no_new_topics"] == 1 ? true : false;
        $this->categoryAddedGroups = $queryResponse["added"];
        $this->categoryKeyWords = $queryResponse["keywords"];
    }
    public function getId(){
        return $this->categoryId;
    }
    public function getName(){
        return $this->categoryName;
    }
    public function getDescription(){
        return $this->categoryDescription;
    }
    public function isPublic(){
        return $this->categoryIsPublic;
    }
    public function CanCreateComments(){
        return $this->categoryNoComments;
    }
    public function CanCreateTopic(){
        return $this->categoryNoTopics;
    }
    public function isGroupAdded($groupId){
        return in_array($groupId, $this->categoryAddedGroups);
    }
    public function delete(){
        return self::DeleteCategory($this->categoryId);
    }
    public function setParam($paramName, $newValue){
        if ($paramName == "id") return false;
        else return self::ChangeCategoryParams($this->categoryId, $paramName, $newValue);
    }
    public function getTopicsCount(){
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_topics` WHERE `categoryId` = ?", [$this->categoryId])["count(*)"];
    }
    public function getKeyWords(){
        return $this->categoryKeyWords;
    }
}