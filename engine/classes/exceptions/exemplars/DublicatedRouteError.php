<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class DublicatedRouteError extends TavernException
{
    protected $availableCodes = [ErrorManager::EC_DUPLICATED_ROUTE_URL, ErrorManager::EC_DUPLICATED_ROUTE_NAME];
    protected $messageInDescription = true;
}