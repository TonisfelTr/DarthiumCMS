<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;

class GroupNotExistError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, ErrorManager::EC_GROUP_NOT_EXIST);
    }
}