<?php

namespace Exceptions\Exemplars;

use Exceptions\TavernException;
use Engine\ErrorManager;

class InvalidNicknameError extends TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, ErrorManager::EC_NICKNAME_CONTAINS_INVALID_SYMBOLS);
    }
}