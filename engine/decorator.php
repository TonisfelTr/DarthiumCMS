<?php

namespace SiteBuilders {

    use Engine\DataKeeper;
    use Engine\ErrorManager;

    const SB_TABLE = "tt_staticcomponents";
    const SB_RIGHTSIDE = 1;
    const SB_LEFTSIDE = 2;

    interface HTMLEntityInterface {
        public function getId();
        public function getType();
        public function getContent();
        public function getName();
        public function getVisibility();
        public function changeParam($param, $newValue);
    }

    class Banner extends BannerAgent implements HTMLEntityInterface {
        private $bannerId;
        private $bannerType;
        private $bannerContent;
        private $bannerName;
        private $bannerVisibility;

        public function _construct($idBanner){
            $this->bannerId = $idBanner;
            $bannerInfo = DataKeeper::Get(SB_TABLE, array("name", "content", "type", "isVisible"));
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

    class SidePanel extends SidePanelsAgent implements HTMLEntityInterface {
        private $panelId;
        private $panelType;
        private $panelContent;
        private $panelName;
        private $panelVisiblity;

        public function _construct($panelId){
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

    class BannerAgent{
        //TODO: Проверить, возможно тут будет ошибка с тем, что контент пустой.
        public static function AddSmallBanner($name, $content = null){
            $result = DataKeeper::InsertTo(SB_TABLE, array("type" => "smallbanner",
                                                                              "name" => $name,
                                                                              "content" => $content,
                                                                              "isVisible" => 1));
            if ($result !== false)
                return $result;
            else
                return false;
        }
        public static function AddBigBanner($name, $content = null, $isVisible = false){
            $result = DataKeeper::InsertTo(SB_TABLE, array("type" => "banner",
                                                                        "name" => $name,
                                                                        "content" => $content,
                                                                        "isVisible" => (($isVisible == false) ? 0 : 1)));
            if ($result > 0)
                return $result;
            else
                return false;
        }
        public static function RemoveBanner($idBanner){
            $result = DataKeeper::Delete(SB_TABLE, array("id" => $idBanner));
            if ($result)
                return true;
            else
                return false;
        }
        public static function EditBanner($idBanner, $param, $newValue){
            $result = DataKeeper::Update(SB_TABLE, array($param => $newValue), array("id" => $idBanner));
            if ($result)
                return true;
            else
                return false;
        }
        public static function EditSmallBanner($type, $link){
            switch($type){
                case "first":
                case 1:
                case "1":
                    if (self::IsBannerExists("name", "firstbanner")){
                        $result = DataKeeper::Update(SB_TABLE, array("html" => $link), array("name" => "firstbanner"));
                        return $result;
                        break;
                    }
                case "second":
                case 2:
                case "2":
                    if (self::IsBannerExists("name", "secondbanner")){
                        $result = DataKeeper::Update(SB_TABLE, array("html" => $link), array("name" => "secondbanner"));
                        return $result;
                        break;
                    }
            }
            return false;
        }
        public static function GetBanners($type){
            $result = DataKeeper::Get(SB_TABLE, array("*"), array("type" => $type));
            return $result;
        }
        public static function GetBigBannersCount(){
            $result = DataKeeper::MakeQuery("SELECT count(*) FROM `" . SB_TABLE . "` WHERE `type`=?", array("banner"));
            if (is_array($result)){
                return $result["count(*)"];
            }
            else return $result;
        }
        public static function GetBannersByName($name){
            $result = DataKeeper::Get(SB_TABLE, array("id", "type", "name", "content", "isVisible"), array("name" => $name));
            return $result;
        }
        public static function IsBannerExists($type, $param){
            if ($type == "id" && is_numeric($param)){
                $whereArray = array("id" => $param);
            } elseif ($type == "name" && !empty($param))
                $whereArray = array("name" => $param);

            $result = DataKeeper::Get(SB_TABLE, array("*"), $whereArray);
            if (is_array($result))
                return $result;
            else
                return false;
        }
        public static function GetBannerHTML($bannerId){
            if (self::IsBannerExists("id", $bannerId) === false)
                return false;
            $result = DataKeeper::Get(SB_TABLE, array("content"), array("id" => $bannerId));
            return $result[0]["content"];
        }
    }
    
    class SidePanelsAgent{
        public static function AddSidePanel($side, $name, $content, $isVisible){
            if ($side == SB_LEFTSIDE) $side = "leftside";
            if ($side == SB_RIGHTSIDE) $side = "rightside";
            $result = DataKeeper::InsertTo(SB_TABLE, array(
                "type" => $side,
                "name" => $name,
                "content" => $content,
                "isVisible" => $isVisible
            ));
            if ($result > 0)
                return true;
            else
                return false;
        }
        public static function EditSidePanel($id, array $newContent){
            if (!DataKeeper::isExistsIn(SB_TABLE, "id", $id)){
                ErrorManager::GenerateError(35);
                return ErrorManager::GetError();
            }

            $result = DataKeeper::Update(SB_TABLE, $newContent, ["id" => $id]);
            if ($result)
                return true;
            else
                return false;
        }
        public static function DeleteSidePanel($idPanel){
            if (!DataKeeper::isExistsIn(SB_TABLE, "id", $idPanel)){
                ErrorManager::GenerateError(35);
                return ErrorManager::GetError();
            }

            $result = DataKeeper::Delete(SB_TABLE, ["id" => $idPanel]);
            if ($result)
                return true;
            else
                return false;
        }
        public static function GetPanel($idPanel){
            if (!DataKeeper::isExistsIn(SB_TABLE, "id", $idPanel)){
                ErrorManager::GenerateError(35);
                return ErrorManager::GetError();
            }

            $result = DataKeeper::Get(SB_TABLE, array("name", "type", "content", "isVisible"), array("id" => $idPanel));
            if (is_array($result))
                return $result;
            else
                return false;
        }
        public static function GetPanelsList(){
            $result = DataKeeper::Get(SB_TABLE, array("id"), array("id" => "*"));
            if (is_array($result))
                return $result;
            else
                return false;
        }
    }

}