<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class EmptyOutputBufferError extends TavernException
{
    protected $availableCodes = [
        ErrorManager::EC_EMPTY_OUTPUT_BUFFER,
    ];
}