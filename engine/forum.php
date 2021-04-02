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

    class StaticPagesAgent{
        private static function CreateBTMLFile($name, $text){
            if (file_put_contents("../../site/statics/" . $name . ".txt", self::FilterText($text),FILE_USE_INCLUDE_PATH))
                return true;
            else return false;
        }
        private static function FilterText($text){
            $fText = $text;

            $fText = preg_replace("/(<\?|\?php).*(\?>)/", "", $fText);

            return $fText;
        }

        public static function isPageExists($idPage){
            return DataKeeper::Get("tt_staticpages", ["id"], ["id" => $idPage])[0]["id"] <= 0 ? false : true;
        }

        public static function CreatePage($name, $authorId, $description, $text, $keywords = ""){
            $fetchedRows = DataKeeper::InsertTo("tt_staticpages", ["name" => $name,
                                                                         "description" => $description,
                                                                         "authorId" => $authorId,
                                                                         "createDate" => date("Y-m-d", Engine::GetSiteTime()),
                                                                         "keywords" => $keywords]);
            if ($fetchedRows > 0) {
                if (StaticPagesAgent::CreateBTMLFile($fetchedRows, $text))
                    return true;
                else
                    return false;
            }
            else
                return false;
        }
        public static function RemovePage($idPage)
        {
            if (!self::isPageExists($idPage)) return false;

            if (DataKeeper::Delete("tt_staticpages", ["id" => $idPage]) > 0)
                if (unlink("../../site/statics/$idPage.txt")) return true;
                else return false;
            else return false;
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
            $lowBorder = ($page - 1) * 20;
            $highBorder = $lowBorder + 20;

            return DataKeeper::MakeQuery("SELECT `id` FROM `tt_staticpages` ORDER BY `id` DESC LIMIT $lowBorder, $highBorder", null, true);
        }
        public static function GetPagesListOfName($name, $page = 1){
            $name = "%" . str_replace("*", "%", $name) . "%";
            $lowBorder = ($page - 1) * 20;
            $highBorder = $lowBorder + 20;

            return DataKeeper::MakeQuery("SELECT `id` FROM `tt_staticpages` WHERE `name` LIKE ? ORDER BY `id` DESC LIMIT $lowBorder, $highBorder", [$name], true);
        }
        public static function GetPagesListOfAuthor($author, $page = 1){
            $author = "%" . str_replace("*", "%", $author) . "%";
            $lowBorder = ($page - 1) * 20;
            $highBorder = $lowBorder + 20;

            return DataKeeper::MakeQuery("SELECT `id` FROM `tt_staticpages` WHERE `authorId` = (SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?) ORDER BY `id` DESC LIMIT $lowBorder, $highBorder",
                [$author], true);
        }
        public static function GetPagesCount(){
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_staticpages`")[0]["count(*)"];
        }
        public static function GetLastPageID(){
            return DataKeeper::getMax("tt_staticpages", "id");
        }
        public static function GetPageKeyWords($pageId){
            return DataKeeper::Get("tt_staticpages", ["keywords"], ["id" => $pageId])[0]["keywords"];
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
        private $categoryKeyWords;

        public function __construct($categoryId){
            if (!self::isCategoryExists($categoryId)){
                ErrorManager::GenerateError(32);
                return ErrorManager::GetError();
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
            $queryResponse = DataKeeper::Get("tt_topiccomments", ["*"], ["id" => $commentId])[0];

            $this->id = $commentId;
            $this->topicParentId = $queryResponse["topicId"];
            $this->text = $queryResponse["text"];
            $this->authorId = $queryResponse["authorId"];
            $this->createDateTime = $queryResponse["createDate"];
            $this->changeDateTime = $queryResponse["editDate"];
            $this->changeReason = $queryResponse["editReason"];
            $this->changerId = $queryResponse["editorId"];
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
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_categories` WHERE `id` = ?", [$categoryId])["count(*)"] > 0 ? true : false;
        }
        public static function isTopicExists($topicId){
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_topics` WHERE `id` = ?", [$topicId])["count(*)"] > 0 ? true : false;
        }
        public static function IsExistQuizeInTopic(int $topicId){
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_quizes` WHERE `topicId` = ?", [$topicId])["count(*)"] > 0 ? true : false;
        }
        public static function IsVoted(int $userId, int $quizeId){
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_quizesanswers` WHERE `userId` = ? AND `quizId` = ?", [$userId, $quizeId])["count(*)"] > 0 ? true : false;
        }

        public static function SearchByTopicName($topicName, int $page = 1){
            $lowBorder = $page * 15 - 15;
            $highBorder = 15;
            $topicSubstrName = "%$topicName%";

            return DataKeeper::MakeQuery("SELECT authorId, name FROM tt_topics WHERE name LIKE ? ORDER BY id DESC LIMIT $lowBorder,$highBorder", [$topicSubstrName], true);
        }
        public static function GetCountTopicsByName($topicName){
            $topicNameForQuery = "%$topicName%";
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_topics` WHERE `name` LIKE ?", [$topicNameForQuery], false)["count(*)"];
        }
        public static function SearchByQuizeQuestion($quizeQuest, int $page = 1){
            $lowBorder = $page * 15 - 15;
            $highBorder = 15;
            $needle = "%".$quizeQuest."%";

            return DataKeeper::MakeQuery("SELECT `authorId`, `name` 
                                                 FROM `tt_topics` 
                                                 WHERE `id` IN (SELECT `topicId` 
                                                                FROM `tt_quizes`
                                                                WHERE `quest` LIKE ?)
                                                 LIMIT 0,15", [$needle], true);
        }
        public static function GetCountQuizesByQuestion($quizeQuest){
            return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_quizes` WHERE `quest` LIKE ?", [$quizeQuest])["count(*)"];
        }
        public static function SearchByTopicAuthorNickname($nickName, int $page = 1){
            $lowBorder = $page * 15 - 15;
            $highBorder = 15;
            $authorNickname = "%$nickName%";
            return DataKeeper::MakeQuery("SELECT `authorId`, `name` 
                                                FROM `tt_topics` 
                                                WHERE `authorId` IN (SELECT `id` 
                                                                     FROM `tt_users` 
                                                                     WHERE `nickname` LIKE ?) 
                                                ORDER BY `id` DESC 
                                                LIMIT $lowBorder,$highBorder", [$authorNickname], true);
        }
        public static function GetCountTopicsOfAuthor($nickname){
            $queryResponse = DataKeeper::MakeQuery("SELECT count(*) 
                                                           FROM `tt_topics` AS `topics`
                                                           LEFT JOIN `tt_users` AS `users`
                                                           ON `topics`.`authorId` = `users`.`id` 
                                                           WHERE `users`.`nickname` LIKE ?", ["%$nickname%"]);
            return $queryResponse["count(*)"];
        }

        public static function GetQuizeByTopic(int $topicId){
            return DataKeeper::Get("tt_quizes", ["id"], ["topicId" => $topicId])[0];
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
            DataKeeper::Delete("tt_quizeanswers", ["quizeId" => $quizeId]);
            DataKeeper::Delete("tt_quizevars", ["quizeId" => $quizeId]);
        }

        public static function CreateCategory($name, $descript, $keywords, $public = true, $no_comments = false, $no_new_topics = false){
            return DataKeeper::InsertTo("tt_categories", ["name" => $name,
                                                                "descript" => $descript,
                                                                "public" => (int)$public,
                                                                "no_comment" => (int)$no_comments,
                                                                "no_new_topics" => (int)$no_new_topics,
                                                                "keywords" => $keywords]);
        }
        public static function ChangeCategoryParams($idCategory, $paramName, $newValue){
            if ($paramName == "id") return false;

            return DataKeeper::Update("tt_categories", [$paramName => $newValue], ["id" => $idCategory]);
        }
        public static function DeleteCategory($idCategory){
            if (!self::isCategoryExists($idCategory)){
                return false;
            }

            $toCategoryId = DataKeeper::Get("tt_categories", ["id"], [1]);
            DataKeeper::Delete("tt_categories", ["id" => $idCategory]);
            return DataKeeper::Update("tt_topics", ["categoryId" => $toCategoryId], [$idCategory]);
        }
        public static function GetCategoryList($public = true){
            if ($public == true) $query = "SELECT `id` FROM `tt_categories` WHERE `public`=?";
            else $query = "SELECT `id` FROM `tt_categories`";

            return DataKeeper::MakeQuery($query, $public == true ? [1] : [], true);

        }
        public static function GetCategoryParam($categoryId, $paramName)
        {
            return DataKeeper::Get("tt_categories", [$paramName], ["id" => $categoryId])[0][$paramName];
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
        }
        public static function DeleteTopic(int $topicId){
            if (!ForumAgent::isTopicExists($topicId))
                return false;

            if (DataKeeper::Delete("tt_topics", ["id" => $topicId])){
                DataKeeper::Delete("tt_topicsmarks", ["topicId" => $topicId]);
                DataKeeper::Delete("tt_topiccomments", ["topicId" => $topicId]);
                $quizeId = (ForumAgent::IsExistQuizeInTopic($topicId)) ? ForumAgent::GetQuizeByTopic($topicId) : "";
                DataKeeper::Delete("tt_quizes", ["topicId" => $topicId]);
                DataKeeper::Delete("tt_quizesanswers", ["quizeId" => $quizeId]);
                DataKeeper::Delete("tt_quizesvars", ["quizeId" => $quizeId]);
                return true;
            }

            return false;
        }
        public static function GetTopicList($page = 1, $mini = false, $categoryId = null){
            $lowBorder = $page * 14 - 14;
            $highBorder = 14;

            $stmtQuery = false;
            if (!$mini) {
                if ($categoryId == null) {
                    $query = "SELECT `id` FROM `tt_topics` ORDER BY `id` DESC LIMIT $lowBorder, $highBorder";
                    $stmtQuery = false;
                } else {
                    $query = "SELECT `id` FROM `tt_topics` WHERE `categoryId`=? ORDER BY `id` DESC LIMIT $lowBorder, $highBorder";
                    $stmtQuery = true;
                }
            } else {
                $query = "SELECT `id` FROM `tt_topics` ORDER BY `id` DESC LIMIT 0, 5";
                return DataKeeper::MakeQuery($query, [], true);
            }
            if ($stmtQuery) {
                if (!is_null($categoryId))
                    return DataKeeper::MakeQuery($query, [$categoryId], true);
            } else {
                return DataKeeper::MakeQuery($query, [], true);
            }
            return false;
        }
        public static function GetTopicCount($categoryId = null){
            $stmtQuery = false;
            if (is_null($categoryId)) {
                $query = "SELECT count(*) FROM `tt_topics`";
                $stmtQuery = false;
            } else {
                $query = "SELECT count(*) FROM `tt_topics` WHERE categoryId=?";
                $stmtQuery = true;
            }
            if ($stmtQuery) {
                if (!is_null($categoryId)) {
                    return DataKeeper::MakeQuery($query, [$categoryId])["count(*)"];
                }
            }
            return DataKeeper::MakeQuery($query)["count(*)"];
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

            if (!$mini) {
                return DataKeeper::MakeQuery("SELECT `id` FROM `tt_topics` WHERE `authorId`=? LIMIT 0,5", [$userId], true);
                //$result = DataKeeper::Get("tt_topics", ["id"], ["authorId" => $userId]);
                //return $result;
            }
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
        }
        public static function ChangeTopic(int $topicId, array $whatArray)
        {
            $result = DataKeeper::Update("tt_topics", $whatArray, ["id" => $topicId]);
            return $result;
        }
        public static function GetTopicId(string $topicName){
            return DataKeeper::Get("tt_topics", ["id"], ["name" => $topicName])[0]["id"];
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