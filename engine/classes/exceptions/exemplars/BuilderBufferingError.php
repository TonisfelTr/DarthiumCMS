<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class BuilderBufferingError extends TavernException
{
    protected $availableCodes = [ErrorManager::EC_INVALID_BUFFERING_INDEX,
                                 ErrorManager::EC_OUT_OF_RANGE_BUFFERING_LEVEL];
}