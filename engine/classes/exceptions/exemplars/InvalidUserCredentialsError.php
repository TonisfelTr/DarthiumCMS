<?php

namespace Exceptions\Exemplars;

use Exceptions\TavernException;
use Engine\ErrorManager;

class InvalidUserCredentialsError extends TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, ErrorManager::EC_INVALID_USER_ACCESS_DATA);
    }
}