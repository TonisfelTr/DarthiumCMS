<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class FlashSessionDidNotReadError extends TavernException
{
    public function __construct() {
        parent::__construct("Flash session did not read", ErrorManager::EC_FLASH_SESSION_CONTENT_DID_NOT_READ);
    }
}