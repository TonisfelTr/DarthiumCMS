<?php

namespace Builder\Models;

use Engine\DataKeeper;
use SiteBuilders\BannerAgent;
use SiteBuilders\HTMLEntityInterface;

class Banner extends BannerAgent implements HTMLEntityInterface {
    private $bannerId;
    private $bannerType;
    private $bannerContent;
    private $bannerName;
    private $bannerVisibility;

    public function _construct($idBanner){
        $this->bannerId = $idBanner;
        $bannerInfo = DataKeeper::Get(\Builder\SB_TABLE, array("name", "content", "type", "isVisible"));
        if (!in_array($bannerInfo["type"], ["banner", "smallbanner"]))
            return false;

        $this->bannerContent = $bannerInfo["content"];
        $this->bannerType = $bannerInfo["type"];
        $this->bannerName = $bannerInfo["name"];
        $this->bannerVisibility = $bannerInfo["isVisible"];
    }

    public function getId(){
        return $this->bannerId;
    }

    public function getType(){
        return $this->bannerType;
    }

    public function getContent(){
        return $this->bannerContent;
    }

    public function getName(){
        return $this->bannerName;
    }

    public function getVisibility(){
        return $this->bannerVisibility;
    }

    public function changeParam($param, $newValue){
        return self::EditBanner($this->bannerId, $param, $newValue);
    }
}