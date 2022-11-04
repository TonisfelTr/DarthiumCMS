<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;

class ReferrerNotExistError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, ErrorManager::EC_REFERRER_NOT_EXIST);
    }
}