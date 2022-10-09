<?php

namespace Decorator\Controllers;

use Engine\DataKeeper;

class BannerAgent{
    public static function AddSmallBanner($name, $content = null){
        $result = DataKeeper::InsertTo(\Decorator\SB_TABLE, array("type" => "smallbanner",
            "name" => $name,
            "content" => $content,
            "isVisible" => 1));
        if ($result !== false)
            return $result;
        else
            return false;
    }
    public static function AddBigBanner($name, $content = null, $isVisible = false){
        $result = DataKeeper::InsertTo(\Decorator\SB_TABLE, array("type" => "banner",
            "name" => $name,
            "content" => $content,
            "isVisible" => (($isVisible == false) ? 0 : 1)));
        if ($result > 0)
            return $result;
        else
            return false;
    }
    public static function RemoveBanner($idBanner){
        $result = DataKeeper::Delete(\Decorator\SB_TABLE, array("id" => $idBanner));
        if ($result)
            return true;
        else
            return false;
    }
    public static function EditBanner($idBanner, $param, $newValue){
        $result = DataKeeper::Update(\Decorator\SB_TABLE, array($param => $newValue), array("id" => $idBanner));
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
                    $result = DataKeeper::Update(\Decorator\SB_TABLE, array("html" => $link), array("name" => "firstbanner"));
                    return $result;
                    break;
                }
            case "second":
            case 2:
            case "2":
                if (self::IsBannerExists("name", "secondbanner")){
                    $result = DataKeeper::Update(\Decorator\SB_TABLE, array("html" => $link), array("name" => "secondbanner"));
                    return $result;
                    break;
                }
        }
        return false;
    }
    public static function GetBanners($type){
        $result = DataKeeper::Get(\Decorator\SB_TABLE, array("*"), array("type" => $type));
        return $result;
    }
    public static function GetBigBannersCount(){
        $result = DataKeeper::MakeQuery("SELECT count(*) FROM `" . \Decorator\SB_TABLE . "` WHERE `type`=?", array("banner"));
        if (is_array($result)){
            return $result["count(*)"];
        }
        else return $result;
    }
    public static function GetBannersByName($name){
        $result = DataKeeper::Get(\Decorator\SB_TABLE, array("id", "type", "name", "content", "isVisible"), array("name" => $name));
        return $result;
    }
    public static function IsBannerExists($type, $param){
        if ($type == "id" && is_numeric($param)){
            $whereArray = array("id" => $param);
        } elseif ($type == "name" && !empty($param))
            $whereArray = array("name" => $param);

        $result = DataKeeper::Get(\Decorator\SB_TABLE, array("*"), $whereArray);
        if (is_array($result))
            return $result;
        else
            return false;
    }
    public static function GetBannerHTML($bannerId){
        if (self::IsBannerExists("id", $bannerId) === false)
            return false;
        $result = DataKeeper::Get(\Decorator\SB_TABLE, array("content"), array("id" => $bannerId));
        return $result[0]["content"];
    }
}