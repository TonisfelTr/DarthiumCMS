<?php

namespace Engine;

use Builder\Controllers\BuildAgent;
use Engine\Services\File;
use Exceptions\Exemplars\InvalidFileInputContentError;
use Exceptions\Exemplars\UserExistsError;
use Exceptions\TavernException;
use Users\UserAgent;

class FileAgent
{
    public static function uploadFile(int $idUser, string $fileSourceName)
    {
        if (!UserAgent::IsUserExist($idUser)) {
            throw new UserExistsError("User with that ID doesn't exist", 7);
        }

        self::hasSentFile($fileSourceName, null, true);
        $file = new File($fileSourceName);

        if (!$file->isExtensionValid()) {
            throw new InvalidFileInputContentError(ErrorAgent::EC_NOT_PERMITTED_FILE_EXTENSION);
        }

        if (!$file->isSizeValid()) {
            throw new InvalidFileInputContentError(ErrorAgent::EC_FILE_OVERSIZE);
        }

        $uploadPath = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";

        if ($file->isImage()) {
            $uploadPath .= "images/";
            $filePath = "uploads/images/";
        } elseif ($file->isDocument()) {
            $uploadPath .= "docs/";
            $filePath = "uploads/docs/";
        } elseif ($file->isArchive()) {
            $uploadPath .= "zips/";
            $filePath = "uploads/zips/";
        } else {
            $uploadPath .= "others/";
            $filePath = "uploads/others/";
        }

        if ($newName = $file->moveTo($uploadPath)) {
            return (bool) Database::InsertTo("tt_uploads", [ "file_path" => $filePath, "name" => $newName, "author" => $idUser, "upload_date" => date("Y-m-d", Engine::GetSiteTime())]);
        } else {
            throw new InvalidFileInputContentError(ErrorAgent::EC_CANNOT_FIND_TEMP_FILE);
        }
    }
    
    public static function getUploadList($idUser)
    {
        return Database::Get("tt_uploads", [ "id"], [ "author" => $idUser]);
    }
    
    public static function getUploadedFilesList(int $page)
    {
        $lowBorder = $page * 50 - 50;
        $highBorder = 50;

        return Database::MakeQuery("SELECT * FROM `tt_uploads` ORDER BY id DESC LIMIT $lowBorder,$highBorder", null, true);
    }
    
    public static function getUploadedFilesListByAuthor(string $nickname, int $page)
    {
        $lowBorder = $page * 50 - 50;
        $highBorder = 50;

        return Database::MakeQuery("SELECT *
                                                    FROM `tt_uploads`
                                                    WHERE `author` IN 
                                                    (SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?)
                                                    ORDER BY id DESC
                                                    LIMIT $lowBorder,$highBorder", ["%$nickname%"], true);
    }
    
    public static function getUploadedFilesListByReference(string $ref, int $page)
    {
        $lowBorder = $page * 50 - 50;
        $highBorder = 50;

        $queryResponse = Database::MakeQuery("SELECT * FROM tt_uploads
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
    
    public static function getUploadInfo(int $fId, string $param = "") {
        if ($param != "") {
            return Database::Get("tt_uploads", [ $param], [ "id" => $fId])[0][$param];
        } else {
            return Database::Get("tt_uploads", [ "*"], [ "id" => $fId])[0][$param];
        }
    }
    
    public static function deleteFile(int $fId): bool
    {
        $fileInfo = self::GetUploadInfo($fId);
        if (!unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $fileInfo["file_path"] . $fileInfo["name"])) {
            return false;
        }

        return Database::Delete("tt_uploads", [ "id" => $fId]);

    }
    
    public static function deleteFilesOfUser(int $userId): bool {
        $uploadedFiles = Database::getBuilder()
            ->select("file_path")
                                 ->select("name")
                                 ->where("author", "=", $userId)
                                 ->runDirectly();
        
        foreach ($uploadedFiles as $fileData) {
            BuildAgent::fileRemove("{$fileData["file_path"]} /{$fileData["name"]}", DEFAULT_ROOT);
        }
        
        Database::getBuilder()
                       ->delete("tt_uploads")
                       ->where("author", "=", $userId)
                       ->runDirectly();
        
        return Database::getLastAffectedCount() > 0;
    }

    /**
     * Check if file had been sent.
     *
     * @param string|null $senderName Sender name.
     * @param int|null $indexOfContent Index of uploaded file. If has been uploaded only one file, throw an exception.
     * @param bool $strict If true, throw exception in case of false result.
     * @return bool Checking result of file sent.
     */
    public static function hasSentFile(string $senderName = null, int $indexOfContent = null, bool $strict = false) : bool {
        //If sender exists...
        if (!isset($_FILES[$senderName])) {
            //If file number is not null...
            if (!is_null($indexOfContent)) {
                //If sent files count is more then 0...
                if (is_array($_FILES[$senderName]["name"])) {
                    //If sender sent file with number $indexOfContent
                    if ($indexOfContent < 0) {
                        throw new TavernException("Index cannot be less then 0", ErrorAgent::EC_INVALID_ARGUMENT);
                    }

                    if (isset($_FILES[$senderName]["name"][$indexOfContent])) {
                        if ($_FILES[$senderName]["name"][$indexOfContent] != "") {
                            return true;
                        } else {
                            if ($strict) {
                                throw new InvalidFileInputContentError(ErrorAgent::EC_FILE_NOT_EXIST);
                            }

                            return false;
                        }
                    } else {
                        if ($strict) {
                            throw new InvalidFileInputContentError(ErrorAgent::EC_INVALID_FILE_INDEX);
                        }

                        return false;
                    }
                //If sent files count is 1...
                } else {
                    if ($strict) {
                        throw new TavernException("Sender has sent only one file", ErrorAgent::EC_INVALID_ARGUMENT);
                    }

                    return false;
                }
            //If file number is null...
            //If sent file has name...
            } elseif (isset($_FILES[$senderName]["name"])) {
                //If name is empty...
                if ($_FILES[$senderName]["name"] != "") {
                    return true;
                } elseif ($strict) {
                    throw new InvalidFileInputContentError(ErrorAgent::EC_FILE_NOT_EXIST);
                }

                return false;
            }
        }

        //If sender does not exist or any another way...
        return false;
    }

    public static function getFile(string $senderName, bool $strict = false) {
        if ($strict && !self::hasSentFile($senderName)) {
            throw new InvalidFileInputContentError(ErrorAgent::EC_NO_FILE_TO_UPLOAD);
        }

        return File::getOneOrAll($senderName);
    }
    
    public static function extractType(string $path): string {
        $exploded = explode(".", $path);
        
        return end($exploded);
    }
}