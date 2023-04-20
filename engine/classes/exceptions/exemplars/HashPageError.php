<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class HashPageError extends TavernException
{
    protected $availableCodes = [
       ErrorManager::EC_PAGE_HASHING_ERROR
    ];
}