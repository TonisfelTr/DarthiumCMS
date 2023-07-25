<?php

namespace Exceptions\Exemplars;

use Exceptions\TavernException;

class RoutesRegistrationError extends TavernException
{
    protected     $availableCodes = [99];
    protected     $message;
    protected int $currentCode    = 99;

    public function __construct(string $message = "", int $errorCode = 99) {
        parent::__construct($message, $errorCode);
    }
}