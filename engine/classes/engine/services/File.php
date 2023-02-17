<?php

namespace Engine\Services;

use Engine\Engine;
use Engine\ErrorManager;
use Engine\Uploader;
use Exceptions\Exemplars\InvalidFileInputContentError;
use Exceptions\TavernException;

/**
 * It handles all the basic information that a browser
 * sent and the server received. This class contains all the information from the
 * $_FILES global superarray, separated for different properties of this entity. Also,
 * this class can do every can do Uploader class but just with regards to exemplar
 * of this class created entity.
 **/
class File extends Uploader {
    /** @var string File name on the client machine. */
    private string $fileName;
    /** @var string The MIME type of the file. Exists, if the browser provided this information. */
    private string $fileType;
    /** @var int The size of the uploaded file in bytes. */
    private int $fileSize;
    /** @var string Path from the row with temporary name of file which given after uploading to the server. */
    private string $fileTmpPath;
    /** @var string Name from the row with temporary name of file which given after uploading to the server. */
    private string $fileTmpName;
    /** @var int The error code associated with this file upload. */
    private int $uploadingErrorCode;
    /** @var string The full path as submitted by the browser. It cannot be trusted. Available as of PHP 8.1 */
    private string $fileFullPath;

    /**
     * Returns file entity or file entities array from received data. If $senderName is not null, return
     * file entity or file entities array, dependency by count of sent files from sender. If $senderName
     * is null, return all able file entities.
     * 
     * @param string|null $senderName Input tag name.
     * @return File|array Entity or entities array.
     */
    public static function getOneOrAll(string $senderName = null) {
        if (!self::hasSentFile($senderName)) {
            throw new InvalidFileInputContentError(44);
        }

        //If $senderName is not null, handle files from gotten sender.
        if (!is_null($senderName)) {
            if (!is_array($_FILES[$senderName]["name"])) {
                $result = new File($senderName);
            } else {
                $result = [];

                for ($i = 0; $i < count($_FILES[$senderName]["name"]); $i++) {
                    $result[] = new File($senderName, $i);
                }
            }
        //If $senderName is null...
        //If count of senders is more then one, handle all of them recursively.
        } elseif (count($_FILES) > 1) {
            //Enumerate all senders...
            foreach (array_keys($_FILES) as $key) {
                $result[] = File::getOneOrAll($key);
            }
        //If a sender is the only one, handle that one recursively
        } else {
            $result = File::getOneOrAll($_FILES[array_keys($_FILES)[0]]);
        }

        return $result;
    }

    /**
     * Create file entity handler.
     *
     * @param string $senderName Sender name.
     * @param int|null $indexOfContents Index of sent file from sender. If tag has one file or file index does not
     *                                  exist, it throw an exception.
     */
    public function __construct(string $senderName, int $indexOfContents = null) {
        if (!self::hasSentFile($senderName)) {
            throw new InvalidFileInputContentError(ErrorManager::EC_NO_FILE_TO_UPLOAD);
        }

        if (is_null($indexOfContents)) {
            $this->fileName = $_FILES[$senderName]["name"];
            $this->fileType = $_FILES[$senderName]["type"] ?? null;
            $this->fileSize = $_FILES[$senderName]["size"];
            $this->uploadingErrorCode = $_FILES[$senderName]["error"];

            $dividerTmpName = explode("/", $_FILES[$senderName]["tmp_name"]);
            $this->fileTmpName = end($dividerTmpName);
            $this->fileTmpPath = substr($_FILES[$senderName]["tmp_name"], -(strlen($this->fileTmpName)));

            $this->fileFullPath = $_FILES[$senderName]["full_path"] ?? null;
        } else {
            $this->fileName = $_FILES[$senderName]["name"][$indexOfContents];
            $this->fileType = $_FILES[$senderName]["type"][$indexOfContents] ?? null;
            $this->fileSize = $_FILES[$senderName]["size"][$indexOfContents];
            $this->uploadingErrorCode = $_FILES[$senderName]["error"][$indexOfContents];

            $dividerTmpName = explode("/", $_FILES[$senderName]["tmp_name"][$indexOfContents]);
            $this->fileTmpName = end($dividerTmpName);
            $this->fileTmpPath = substr($_FILES[$senderName]["tmp_name"][$indexOfContents], -(strlen($this->fileTmpName)));

            $this->fileFullPath = $_FILES[$senderName]["full_path"][$indexOfContents] ?? null;
        }
    }

    public function getName() : string {
        return $this->fileName;
    }

    public function getType() : string {
        return $this->fileType;
    }

    public function getSize() : int {
        return $this->fileSize;
    }

    public function getTempName() : string {
        return $this->fileTmpName;
    }

    public function isUploadingFailed() : bool {
        return $this->uploadingErrorCode != UPLOAD_ERR_OK;
    }

    public function getUploadingError() : int {
        return $this->uploadingErrorCode;
    }

    /**
     * Returns content of full path property.
     *
     * @return string Full path.
     * @throws TavernException Throws if PHP version is less then 8.1.0.
     */
    public function getFullPath() : string {
        if (version_compare(PHP_VERSION, "8.1.0", ">=")) {
            return $this->fileFullPath;
        }

        throw new TavernException(ErrorManager::getErrorDescription(ErrorManager::EC_INVALID_PHP_VERSION), ErrorManager::EC_INVALID_PHP_VERSION);
    }

    public function getBasicExtension() : string {
        $a = explode(".", $this->fileName);

        return count($a) > 2
            ? end($a)
            : "";
    }

    public function isExtensionValid() : bool {
        $exts = explode(",", Engine::GetEngineInfo("upf"));

        return in_array($this->getBasicExtension(), $exts);
    }

    public function isSizeValid() : bool {
        return Engine::GetEngineInfo("ups") >= $this->fileSize;
    }

    public function isImage() : bool {
        return in_array($this->getBasicExtension(), ['gif', 'png', 'bmp', 'tiff', 'tif', 'jpeg', 'jpg']);
    }

    public function isDocument() : bool {
        return in_array($this->getBasicExtension(), ['doc', 'txt', 'xls', 'ppt', 'pptx', 'docx']);
    }

    public function isArchive() : bool {
        return in_array($this->getBasicExtension(), ['zip', 'rar', 'tar', 'gzip', '7z', 'gz']);
    }

    public function moveTo(string $newPath, string $newName = null) : string {
        if (is_null($newName)) {
            $newName = Engine::RandomGen(10) . "." . $this->getBasicExtension();
            move_uploaded_file($this->getTempFullPath(), $newPath . $newName);
        }
    }
}