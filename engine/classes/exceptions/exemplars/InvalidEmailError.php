<?php

namespace Exceptions\Exemplars;

use Exceptions\TavernException;
use Engine\ErrorManager;

class InvalidEmailError extends TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, ErrorManager::EC_EMAIL_CONTAINS_INVALID_SYMBOLS);
    }
}