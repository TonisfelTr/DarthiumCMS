<?php

namespace Exceptions\Exemplars;

class ReportAnswerManipulationError extends \Exceptions\TavernException
{
    public function __construct(string $message = "", int $codeError = 999) {
        parent::__construct($message, $codeError);
    }
}