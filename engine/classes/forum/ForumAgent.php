<?php

namespace Forum;

use Engine\DataKeeper;
use Engine\Engine;
use Engine\ErrorManager;
use Users\UserAgent;

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

        return DataKeeper::MakeQuery("SELECT authorId, name FROM tt_topics WHERE name LIKE ? OR text LIKE ? OR preview LIKE ? ORDER BY id DESC LIMIT $lowBorder,$highBorder",
            [$topicSubstrName, $topicSubstrName, $topicSubstrName],
            true);
    }
    public static function GetCountTopicsByName($topicName){
        $topicNameForQuery = "%$topicName%";
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_topics` WHERE `name` LIKE ? OR text LIKE ? OR preview LIKE ? ",
            [$topicNameForQuery, $topicNameForQuery, $topicNameForQuery],
            false)["count(*)"];
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
        return DataKeeper::Update("tt_topics", ["categoryId" => $toCategoryId], ["categoryId" => $idCategory]);
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