<?php

namespace Exceptions\Exemplars;

class InvalidPageNumberError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 41);
    }
}