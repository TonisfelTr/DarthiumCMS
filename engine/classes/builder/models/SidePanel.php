<?php

namespace Builder\Models;

use Builder\HTMLEntityInterface;
use Builder\Controllers\SidePanelsAgent;

class SidePanel extends SidePanelsAgent implements HTMLEntityInterface {
    private $panelId;
    private $panelType;
    private $panelContent;
    private $panelName;
    private $panelVisiblity;

    public function __construct($panelId){
        $this->panelId = $panelId;
        $panelInfo = self::GetPanel($panelId);
        if (!is_array($panelInfo))
            return false;

        $this->panelType = $panelInfo["type"];
        $this->panelContent = $panelInfo["content"];
        $this->panelName = $panelInfo["name"];
        $this->panelVisiblity = $panelInfo["isVisible"];
    }

    public function getId(){
        return $this->panelId;
    }

    public function getType(){
        return $this->panelType;
    }

    public function getContent(){
        return $this->panelContent;
    }

    public function getName(){
        return $this->panelName;
    }

    public function getVisibility()
    {
        return $this->panelVisiblity;
    }

    public function changeParam($param, $newValue)
    {
        return self::EditSidePanel($this->panelId, array($param => $newValue));
    }
}