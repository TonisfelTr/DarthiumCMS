<?php

namespace Forum\Models;

use Engine\DataKeeper;

class StaticPage{
    private $pageAuthorId;
    private $pageName;
    private $pageCreateDate;
    private $pageID;
    private $pageDescription;
    private $pageKeyWords;

    public function __construct($idPage)
    {
        $queryResponse = DataKeeper::Get("tt_staticpages", ["*"], ["id" => $idPage])[0];

        $this->pageID = $queryResponse["id"];
        $this->pageName = $queryResponse["name"];
        $this->pageDescription = $queryResponse["description"];
        $this->pageAuthorId = $queryResponse["authorId"];
        $this->pageCreateDate = $queryResponse["createDate"];
        $this->pageKeyWords = $queryResponse["keywords"];
    }
    public function getPageAuthorId(){
        return $this->pageAuthorId;
    }
    public function getPageName(){
        return $this->pageName;
    }
    public function getPageID(){
        return $this->pageID;
    }
    public function getPageCreateDate(){
        return $this->pageCreateDate;
    }
    public function getPageDescription(){
        return $this->pageDescription;
    }
    public function getContent(){
        return file_get_contents("site/statics/$this->pageID.txt", FILE_USE_INCLUDE_PATH);
    }
    public function getKeyWords(){
        return $this->pageKeyWords;
    }
}