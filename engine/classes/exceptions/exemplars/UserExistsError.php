<?php

namespace Exceptions\Exemplars;

use Exceptions\TavernException;

class UserExistsError extends TavernException
{
    public function __construct(string $message, int $errorCode = 999) {
        parent::__construct($message, $errorCode);
    }
}