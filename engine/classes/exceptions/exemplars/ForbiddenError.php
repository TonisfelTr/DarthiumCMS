<?php

namespace Exceptions\Exemplars;

use Exceptions\TavernException;

class ForbiddenError extends TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 14);
    }
}