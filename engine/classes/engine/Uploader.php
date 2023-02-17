<?php

namespace Engine;

use Engine\Services\File;
use Exceptions\Exemplars\InvalidFileInputContentError;
use Exceptions\Exemplars\UserExistsError;
use Exceptions\TavernException;
use Users\UserAgent;

class Uploader
{
    public static function UploadFile(int $idUser, string $fileSourceName)
    {
        if (!UserAgent::IsUserExist($idUser)) {
            throw new UserExistsError("User with that ID doesn't exist", 7);
        }

        self::hasSentFile($fileSourceName, null, true);
        $file = new File($fileSourceName);

        if (!$file->isExtensionValid()) {
            throw new InvalidFileInputContentError(ErrorManager::EC_NOT_PERMITTED_FILE_EXTENSION);
        }

        if (!$file->isSizeValid()) {
            throw new InvalidFileInputContentError(ErrorManager::EC_FILE_OVERSIZE);
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
            return (bool) DataKeeper::InsertTo("tt_uploads", ["file_path" => $filePath, "name" => $newName, "author" => $idUser, "upload_date" => date("Y-m-d", Engine::GetSiteTime())]);
        } else {
            throw new InvalidFileInputContentError(ErrorManager::EC_CANNOT_FIND_TEMP_FILE);
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

    public static function GetUploadInfo(int $fId, string $param = "") {
        if ($param != "") {
            return DataKeeper::Get("tt_uploads", [$param], ["id" => $fId])[0][$param];
        } else {
            return DataKeeper::Get("tt_uploads", ["*"], ["id" => $fId])[0][$param];
        }
    }

    public static function DeleteFile(int $fId) : bool
    {
        $fileInfo = self::GetUploadInfo($fId);
        if (!unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $fileInfo["file_path"] . $fileInfo["name"])) {
            return false;
        }

        return DataKeeper::Delete("tt_uploads", ["id" => $fId]);

    }

    public static function DeleteFilesOfUser(int $userId)
    {
        return DataKeeper::Delete("tt_uploads", ["author" => $userId]);
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
                        throw new TavernException("Index cannot be less then 0", ErrorManager::EC_INVALID_ARGUMENT);
                    }

                    if (isset($_FILES[$senderName]["name"][$indexOfContent])) {
                        if ($_FILES[$senderName]["name"][$indexOfContent] != "") {
                            return true;
                        } else {
                            if ($strict) {
                                throw new InvalidFileInputContentError(ErrorManager::EC_FILE_NOT_EXIST);
                            }

                            return false;
                        }
                    } else {
                        if ($strict) {
                            throw new InvalidFileInputContentError(ErrorManager::EC_INVALID_FILE_INDEX);
                        }

                        return false;
                    }
                //If sent files count is 1...
                } else {
                    if ($strict) {
                        throw new TavernException("Sender has sent only one file", ErrorManager::EC_INVALID_ARGUMENT);
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
                    throw new InvalidFileInputContentError(ErrorManager::EC_FILE_NOT_EXIST);
                }

                return false;
            }
        }

        //If sender does not exist or any another way...
        return false;
    }

    public static function getFile(string $senderName, bool $strict = false) {
        if ($strict && !self::hasSentFile($senderName)) {
            throw new InvalidFileInputContentError(ErrorManager::EC_NO_FILE_TO_UPLOAD);
        }

        return File::getOneOrAll($senderName);
    }
}