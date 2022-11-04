<?php

namespace Exceptions\Exemplars;

use Exceptions\TavernException;

class NotLoadedEngineConfigError extends TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 0);
    }
}