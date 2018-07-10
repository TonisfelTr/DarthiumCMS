<?php

namespace Forum {
    use Engine\Engine;
    use Engine\ErrorManager;
    use Users\User;

    class StaticPage{
        private $pageAuthorId;
        private $pageName;
        private $pageCreateDate;
        private $pageID;
        private $pageDescription;

        public function __construct($idPage)
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM `tt_staticpages` WHERE `id` = ?")){
                $stmt->bind_param("i",$idPage);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id, $name, $description, $authorId, $createDate);
                $stmt->fetch();
                $this->pageID = $id;
                $this->pageName = $name;
                $this->pageDescription = $description;
                $this->pageAuthorId = $authorId;
                $this->pageCreateDate = $createDate;
            }
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
            return file_get_contents("../../site/statics/$this->pageID.html", FILE_USE_INCLUDE_PATH);
        }
    }

    class StaticPagesAgent{
        private static function CreateBTMLFile($name, $text){
            function writeTitle($name){
                $title = StaticPagesAgent::GetPage($name)->getPageName();
                if (file_put_contents("../../site/statics/" . $name . ".html", "<title>$title</title>",FILE_USE_INCLUDE_PATH | FILE_APPEND))
                    return true;
                else return false;
            }
            if (file_put_contents("../../site/statics/" . $name . ".txt", self::FilterText($text),FILE_USE_INCLUDE_PATH) && writeTitle($name))
                return true;
            else return false;
        }
        private static function FilterText($text){
            $fText = $text;

            $fText = preg_replace("/(<\?|\?php).*(\?>)/", "", $fText);

            return $fText;
        }

        public static function isPageExists($idPage){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_staticpages` WHERE `id`=?")){
                $stmt->bind_param("i", $idPage);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($result);
                $stmt->fetch();
                if ($result == $idPage) return true;
                else return false;
            }
            $stmt->close();
            return true;
        }

        public static function CreatePage($name, $authorId, $description, $text){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_staticpages` (`id`, `name`, `description`, `authorId`, `createDate`) VALUE (NULL,?,?,?,?)")){
                $time = date("Y-m-d");
                $stmt->bind_param("ssis", $name, $description, $authorId, $time);
                $stmt->execute();
                if ($stmt->errno){
                    echo $stmt->error;
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
            }
            $stmt->close();

            if ($stmt = $mysqli->prepare("SELECT MAX(`id`) FROM `tt_staticpages`")){
                $stmt->execute();
                if ($stmt->errno){
                    echo $stmt->error;
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($result);
                $stmt->fetch();
                if (StaticPagesAgent::CreateBTMLFile($result, $text))
                    return true;
            }
            echo $stmt->error;
            echo $mysqli->error;
            $stmt->close();
            return false;
        }
        public static function RemovePage($idPage)
        {
            if (!self::isPageExists($idPage)) return false;
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_staticpages` WHERE `id`=?")) {
                $stmt->bind_param("i", $idPage);
                $stmt->execute();
                if ($stmt->errno) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                if (unlink("../../site/statics/$idPage.html")) return true;
            }
            return false;
        }
        public static function EditPage($idPage){
            if (!self::isPageExists($idPage)) return false;
            return file_get_contents("../../site/statics/$idPage.html", FILE_USE_INCLUDE_PATH);
        }
        public static function GetPage($idPage){
            return new StaticPage($idPage);
        }
        public static function GetPagesList($page = 1){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            $lowBorder = ($page - 1) * 20;
            $highBorder = $lowBorder + 20;
            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_staticpages` ORDER BY `id` DESC LIMIT $lowBorder, $highBorder")){
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id);
                $result = [];
                while($stmt->fetch()){
                    array_push($result, $id);
                }
                return $result;
            }
            return false;
        }
        public static function GetPagesCount(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_staticpages`")){
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($count);
                return $count;
            }
            return false;
        }
        public static function GetLastPageID(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT MAX(`id`) FROM `tt_staticpages`")){
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id);
                $stmt->fetch();
                return $id;
            }
            return false;
        }
    }

    class Category extends ForumAgent{
        private $categoryId;
        private $categoryName;
        private $categoryDescription;
        private $categoryIsPublic;
        private $categoryNoComments;
        private $categoryNoTopics;
        private $categoryAddedGroups;

        public function __construct($categoryId){
            if (!self::isCategoryExists($categoryId)){
                ErrorManager::GenerateError(32);
                return ErrorManager::GetError();
            }

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM `tt_categories` WHERE `id`=?")){
                $stmt->bind_param("i", $categoryId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id, $name, $descript, $public, $no_comments, $no_new_topics, $added);
                $stmt->fetch();
                $this->categoryId = $id;
                $this->categoryName = $name;
                $this->categoryDescription = $descript;
                $this->categoryIsPublic = $public;
                $this->categoryNoComments = ($no_comments === 1) ? true : false;
                $this->categoryNoTopics = ($no_new_topics === 1) ? true : false;
                $this->categoryAddedGroups = explode(",", $added);
            }

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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_topics` WHERE `categoryId`=?")){
                $stmt->bind_param("i", $this->categoryId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($var);
                $stmt->fetch();
                return $var;
            }
            return false;
        }
    }
    class Topic extends ForumAgent{
        private $topicId;
        private $topicName;
        private $topicAuthorId;
        private $topicCategoryId;
        private $topicText;
        private $topicMark;
        private $topicCreateDate;

        private $topicAuthor;
        private $topicCategory;

        public function __construct($topicId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM `tt_categories` WHERE `id`=?")){
                $stmt->bind_param("i", $topicId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id, $authorId, $categoryId, $name, $text, $mark, $createDate);
                $stmt->fetch();
                $this->topicId = $id;
                $this->topicAuthorId = $authorId;
                $this->topicCategoryId = $categoryId;
                $this->topicName = $name;
                $this->topicText = $text;
                $this->topicMark = $mark;
                $this->topicCreateDate = $createDate;
                $this->topicAuthor = new User($authorId);
                $this->topicCategory = new Category($categoryId);
            }
            return false;
        }
    }

    class ForumAgent {
        public static function isCategoryExists($categoryId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_categories` WHERE `id`=?")){
                $stmt->bind_param("i", $categoryId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($var);
                $stmt->fetch();
                if ($var > 0) return true;
            }
            return false;
        }
        public static function isTopicExists($topicId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_topics` WHERE `id`=?")){
                $stmt->bind_param("i", $topicId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($var);
                $stmt->fetch();
                if ($var > 0) return true;
            }
            return false;
        }

        public static function CreateCategory($name, $descript, $public = true, $no_comments = false, $no_new_topics = false){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("INSERT INTO `tt_categories` (`id`, `name`, `descript`, `public`, `no_comment`, `no_new_topics`) VALUE (NULL,?,?,?,?,?)")){
                $stmt->bind_param("ssiii", $name, $descript, $public, $no_comments, $no_new_topics);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }
            return false;
        }
        public static function ChangeCategoryParams($idCategory, $paramName, $newValue){
            if ($paramName == "id") return false;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("UPDATE `tt_categories` SET `$paramName`=? WHERE `id`=?")){
                $stmt->bind_param("si", $newValue, $idCategory);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }

            return false;
        }
        public static function DeleteCategory($idCategory){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("DELETE FROM `tt_categories` WHERE `id`=?")){
                $stmt->bind_param("i", $idCategory);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                return true;
            }

            return false;

        }
        public static function GetCategoryList($public = true){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($public == true) $query = "SELECT `id` FROM `tt_categories` WHERE `public`=?";
            else $query = "SELECT `id` FROM `tt_categories`";

            if ($stmt = $mysqli->prepare($query)){
                if ($public == true){
                    $public = 1;
                    $stmt->bind_param("i", $public);
                }
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $result = array();
                $stmt->bind_result($var);
                while($stmt->fetch()){
                    array_push($result, $var);
                }
                return $result;
            }
            return false;
        }
        public static function GetCategoryParam($categoryId, $paramName){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `$paramName` FROM `tt_categories` WHERE `id`=?")){
                $stmt->bind_param("i", $categoryId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($var);
                $stmt->fetch();
                return $var;
            }
            return false;
        }
        public static function GetTopicList($page = 1, $categoryId = null){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $lowBorder = ($page - 1) * 15;
            $highBorder = $lowBorder + 15;

            $stmtQuery = false;
            if (is_null($categoryId)) {
                if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_topics` ORDER BY `id` DESC LIMIT $lowBorder, $highBorder")) $stmtQuery = true;
            } else {
                if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_topics` WHERE `categoryId`=? ORDER BY `id` DESC LIMIT $lowBorder, $highBorder")) $stmtQuery = true;
            }
            if ($stmtQuery){
                if (!is_null($categoryId))
                    $stmt->bind_param("i",$categoryId);
                $stmt->execute();
                $stmt->bind_result($id);
                $result = [];
                while ($stmt->fetch()){
                    array_push($result, $id);
                }
                return $result;
            }
            return false;
        }
        public static function GetTopicCount($categoryId = null){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmtQuery = false;
            if (is_null($categoryId)) {
                if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_topics`")) $stmtQuery = true;
            } else {
                if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_topics` WHERE `categoryId`=?")) $stmtQuery = true;
            }
            if ($stmtQuery){
                if (!is_null($categoryId)){
                    $stmt->bind_param("i", $categoryId);
                }
                $stmt->execute();
                $stmt->bind_result($result);
                $stmt->fetch();
                return $result;
            }

            return false;

        }

        public static function GetCategory($idCategory){
            return new Category($idCategory);
        }

    }
}