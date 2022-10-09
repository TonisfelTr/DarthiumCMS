<?php

namespace Engine;

use Users\UserAgent;

class Uploader
{
    public static function ExtractType($path)
    {

        $tmp = explode(".", $path);
        return end($tmp);

    }

    public static function UploadFile($idUser, $file)
    {
        if ($file['name'] == '') {
            ErrorManager::GenerateError(28);
            return ErrorManager::GetError();
        }

        if (!UserAgent::IsUserExist($idUser)) {
            ErrorManager::GenerateError(7);
            return ErrorManager::GetError();
        }

        $types = Engine::GetEngineInfo("upf");
        $maxsize = Engine::GetEngineInfo("ups");

        if (!strstr($types, self::ExtractType($file['name']))) {
            ErrorManager::GenerateError(13);
            return ErrorManager::GetError();
        }

        if ($file['size'] >= $maxsize) {
            ErrorManager::GenerateError(27);
            return ErrorManager::GetError();
        }

        $images = array();
        $docs = array();
        $zips = array();
        $other = array();

        $types = explode(",", $types);
        for ($i = 0; $i < count($types); $i++) {
            if (in_array($types[$i], ['gif', 'png', 'bmp', 'tiff', 'tif', 'jpeg', 'jpg'])) array_push($images, $types[$i]);
            elseif (in_array($types[$i], ['doc', 'txt', 'xls', 'ppt', 'pptx', 'docx'])) array_push($docs, $types[$i]);
            elseif (in_array($types[$i], ['zip', 'rar', 'tar', 'gzip', '7z', 'gz'])) array_push($zips, $types[$i]);
            else array_push($other, $types[$i]);
        }

        $uploadPath = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";
        $filePath = '';

        if (in_array(self::ExtractType($file['name']), $images)) {
            $uploadPath .= "images/";
            $filePath = "uploads/images/";
        }
        if (in_array(self::ExtractType($file['name']), $docs)) {
            $uploadPath .= "docs/";
            $filePath = "uploads/docs/";
        }
        if (in_array(self::ExtractType($file['name']), $zips)) {
            $uploadPath .= "zips/";
            $filePath = "uploads/zips/";
        }
        if (in_array(self::ExtractType($file['name']), $other)) {
            $uploadPath .= "others/";
            $filePath = "uploads/others/";
        }

        $newName = Engine::RandomGen() . "." . self::ExtractType($file['name']);

        if (move_uploaded_file($file['tmp_name'], $uploadPath . $newName)) {
            return (bool) DataKeeper::InsertTo("tt_uploads", ["file_path" => $filePath, "name" => $newName, "author" => $idUser, "upload_date" => date("Y-m-d", Engine::GetSiteTime())]);
        } else {
            ErrorManager::GenerateError(12);
            return ErrorManager::GetError();
        }
    }

    public static function GetUploadList($idUser)
    {
        return DataKeeper::Get("tt_uploads", ["id"], ["author" => $idUser]);
    }

    public static function GetUploadedFilesList(int $page)
    {
        $lowBorder = $page * 50 - 50;
        $highBorder = 50;

        return DataKeeper::MakeQuery("SELECT * FROM `tt_uploads` ORDER BY id DESC LIMIT $lowBorder,$highBorder", null, true);
    }

    public static function GetUploadedFilesListByAuthor(string $nickname, int $page)
    {
        $lowBorder = $page * 50 - 50;
        $highBorder = 50;

        return DataKeeper::MakeQuery("SELECT *
                                                    FROM `tt_uploads`
                                                    WHERE `author` IN 
                                                    (SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?)
                                                    ORDER BY id DESC
                                                    LIMIT $lowBorder,$highBorder", ["%$nickname%"], true);
    }

    public static function GetUploadedFilesListByReference(string $ref, int $page)
    {
        $lowBorder = $page * 50 - 50;
        $highBorder = 50;

        $queryResponse = DataKeeper::MakeQuery("SELECT * FROM tt_uploads 
                                                     WHERE name LIKE ?
                                                     ORDER BY id DESC                                                       
                                                     LIMIT $lowBorder,$highBorder", ["%.$ref"], true);

        $files = [];
        foreach ($queryResponse as $response){
            $files[] = [
                "id" => $response["id"],
                "file_path" => $response["file_path"],
                "upload_date" => $response["upload_date"],
                "name" => $response["name"],
                "author" => $response["author"]
            ];
        }
        return $files;
    }

    public static function GetUploadInfo($fId, $param)
    {
        return DataKeeper::Get("tt_uploads", [$param], ["id" => $fId])[0][$param];
    }

    public static function DeleteFile($fId)
    {
        if (!unlink($_SERVER["DOCUMENT_ROOT"] . "/" . self::GetUploadInfo($fId, "file_path") . self::GetUploadInfo($fId, "name")))
            return false;

        return DataKeeper::Delete("tt_uploads", ["id" => $fId]);

    }

    public static function DeleteFilesOfUser(int $userId)
    {
        return DataKeeper::Delete("tt_uploads", ["author" => $userId]);
    }

}