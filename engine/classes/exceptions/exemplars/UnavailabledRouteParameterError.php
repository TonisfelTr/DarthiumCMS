<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class UnavailabledRouteParameterError extends TavernException
{
    protected $availableCodes = [55];
}