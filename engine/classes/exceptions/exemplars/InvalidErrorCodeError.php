<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class InvalidErrorCodeError extends TavernException
{
    public function __construct(int $codeError = null) {
        if (is_null($codeError)) {
            parent::__construct("Invalid code error", ErrorManager::EC_INVALID_ERROR_CODE);
        } else {
            parent::__construct(ErrorManager::getErrorDescription($codeError), $codeError);
        }
    }
}