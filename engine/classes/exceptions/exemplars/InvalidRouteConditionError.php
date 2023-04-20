<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class InvalidRouteConditionError extends TavernException
{
    protected $availableCodes = [
        ErrorManager::EC_INVALID_CONDITION_ALGORITHM,
        ErrorManager::EC_INVALID_SYNTAX_OF_URL_CHAIN_LINK,
        ErrorManager::EC_TOO_FEW_ROUTE_ARGUMENTS,
        ErrorManager::EC_ROUTE_TITLING_INVALID_TYPE,
        ErrorManager::EC_ROUTE_TITLE_IS_NOT_STRING
        ];
}