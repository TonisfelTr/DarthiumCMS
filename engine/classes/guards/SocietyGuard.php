<?php

namespace Guards;

use Engine\DataKeeper;
use Engine\Engine;
use Engine\ErrorManager;
use Users\UserAgent;

class SocietyGuard
{
    public static function IsBanned($var, $isIP = false)
    {
        $type  = $isIP ? 2 : 1;
        $query = $isIP ? DataKeeper::MakeQuery("SELECT count(*) FROM `tt_banned` WHERE ? REGEXP `banned` AND `type` = ?", [$var, $type]) :
            DataKeeper::MakeQuery("SELECT count(*) FROM `tt_banned` WHERE `banned` = ? AND `type` = ?", [$var, $type]);
        if ($query["count(*)"] >= 1)
            return true;
        else
            return false;
    }
    public static function Ban($id, $reason, $time = 1, $author)
    {
        if (self::IsBanned($id)) {
            ErrorManager::GenerateError(5);
            return ErrorManager::GetError();
        }
        if (!UserAgent::IsUserExist($id)) {
            ErrorManager::GenerateError(7);
            return ErrorManager::GetError();
        }

        $result = DataKeeper::InsertTo("tt_banned", ["banned" => $id,
            "type" => 1,
            "banned_time" => Engine::GetSiteTime(),
            "unban_time" => $time == 0 ? 0 : Engine::GetSiteTime() + $time,
            "reason" => $reason,
            "author" => $author]);
        if ($result >= 0){
            return true;
        } else
            return false;
    }
    public static function BanWithSearch($needle, $reason, $time = 1, $author) {
        //Поиск пользователей по шаблону.
        $needle = str_replace("*", "%", $needle);

        $haystack = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?", [$needle]);
        $banID    = $haystack["id"];
        $result   = self::Ban($banID, $reason, $time, $author);

        if ($result == 0)
            return false;

        return true;
    }
    public static function BanIP($ip, $reason, $time = 1, $author)
    {
        if (self::IsBanned($ip, true)){
            ErrorManager::GenerateError(5);
            return ErrorManager::GetError();
        }

        $result = DataKeeper::InsertTo("tt_banned", ["banned" => $ip,
            "type" => 2,
            "banned_time" => Engine::GetSiteTime(),
            "unban_time" => $time != 0 ? Engine::GetSiteTime() + $time : 0,
            "reason" => $reason,
            "author" => $author]);
        if ($result >= 0)
            return true;
        else
            return false;
    }
    public static function Unban($id)
    {
        if (!self::IsBanned($id)) {
            ErrorManager::GenerateError(6);
            return ErrorManager::GetError();
        }

        $result = DataKeeper::Delete("tt_banned", ["banned"=> $id, "type" => 1]);

        return $result == 0 ? false : true;
    }
    public static function UnbanIP($ip)
    {
        if (!self::IsBanned($ip, true)) {
            ErrorManager::GenerateError(6);
            return ErrorManager::GetError();
        }

        $result = DataKeeper::Delete("tt_banned", ["banned" => $ip, "type" => 2]);

        return $result == 0 ? false : true;
    }
    public static function GetBanUserList($page = 1)
    {
        $lowBorder = ($page - 1) * 50;
        $highBorder = $page * 50;

        return DataKeeper::MakeQuery("SELECT `banned` FROM `tt_banned` WHERE `type`=? LIMIT $lowBorder, $highBorder", ["1"], true);
    }
    public static function GetBanUserParam($idUser, $param)
    {
        if (!self::IsBanned($idUser)) {
            ErrorManager::GenerateError(6);
            return ErrorManager::GetError();
        }

        return DataKeeper::Get("tt_banned", [$param], ["banned" => $idUser, "type" => 1])[0][$param];
    }
    public static function GetBanListByParams($params, $page = 1)
    {
        $lowBorder = ($page - 1) * 50;
        $highBorder = $page * 50;

        if ($params["nickname"] == "") $params["nickname"] = "%";
        elseif (strstr($params["nickname"], "*") === FALSE) $params["nickname"] = UserAgent::GetUserId($params["nickname"]);
        else $usersId = UserAgent::FindUsersBySNickname($params["nickname"]);

        if ($params["reason"] == "") $params["reason"] = "%";
        else $params["reason"] = str_replace("*", "%", $params["reason"]);

        $queryResponse = DataKeeper::MakeQuery("SELECT `banned` FROM `tt_banned` WHERE `reason` LIKE ? AND `type` = ? LIMIT $lowBorder, $highBorder", [$params["reason"], 1], true);
        $result = [];
        foreach ($queryResponse as $response){
            if (isset($usersId)){
                if (in_array($response, $usersId))
                    $result[] = $response;
            } else
                $result[] = $response;
        }
        return $result;
    }
    public static function GetIPBanList($page = 1){
        $lowBorder = ($page - 1) * 50;
        $highBorder = $page * 50;

        return DataKeeper::MakeQuery("SELECT `banned` FROM `tt_banned` WHERE `type` = ? LIMIT $lowBorder, $highBorder", [2], true);
    }
    public static function GetIPBanParam($ip, $param)
    {
        if (!self::IsBanned($ip, true)) {
            ErrorManager::GenerateError(6);
            return ErrorManager::GetError();
        }

        $queryResponse = DataKeeper::MakeQuery("SELECT $param FROM `tt_banned` WHERE ? REGEXP `banned` AND `type` = ?", [$ip, 2])[$param];

        return $queryResponse;
    }
}