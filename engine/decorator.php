<?php

namespace SiteBuilders {

    use Engine\DataKeeper;
    use Engine\ErrorManager;
    use Users\PrivateMessager;
    use Engine\Engine;

    const SB_TABLE = "tt_staticcomponents";
    const SB_NAVIGATOR = "tt_navbar";
    const SB_RIGHTSIDE = 3;
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
                return $result;
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
                return $result[0];
            else
                return false;
        }
        public static function GetPanelsList(){
            $result = DataKeeper::Get(SB_TABLE, array("id"));
            if (is_array($result))
                return $result;
            else
                return false;
        }
    }

    class NavbarAgent {

        public static function GetElements()
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM tt_navbar")) {
                $stmt->execute();
                $stmt->bind_result($id, $type, $content, $parent, $action);
                $result = [];
                while ($stmt->fetch()) {
                    if ($type == "nav-btn") {
                        array_push($result, [$type, $content, $action]);
                    } elseif ($type == "nav-list") {
                        array_push($result, [$id, $type, $content, $action]);
                    }
                }
                return $result;
            }
        }
        public static function GetElementsOfList($parentId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT id,content, action FROM tt_navbar WHERE parent = ?")){
                $stmt->bind_param("i", $parentId);
                $stmt->execute();
                $result = [];
                $stmt->bind_result($id, $content, $action);
                while($stmt->fetch()){
                    array_push($result, [$id, $content, $action]);
                }
                return $result;
            }
            return false;
        }
        public static function AddButton($text, $link){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_navbar` (type, content, parent, action) VALUE (?,?,?,?)")){
                $button = "nav-btn";
                $parent = 0;
                $stmt->bind_param("ssis", $button, $text, $parent, $link);
                $stmt->execute();

                return true;
            }

            return false;
        }
        public static function AddList($name, $content){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_navbar` (type, content, parent,action) VALUE (?,?,?,?)")){
                $button = "nav-list";
                $parent = 0;
                $stmt->bind_param("ssis", $button, $name, $parent, $content);
                $stmt->execute();
                return true;
            }
            return false;
        }
        public static function AddListElement($parentListId, $content, $action){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO tt_navbar (type, content, parent, action) VALUE (?,?,?,?)")){
                $type = "nav-list-element";
                $stmt->bind_param("ssis", $type, $content, $parentListId, $action);
                $stmt->execute();
                return $stmt->insert_id;
            }
            return false;
        }
        public static function RemoveElement($id){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM tt_navbar WHERE id = ? OR parent = ?")){
                $stmt->bind_param("ii", $id, $id);
                $stmt->execute();
                return true;
            }
            return false;
        }
        public static function ChangeElement($id, $content, $action){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE tt_navbar SET content = ?, action = ? WHERE id = ?")){
                $stmt->bind_param("ssi", $content, $action, $id);
                $stmt->execute();
                return true;
            }
            return false;
        }
    }
}