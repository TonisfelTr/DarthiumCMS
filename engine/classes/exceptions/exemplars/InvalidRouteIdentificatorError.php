<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class InvalidRouteIdentificatorError extends TavernException
{
    protected $availableCodes = [ErrorManager::EC_INVALID_ROUTE_NAME,
                                 ErrorManager::EC_INVALID_ROUTE_URL,
                                 ErrorManager::EC_INVALID_LINK_IN_URL,
                                 ErrorManager::EC_INVALID_SYMBOLS_IN_ROUTE_URL,
                                 ErrorManager::EC_ABSOLUTE_ARGUMENT_HAVE_PROPERTIES,
        ];
}