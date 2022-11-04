<?php

namespace Exceptions\Exemplars;

class CaptchaGenerationError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 8);
    }
}