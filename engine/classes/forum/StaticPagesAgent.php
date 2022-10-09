<?php

namespace Forum;

use Engine\DataKeeper;
use Engine\Engine;

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
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_staticpages`")["count(*)"];
    }
    public static function GetLastPageID(){
        return DataKeeper::getMax("tt_staticpages", "id");
    }
    public static function GetPageKeyWords($pageId){
        return DataKeeper::Get("tt_staticpages", ["keywords"], ["id" => $pageId])[0]["keywords"];
    }
}