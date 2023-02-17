<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class InvalidFileInputContentError extends TavernException
{
    public function __construct(int $errorCode = ErrorManager::EC_NO_FILE_TO_UPLOAD) {
        if (!in_array($errorCode, [
            ErrorManager::EC_FILE_CANNOT_UPLOADED,
            ErrorManager::EC_NO_FILE_TO_UPLOAD,
            ErrorManager::EC_FILE_NOT_EXIST,
            ErrorManager::EC_FILE_OVERSIZE,
            ErrorManager::EC_INVALID_FILE_INDEX,
            ErrorManager::EC_ONE_SENT_FILE,
            ErrorManager::EC_NOT_PERMITTED_FILE_EXTENSION,
            ErrorManager::EC_CANNOT_FIND_TEMP_FILE])) {
            throw new InvalidErrorCodeError();
        }

        parent::__construct(ErrorManager::getErrorDescription($errorCode), $errorCode);
    }
}