<?php

namespace Decorator\Controllers;

use Engine\DataKeeper;
use Engine\ErrorManager;

class SidePanelsAgent{
    public static function AddSidePanel($side, $name, $content, $isVisible){
        if ($side == SB_LEFTSIDE) $side = "leftside";
        if ($side == SB_RIGHTSIDE) $side = "rightside";
        $result = DataKeeper::InsertTo(\Decorator\SB_TABLE, array(
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
        if (!DataKeeper::exists(\Decorator\SB_TABLE, "id", $id)){
            ErrorManager::GenerateError(35);
            return ErrorManager::GetError();
        }

        $result = DataKeeper::Update(\Decorator\SB_TABLE, $newContent, ["id" => $id]);
        if ($result)
            return true;
        else
            return false;
    }
    public static function DeleteSidePanel($idPanel){
        if (!DataKeeper::exists(\Decorator\SB_TABLE, "id", $idPanel)){
            ErrorManager::GenerateError(35);
            return ErrorManager::GetError();
        }

        $result = DataKeeper::Delete(\Decorator\SB_TABLE, ["id" => $idPanel]);
        if ($result)
            return true;
        else
            return false;
    }
    public static function GetPanel($idPanel){
        if (!DataKeeper::exists(\Decorator\SB_TABLE, "id", $idPanel)){
            ErrorManager::GenerateError(35);
            return ErrorManager::GetError();
        }

        $result = DataKeeper::Get(\Decorator\SB_TABLE, array("name", "type", "content", "isVisible"), array("id" => $idPanel));
        if (is_array($result))
            return $result[0];
        else
            return false;
    }
    public static function GetPanelsList(){
        $result = DataKeeper::Get(\Decorator\SB_TABLE, array("id"));
        if (is_array($result))
            return $result;
        else
            return false;
    }
}