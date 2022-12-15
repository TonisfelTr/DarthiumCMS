<?php

namespace Exceptions\Exemplars;

class SessionNotContainError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 42);
    }
}