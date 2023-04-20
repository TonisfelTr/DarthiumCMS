<?php

namespace Users;

use Engine\DataKeeper;
use Exceptions\Exemplars\GroupInvalidNameError;
use Exceptions\Exemplars\GroupNotExistError;

class GroupAgent {

    private static function CheckNameValid($name) {
        if (strlen($name) <= 4) {
            throw new GroupInvalidNameError("Group name is too short", 15);
        }

        if (strlen($name) >= 16) {
            throw new GroupInvalidNameError("Group name is too long", 16);
        }

        preg_match("/[a-zA-Zа-яА-Я]+/", $name, $arrPreg);
        if (count($arrPreg) > 1 || strlen($arrPreg[0]) != strlen($name)) {
            echo 2;
        } else {
            echo 1;
        }

        return (bool)DataKeeper::MakeQuery("SELECT count(*) FROM `tt_groups` WHERE `name` = ?", [$name])["count(*)"];
    }

    public static function IsGroupExists($id) {
        return (bool)DataKeeper::MakeQuery("SELECT count(*) FROM `tt_groups` WHERE `id` = ?", [$id])["count(*)"];
    }

    public static function AddGroup($name, $color, $descript) {
        try {
            if (!self::CheckNameValid($name) == true) {
                return DataKeeper::InsertTo("tt_groups", ["id"       => null,
                    "name"     => $name,
                    "color"    => $color,
                    "descript" => $descript]);
            }
        } catch (GroupInvalidNameError $ex) {
            if ($ex->getErrorCode() == 15) {

            } elseif ($ex->getErrorCode() == 16) {

            }
        }
    }

    public static function RemoveGroup($id) {
        if (!self::IsGroupExists($id)) {
            throw new GroupNotExistError("Group doesn't exist");
        }

        return DataKeeper::Delete("tt_groups", ["id" => $id]);
    }

    public static function ChangeGroupPerms($id, $type, $typeNew) {
        $nonPerms = array(0 => 'id', 1 => 'name', 2 => 'color', 3 => 'descript');
        if (in_array($type, $nonPerms)) {
            exit;
        }
        if (!self::IsGroupExists($id)) {
            throw new GroupNotExistError("Group doesn't exist");
        }

        return DataKeeper::Update("tt_groups", ["$type" => $typeNew], ["id" => $id]);
    }

    public static function ChangeGroupData($id, $type, $typeNew) {
        $nonPerms = array(0 => "id", 1 => 'name', 2 => 'color', 3 => 'descript');
        if (!in_array($type, $nonPerms)) {
            exit;
        }

        if ($type == 'name') {
            try {
                self::CheckNameValid($typeNew);

                return DataKeeper::Update("tt_groups", ["$type" => $typeNew], ["id" => $id]);
            } catch (GroupInvalidNameError $ex) {
                if ($ex->getErrorCode() == 15) {

                } elseif ($ex->getErrorCode() == 16) {

                }
            }
        }
    }

    public static function MoveGroupMembers($id, $toId) {
        if (!GroupAgent::IsGroupExists($toId)) {
            throw new GroupNotExistError("Group doesn't exist");
        }

        return DataKeeper::Update("tt_users", ["group" => $toId], ["group" => $id]);
    }

    public static function GetGroupList() {
        return DataKeeper::Get("tt_groups", ["id"]);
    }

    public static function GetGroupNameById($id) {
        return DataKeeper::Get("tt_groups", ["name"], ["id" => $id])[0]["name"];
    }

    public static function GetGroupColor($id) {
        return DataKeeper::Get("tt_groups", ["color"], ["id" => $id])[0]["color"];
    }

    public static function GetGroupDescribe($id) {
        return DataKeeper::Get("tt_groups", ["descript"], ["id" => $id])[0]["descript"];
    }

    public static function GetUsersCountInGroup(int $groupId) {
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `group` = ?", [$groupId])["count(*)"];
    }

    public static function GetGroupUsers($id, int $page = 1) {
        $lowBorder = $page * 15 - 15;
        $highBorder = 15;

        $queryResponse = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `group` = ? ORDER BY `id` LIMIT $lowBorder, $highBorder", [$id], true);
        return $queryResponse;
    }

    public static function IsHavePerm($id, $perm): bool {
        $nonPerms = array(0 => 'id', 1 => 'name', 2 => 'color', 3 => 'descript');
        if (in_array($perm, $nonPerms)) {
            return false;
        }

        $result = DataKeeper::Get("tt_groups", [$perm], ["id" => $id])[0][$perm];
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}