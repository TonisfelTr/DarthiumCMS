<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class UnavailabledErrorCodeError extends TavernException
{
    public function __construct() {
        parent::__construct("This error code is not available for thrown exception", ErrorManager::EC_INVALID_ERROR_CODE);
    }
}