<?php

namespace Builder\Controllers;

use Engine\DataKeeper;

const SB_NAVIGATOR = "tt_navbar";

class NavbarAgent {

    public static function GetElements()
    {
        $elements = DataKeeper::Get(SB_NAVIGATOR, ["*"], []);

        foreach ($elements as &$element) {
            if (preg_match('/\{[a-zA-Z_\-:]+\}/', $element['action'])) {
                $element['action'] = TagAgent::compileSystemTags($element['action']);
            }
        }

        return $elements;
    }
    public static function GetElementsOfList($parentId){
        return DataKeeper::Get("tt_navbar", ["id", "content", "action"], ["parent" => $parentId]);
    }
    public static function AddButton($text, $link){
        return DataKeeper::InsertTo(SB_NAVIGATOR, ["type" => "nav-btn", "content" => $text, "parent" => 0, "action" => $link]);
    }
    public static function AddList($name, $content){
        return DataKeeper::InsertTo(SB_NAVIGATOR, ["type" => "nav-list", "content" => $name, "parent" => 0, "action" => $content]);
    }
    public static function AddListElement($parentListId, $content, $action){
        return DataKeeper::InsertTo("tt_navbar", ["type" => "nav-list-element", "content" => $content, "parent" => $parentListId, "action" => $action]);
    }
    public static function RemoveElement($id){
        if ($id == 0 || empty($id) || is_null($id))
            return false;

        return DataKeeper::MakeQuery("DELETE FROM `tt_navbar` WHERE `id` = ? OR `parent` = ?", [$id, $id]);
    }
    public static function ChangeElement($id, $content, $action){
        return DataKeeper::Update("tt_navbar", ["content" => $content, "action" => $action], [$id]);
    }
}