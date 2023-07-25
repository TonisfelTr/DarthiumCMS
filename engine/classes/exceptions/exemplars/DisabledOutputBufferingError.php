<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class DisabledOutputBufferingError extends TavernException
{
    protected     $availableCodes = [
        ErrorManager::EC_DISABLED_OUTPUT_BUFFERING,
    ];
    protected int $currentCode;
}