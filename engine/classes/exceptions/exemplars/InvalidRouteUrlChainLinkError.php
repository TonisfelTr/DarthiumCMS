<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class InvalidRouteUrlChainLinkError extends TavernException
{
    protected $availableCodes = [
                                 ErrorManager::EC_DUPLICATED_URL_CHAIN_LINK_NAME,
                                 ErrorManager::EC_INVALID_URL_CHAIN_LINK_NAME,
                                 ErrorManager::EC_URL_CHAIN_LINK_DOES_NOT_EXIST,
                                 ErrorManager::EC_INVALID_LINK_IN_URL,
                                 ErrorManager::EC_INVALID_URL_CHAIN_LINK_CONTENT
        ];
}