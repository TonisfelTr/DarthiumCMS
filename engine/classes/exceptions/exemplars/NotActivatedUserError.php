<?php

namespace Exceptions\Exemplars;

use Exceptions\TavernException;
use Engine\ErrorManager;

class NotActivatedUserError extends TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, ErrorManager::EC_INACTIVE_ACCOUNT);
    }
}