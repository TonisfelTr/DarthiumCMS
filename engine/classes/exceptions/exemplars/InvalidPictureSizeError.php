<?php

namespace Exceptions\Exemplars;

class InvalidPictureSizeError extends FileUploadError
{
    public function __construct(string $message = "", int $errorCode = 999) {
        parent::__construct($message, $errorCode);
    }
}