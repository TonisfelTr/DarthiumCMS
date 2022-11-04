<?php

namespace Exceptions\Exemplars;

class ForbiddenError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 14);
    }
}