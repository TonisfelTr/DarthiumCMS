<?php

namespace Forum {

    use Engine\DataKeeper;
    use Engine\Engine;
    use Engine\ErrorManager;
    use http\Exception\InvalidArgumentException;
    use Users\PrivateMessager;
    use Users\User;
    use Users\UserAgent;

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
            return file_get_contents("site/statics/$this->pageID.txt", FILE_USE_INCLUDE_PATH);
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
        public static function ChangePageData($idPage, $param, $newValue){
            if (!self::isPageExists($idPage))
                return false;

            if ($param == "id") return false;
            if (!in_array($param, ["id", "authorId", "createDate"]))
                $result = DataKeeper::Update("tt_staticpages", array($param => $newValue), array("id" => $idPage));
            if ($result)
                return true;
            else
                return false;

        }
        public static function EditPage($idPage, $newText){
            return file_put_contents("../../site/statics/$idPage.txt", $newText, FILE_USE_INCLUDE_PATH);
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
        public static function GetPagesListOfName($name, $page = 1){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                ErrorManager::PretendToBeDied(ErrorManager::GetError(), new \mysqli_sql_exception());
            }

            $lowBorder = ($page - 1) * 20;
            $highBorder = $lowBorder + 20;
            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_staticpages` WHERE `name` LIKE ? ORDER BY `id` DESC LIMIT $lowBorder, $highBorder")){
                $name = "%" . str_replace("*", "%", $name) . "%";
                $stmt->bind_param("s", $name);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    ErrorManager::PretendToBeDied(ErrorManager::GetError(), new \mysqli_sql_exception());
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
        public static function GetPagesListOfAuthor($author, $page = 1){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                ErrorManager::PretendToBeDied(ErrorManager::GetError(), new \mysqli_sql_exception());
            }

            $lowBorder = ($page - 1) * 20;
            $highBorder = $lowBorder + 20;
            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_staticpages` WHERE `authorId` `id` = (SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?) ORDER BY `id` DESC LIMIT $lowBorder, $highBorder")){
                $author = "%" . str_replace("*", "%", $author) . "%";
                $stmt->bind_param("s", $author);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    ErrorManager::PretendToBeDied(ErrorManager::GetError(), new \mysqli_sql_exception());
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
        private $topicPreviewText;
        private $topicCreateDate;
        private $topicSummaMarks;
        private $topicLikes;
        private $topicDislikes;
        private $topicLastEditor;
        private $topicLastEditDatetime;
        private $topicStatus;

        public function __construct($topicId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
                if ($stmt = $mysqli->prepare("select *, (sub.negatives + sub.positives) as summa from (
    
                                                    select *,
                                                    (select count(mark) from tt_topicsmarks as marks where marks.mark = 0 and marks.topicId = ?) as negatives, 
                                                    (select count(mark) from tt_topicsmarks as marks where marks.mark = 1 and marks.topicId = ?) as positives
                                                    from tt_topics as topics
                                                    
                                                    ) sub where id = ?")){
                $stmt->bind_param("iii", $topicId, $topicId, $topicId );
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id, $authorId, $categoryId, $name, $text, $preview, $createDate, $lastEditor, $lastEditDateTime, $topicStatus, $negatives, $positives, $summa);
                $stmt->fetch();
                $this->topicId = $id;
                $this->topicAuthorId = $authorId;
                $this->topicCategoryId = $categoryId;
                $this->topicName = $name;
                $this->topicText = $text;
                $this->topicSummaMarks = $summa;
                $this->topicLikes = $positives;
                $this->topicDislikes = $negatives;
                $this->topicCreateDate = $createDate;
                $this->topicPreviewText = $preview;
                $this->topicLastEditor = $lastEditor;
                $this->topicLastEditDatetime = $lastEditDateTime;
                $this->topicStatus = $topicStatus;
            }
            return false;
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
            return new User($this->topicAuthorId);
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
    class Quize extends ForumAgent{
        private $QuizeId;
        private $QuizeTopicId;
        private $QuizeQuest;
        private $QuizeAnswers;
        private $QuizeVars;
        private $QuiseAnswersCount;

        public function __construct($quizeId)
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT id, topicId, quest FROM tt_quizes WHERE id = ?")){
                $stmt->bind_param("i", $quizeId);
                $stmt->execute();
                $stmt->bind_result($id,$topicId, $quest);
                $stmt->fetch();
                $this->QuizeTopicId = $topicId;
                $this->QuizeId = $id;
                $this->QuizeQuest = $quest;
                $stmt = null;
            }

            if ($stmt = $mysqli->prepare("SELECT id, var FROM tt_quizesvars WHERE quizId = ?")){
                $stmt->bind_param("i", $quizeId);
                $stmt->execute();
                $stmt->bind_result($id, $var);
                $varsForQuize = [];
                while($stmt->fetch()){
                    array_push($varsForQuize, [$id, $var]);
                }
                $this->QuizeVars = $varsForQuize;
                $stmt = null;
            }

            if ($stmt = $mysqli->prepare("SELECT * FROM tt_quizesanswers WHERE quizId = ?")){
                $stmt->bind_param("i", $quizeId);
                $stmt->execute();
                $stmt->bind_result($userId, $quizId, $varId);
                $answers = [];
                while($stmt->fetch()){
                    array_push($answers, [$userId, $quizId, $varId]);
                }
                $this->QuizeAnswers = $answers;
                $stmt = null;
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM tt_quizesanswers WHERE quizId = ?")){
                $stmt->bind_param("i", $this->QuizeId);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $this->QuiseAnswersCount = $count;
                $stmt = null;
            }
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
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM tt_quizesanswers WHERE varId = ?")){
                $stmt->bind_param("i", $answerId);
                $stmt->execute();
                $stmt->bind_result($countAnswers);
                $stmt->fetch();
                return $countAnswers;
            }
            return false;
        }
        public function getTotalAnswers(){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM tt_quizesanswers WHERE quizId = ?")){
                $stmt->bind_param("i", $this->QuizeId);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                return $count;
            }
        }
    }
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
            $this->id = $commentId;
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            if ($stmt = $mysqli->prepare("SELECT * FROM tt_topiccomments WHERE id = ?")) {
                $stmt->bind_param("i", $commentId);
                $stmt->execute();
                if ($stmt->errno) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($id, $authorId, $topicParentId, $text, $createDateTime, $changeDateTime, $changeReason, $changerId);
                $stmt->fetch();

                $this->id = $id;
                $this->topicParentId = $topicParentId;
                $this->text = $text;
                $this->authorId = $authorId;
                $this->createDateTime = $createDateTime;
                $this->changeDateTime = $changeDateTime;
                $this->changeReason = $changeReason;
                $this->changerId = $changerId;
            }
            return false;
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
            return new User($this->authorId);
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
        public static function IsExistQuizeInTopic(int $topicId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            if ($stmt = $mysqli->prepare("SELECT count(*) FROM tt_quizes WHERE topicId = ?")){
                $stmt->bind_param("i", $topicId);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                if ($count < 1)
                    return false;
                else
                    return true;
            }
        }
        public static function IsVoted(int $userId, int $quizId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM tt_quizesanswers WHERE userId=? AND quizId=?")){
                $stmt->bind_param("ii", $userId, $quizId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($count);
                $stmt->fetch();
                if ($count > 0)
                    return true;
            }

            return false;
        }

        public static function SearchByTopicName($topicName, int $page = 1){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            $lowBorder = $page * 15 - 15;
            $highBorder = 15;

            if ($stmt = $mysqli->prepare("SELECT authorId, name FROM tt_topics WHERE name LIKE ? ORDER BY id DESC LIMIT $lowBorder,$highBorder")){
                $topicSubstrName = "%$topicName%";
                $stmt->bind_param("s", $topicSubstrName);
                $stmt->execute();
                $result = [];
                $stmt->bind_result($authorId, $name);
                while($stmt->fetch()){
                    array_push($result, [$authorId, $name]);
                }
                return $result;
            }
            return false;
        }
        public static function SearchByQuizeQuestion($quizeQuest, int $page = 1){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            $lowBorder = $page * 15 - 15;
            $highBorder = 15;
            $needle = "%".$quizeQuest."%";
            $result = [];
            $topics = [];
            if ($stmt = $mysqli->prepare("SELECT topicId FROM tt_quizes WHERE quest LIKE ?")){
                $stmt->bind_param("s", $needle);
                $stmt->execute();
                $stmt->bind_result($topicId);
                while($stmt->fetch()){
                    array_push($result, $topicId);
                }
                $stmt = null;
            }

            for ($i = 0; $i < count($result); $i++){
                if ($stmt = $mysqli->prepare("SELECT authorId, name FROM tt_topics WHERE id=? LIMIT $lowBorder,$highBorder")){
                    $stmt->bind_param("i", $result[$i]);
                    $stmt->execute();
                    $stmt->bind_result($authorId, $name);
                    while($stmt->fetch())
                        array_push($topics, [$authorId, $name]);
                    $stmt = null;
                }
            }
            return $topics;
        }
        public static function SearchByTopicAuthorNickname($nickName, int $page = 1){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            $lowBorder = $page * 15 - 15;
            $highBorder = 15;

            $topics = [];
            $result = [];
            if ($stmt = $mysqli->prepare("SELECT id FROM tt_users WHERE nickname LIKE ?")){
                $authorNickname = "%$nickName%";
                $stmt->bind_param("s", $authorNickname);
                $stmt->execute();
                $stmt->bind_result($id);
                while($stmt->fetch()){
                    array_push($result, $id);
                }
                $stmt = null;
            }

            for ($i = 0; $i < count($result); $i++){
                if ($stmt = $mysqli->prepare("SELECT authorId, name FROM tt_topics WHERE authorId=? ORDER BY id DESC LIMIT $lowBorder,$highBorder")){
                    $stmt->bind_param("i", $result[$i]);
                    $stmt->execute();
                    $stmt->bind_result($authorId, $name);
                    while ($stmt->fetch())
                        array_push($topics, [$authorId, $name]);
                }
                $stmt = null;
            }

            return $topics;
        }

        public static function GetQuizeByTopic(int $topicId){
            //return DataKeeper::Get("tt_quizes", ["count(*)"], ["id" => $quizId]);
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));
            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }
            if ($stmt = $mysqli->prepare("SELECT id FROM tt_quizes WHERE topicId = ?")){
                $stmt->bind_param("i", $topicId);
                $stmt->execute();
                $stmt->bind_result($id);
                $stmt->fetch();
                return $id;
            }
        }
        public static function CreateQuize(int $topicId, string $quest, array $answers){
            if ($lastId = DataKeeper::InsertTo("tt_quizes", ["topicId" => $topicId,"quest" => $quest])){
                foreach ($answers as $answer){
                    DataKeeper::InsertTo("tt_quizesvars", ["var" => $answer, "quizId" => $lastId]);
                }
            }

        }
        public static function VoteInQuize(int $voterId, int $quizId, int $answer){
            if (DataKeeper::InsertTo("tt_quizesanswers", ["userId" => $voterId,
                                                               "quizId" => $quizId,
                                                                "varId" => $answer]))
                return true;
            else
                return false;
        }
        public static function DeleteQuize(int $quizeId){
            DataKeeper::Delete("tt_quizes", ["id" => $quizeId]);
            DataKeeper::Delete("tt_quizeanswers", ["quizId" => $quizeId]);
            DataKeeper::Delete("tt_quizevars", ["quizId" => $quizeId]);
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
        public static function GetCategoryParam($categoryId, $paramName)
        {
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno) {
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `$paramName` FROM `tt_categories` WHERE `id`=?")) {
                $stmt->bind_param("i", $categoryId);
                $stmt->execute();
                if ($stmt->errno) {
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($var);
                $stmt->fetch();
                return $var;
            }
            return false;
        }

        public static function CreateTopic(int $userId, $name, int $categoryId, $preview, $text){
            if (strlen($name) > 100 || strlen($name) <= 4)
                return false;

            if (!ForumAgent::isCategoryExists($categoryId))
                return false;

            if (strlen($preview) == 0){
                $preview = substr($text, 0, 250);
                $preview .= "...";
            }

            $int = DataKeeper::InsertTo("tt_topics", ["id" => NULL,
                "authorId" => $userId,
                "categoryId" => $categoryId,
                "name" => $name,
                "text" => $text,
                "preview" => $preview,
                "createDate" => date("Y-m-d H:i:s", Engine::GetSiteTime()),
                "status" => 1]);

            if ($int !== false) {
                return $int;
            }
            else
                return false;

            return false;
        }
        public static function DeleteTopic(int $topicId){
            if (!ForumAgent::isTopicExists($topicId))
                return false;

            if (DataKeeper::Delete("tt_topics", ["id" => $topicId])){
                $quizId = (ForumAgent::IsExistQuizeInTopic($topicId)) ? ForumAgent::GetQuizeByTopic($topicId) : "";
                DataKeeper::Delete("tt_quizes", ["topicId" => $topicId]);
                DataKeeper::Delete("tt_quizesanswers", ["quizId" => $quizId]);
                DataKeeper::Delete("tt_quizesvars", ["quizId" => $quizId]);
                return true;
            }

            return false;
        }
        public static function GetTopicList($page = 1, $mini = false, $categoryId = null){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $lowBorder = $page * 14 - 14;
            $highBorder = 14;

            $stmtQuery = false;
            if (!$mini) {
                if ($categoryId == null) {
                    if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_topics` ORDER BY `id` DESC LIMIT $lowBorder, $highBorder")) $stmtQuery = false;
                } else {
                    if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_topics` WHERE `categoryId`=? ORDER BY `id` DESC LIMIT $lowBorder, $highBorder")) $stmtQuery = true;
                }
            } else {
                if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_topics` ORDER BY `id` DESC LIMIT 0, 5")) $stmtQuery = false;
            }
            if ($stmtQuery) {
                if (!is_null($categoryId))
                    $stmt->bind_param("i", $categoryId);
            }

            $stmt->execute();

            if ($stmt->errno){
                echo $stmt->error;
            }
            $stmt->bind_result($id);
            $result = [];
            while ($stmt->fetch()){
                array_push($result, $id);
            }
            return $result;

        }
        public static function GetTopicCount($categoryId = null){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            $stmtQuery = false;
            if (is_null($categoryId)) {
                if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_topics`")) $stmtQuery = false;
            } else {
                if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_topics` WHERE categoryId=?")) $stmtQuery = true;
            }
            if ($stmtQuery) {
                if (!is_null($categoryId)) {
                    $stmt->bind_param("i", $categoryId);
                }
            }
            $stmt->execute();
            $stmt->bind_result($result);
            $stmt->fetch();
            return $result;

        }
        public static function GetCountTopicOfAuthor($userId){
            if (!UserAgent::IsUserExist($userId)){
                ErrorManager::GenerateError(11);
                ErrorManager::PretendToBeDied("User with ID $userId is not exist.", new InvalidArgumentException());
            }
            $result = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_topics` WHERE `authorId`=?", [$userId]);
            return $result["count(*)"];
        }
        public static function GetTopicsOfAuthor($userId, $mini = false, $page = 1){
            if (!UserAgent::IsUserExist($userId)){
                ErrorManager::GenerateError(11);
                ErrorManager::PretendToBeDied("User with ID $userId is not exist.", new InvalidArgumentException());
            }

            if (!$mini)
                return DataKeeper::MakeQuery("SELECT `id` FROM `tt_topics` WHERE `authorId`=? LIMIT 0,5", [$userId]);
            else {
                $start = ($page - 1) * 15;
                $end = $start + 15;
                return DataKeeper::MakeQuery("SELECT `id` FROM `tt_topics` WHERE `authorId`=? LIMIT $start,$end", [$userId]);
            }
        }
        public static function EstimateTopic(int $topicId, int $userId, int $mark){
            if (!ForumAgent::isTopicExists($topicId) || $mark > 1 || $mark < 0)
                return false;
            $dbmark = DataKeeper::MakeQuery("SELECT `mark` FROM `tt_topicsmarks` WHERE `topicId`=? AND `userId`=?", [$topicId, $userId]);
            if (empty($dbmark)){
                if (DataKeeper::InsertTo("tt_topicsmarks", ["topicId" => $topicId, "userId" => $userId, "mark" => $mark]))
                    return true;
                else
                    return false;
            }
            elseif ($dbmark["mark"] == $mark){
                if (DataKeeper::Delete("tt_topicsmarks", ["topicId" => $topicId, "userId" => $userId]))
                    return true;
                else
                    return false;
            }
            else {
                if (DataKeeper::Update("tt_topicsmarks", ["mark" => $mark], ["topicId" => $topicId, "userId" => $userId]))
                    return true;
                else
                    return false;
            }

            return false;
        }
        public static function ChangeTopic(int $topicId, array $whatArray)
        {
            $result = DataKeeper::Update("tt_topics", $whatArray, ["id" => $topicId]);
            return $result;
        }
        public static function GetTopicId(string $topicName){
            return DataKeeper::Get("tt_topics", ["id"], ["name" => $topicName]);
        }

        public static function CreateComment(int $authorId, int $topicId, string $text){

            return DataKeeper::InsertTo("tt_topiccomments",
                    ["id" => null,
                    "authorId" => $authorId,
                    "topicId" => $topicId,
                    "text" => $text,
                    "createDate" => Engine::GetSiteTime()
                    ]);
        }
        public static function EditComment(int $commentId, int $editorId, string $editReason, int $editTime, string $newText){
            return DataKeeper::Update("tt_topiccomments", [
                "editorId" => $editorId,
                "editReason" => $editReason,
                "text" => $newText,
                "editDate" => $editTime], ["id" => $commentId]);
        }
        public static function DeleteComment(int $commentId){
            return DataKeeper::Delete("tt_topiccomments", ["id" => $commentId]);
        }
        public static function GetCountOfCommentOfUser(int $userId){
            $result = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_topiccomments` WHERE authorId = ?", [$userId]);
            return $result["count(*)"];
        }
        public static function GetCommentsOfTopic(int $topicId, int $page = 1){
            if ($page < 1)
                throw new InvalidArgumentException("Page number cannot be less then 0.");

            //Откуда отсчёт
            $lowBorder = $page * 10 - 10;
            //Сколько отсчитывать
            $highBorder = 10;

            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT `id` FROM `tt_topiccomments` WHERE `topicId` = ? LIMIT $lowBorder,$highBorder")){
                $stmt->bind_param("i", $topicId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($topicsId);
                $result = [];
                while ($stmt->fetch()){
                    array_push($result, $topicsId);
                }
                return $result;
            }
            return false;
        }
        public static function GetTotalCommentsOfTopic(int $topicId){
            $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

            if ($mysqli->errno){
                ErrorManager::GenerateError(2);
                return ErrorManager::GetError();
            }

            if ($stmt = $mysqli->prepare("SELECT count(*) FROM `tt_topiccomments` WHERE `topicId` = ?")){
                $stmt->bind_param("i", $topicId);
                $stmt->execute();
                if ($stmt->errno){
                    ErrorManager::GenerateError(9);
                    return ErrorManager::GetError();
                }
                $stmt->bind_result($count);
                $stmt->fetch();
                return $count;
            }
        }
        public static function CreateMentionNotification(string $type, int $userId, int $whereId, string $text){
            //Searching for mentions.
            preg_match_all("/@([A-Za-z0-9\-_]+)/", $text, $matches);
            //
            //print_r($matches[1]); =>
            //Array ( [0] => Admin,
            //       [1] => 7584847575 )
            for ($i = 0; $i < count($matches[1]); $i++){
                $resultChecking = false;
                if ($toUser = UserAgent::GetUserId($matches[1][$i])){
                    $resultChecking = true;
                }

                if ($resultChecking){
                    switch($type){
                        case 'c':
                            UserAgent::GetUser($toUser)->Notifications()->createNotify('22', $userId, $whereId);
                            break;
                        case 't':
                            UserAgent::GetUser($toUser)->Notifications()->createNotify('21', $userId, $whereId);
                            break;
                        default:
                            throw new \InvalidArgumentException("Invalid type of notification");

                    }
                }
            }
        }
    }
}