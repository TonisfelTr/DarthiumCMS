<?php

namespace Exceptions\Exemplars;

class InvalidUploadFileError extends \Exceptions\TavernException
{
    public function __construct(string $message = "", int $errorCode = 999) {
        parent::__construct($message, $errorCode);
    }
}