<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class TagError extends TavernException
{
    protected $availableCodes = [
        ErrorManager::EC_REGISTER_SERVICE_TAGS_AFTER_INJECTING_DEPENDENCIES,
        ErrorManager::EC_TAG_REGISTERED_ALREADY,
        ErrorManager::EC_INVALID_TAG_NAME,
        ErrorManager::EC_INVALID_RETURN_TYPE_OF_TAG_PROCESSING,
        ErrorManager::EC_EMPTY_TAG_CONTENT,
        ErrorManager::EC_INVALID_TAG_CONTENT,
        ErrorManager::EC_EMPTY_TAG_ARGUMENTS,
        ErrorManager::EC_INVALID_TAG_ARGUMENTS,
        ErrorManager::EC_TOO_FEW_REQUIRED_ATTRIBUTES_GIVEN,
        ErrorManager::EC_REQUIRED_ATTRIBUTES_COUNT_MORE_THEN_AVAILABLE,
        ErrorManager::EC_SERVICE_TAG_REGISTRATION_DISABLED,
        ErrorManager::EC_SYSTEM_TAG_REGISTRATION_DISABLED,
        ErrorManager::EC_TAG_DOES_NOT_EXIST,
        ErrorManager::EC_HTML_TAG_CONTAIN_UNAVAILABLE_ARGUMENTS,
        ErrorManager::EC_HTML_TAG_CONTAIN_TOO_FEW_REQUIRED_ARGUMENTS,
        ErrorManager::EC_INVALID_CLOSING_TAG_ARGUMENT_TYPE,
        ErrorManager::EC_TAG_COMPILATION_HAS_NO_RESULT,
        ErrorManager::EC_INVALID_TAG_ARGUMENT_SYNTAX
        ];
}