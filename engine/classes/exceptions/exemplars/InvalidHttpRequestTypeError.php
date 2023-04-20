<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class InvalidHttpRequestTypeError extends TavernException
{
    public function __construct() {
        parent::__construct(ErrorManager::getErrorDescription(ErrorManager::EC_INVALID_METHOD_FOR_ROUTE), ErrorManager::EC_INVALID_METHOD_FOR_ROUTE);
    }
}