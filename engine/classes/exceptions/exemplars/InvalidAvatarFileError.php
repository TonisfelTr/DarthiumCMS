<?php

namespace Exceptions\Exemplars;

class InvalidAvatarFileError extends FileUploadError
{
    public function __construct(string $message = "", int $errorCode = 999) {
        parent::__construct($message, $errorCode);
    }
}