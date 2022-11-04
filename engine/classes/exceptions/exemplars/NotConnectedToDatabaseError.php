<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class NotConnectedToDatabaseError extends TavernException
{
    public function __construct(string $message) {
        parent::__construct($message, ErrorManager::EC_DATABASE_CONNECTION_FAILED);
    }
}