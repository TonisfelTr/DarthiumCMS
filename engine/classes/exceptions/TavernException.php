<?php

namespace Exceptions;

use Engine\ErrorManager;
use Error;

class TavernException extends Error
{
    protected $message;
    protected int $currentCode;

    public function __construct(string $message = "", int $errorCode = 999) {
        ErrorManager::throwIfErrorCodeInvalid($errorCode);

        parent::__construct($message, 500);

        $this->code = 500;
        $this->message = $message;
        $this->currentCode = $errorCode;
    }

    public function getErrorCode() : int {
        return $this->currentCode;
    }
}