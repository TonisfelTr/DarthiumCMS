<?php

namespace Builder\Controllers;

use Engine\DataKeeper;
use Exceptions\Exemplars\SidePanelNotFoundError;

const SB_TABLE     = "tt_staticcomponents";
const SB_RIGHTSIDE = 3;
const SB_LEFTSIDE  = 2;

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
        if (!DataKeeper::exists(SB_TABLE, "id", $id)){
            throw new SidePanelNotFoundError("Cannot find side panel with this ID");
        }

        $result = DataKeeper::Update(SB_TABLE, $newContent, ["id" => $id]);
        if ($result)
            return true;
        else
            return false;
    }
    public static function DeleteSidePanel($idPanel){
        if (!DataKeeper::exists(SB_TABLE, "id", $idPanel)){
            throw new SidePanelNotFoundError("Cannot find side panel with this ID");
        }

        $result = DataKeeper::Delete(SB_TABLE, ["id" => $idPanel]);
        if ($result)
            return true;
        else
            return false;
    }
    public static function GetPanel($idPanel){
        if (!DataKeeper::exists(SB_TABLE, "id", $idPanel)){
            throw new SidePanelNotFoundError("Cannot find side panel with this ID");
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