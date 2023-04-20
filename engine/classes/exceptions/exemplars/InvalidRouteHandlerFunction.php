<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class InvalidRouteHandlerFunction extends TavernException
{
    protected $availableCodes = [
        ErrorManager::EC_INVALID_ROUTE_HANDLER_RESULT
    ];
}